<?php
    if (isset($_POST['token']) && $_POST['token'] == "YOUR_TOKEN_HERE")
    {

	list($character, $quote) = explode("\"", $_POST['text']);

	$cmd = "python3 /var/www/slack/slack/kaamelott_quotes_to_robot_slack.py -s \"#" . $_POST['channel_name'] . "\"";
        $echo_cmd = "<@" . $_POST['user_id'] . "> /kaamelott";
	if (!empty($character)){
		$cmd = $cmd . " -c \"" . $character . "\"";
		$echo_cmd = $echo_cmd . " " . $character;
	}
	if (!empty($quote)){
		$cmd = $cmd . " -q \"" . $quote . "\"";
		$echo_cmd = $echo_cmd . " \"" . $quote . "\"";
	}

	ignore_user_abort(true);
	set_time_limit(0);
	ob_start();
	$quick_resp=array("text"=>"Une seconde, on cherche la meilleure");
	//$quick_resp=array("text"=>$cmd);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($quick_resp);
	header('Connection: close');
	header('Content-Length: '.ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();

	$locale = 'fr_FR.UTF-8'; setlocale(LC_ALL, $locale); putenv('LC_ALL='.$locale);
	
	$return = exec($cmd);
        if ($return != 0){
		
       		$response = array(
                     "response_type"=> "ephemeral",
                     "text"=> "Aucune citation trouvée :( "       
                    );
	}else{
		$response = array(
			"response_type"=> "in_channel",
			"text"=> $echo_cmd);
	}
        header('Content-Type: application/json');
        $ch = curl_init( $_POST['response_url'] );
	curl_setopt( $ch, CURLOPT_POST, 1);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($response));
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt( $ch, CURLOPT_HEADER, 1);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

	$r = curl_exec( $ch );
        
    }
    else
    {
        echo "Application token not valid.";
    }
?>
