<?php
require_once __DIR__ . '/vendor/autoload.php';

define('APP_TOKEN_FILE', __DIR__ . '/app_token.txt');
define('APPLICATION_NAME', 'Slack Reunion App');
define('CREDENTIALS_PATH', __DIR__ . '/.credentials/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/drive-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE)
));
define('REUNION_FOLDER_ID', '0Byvdd_WKBNh3aWYtbUdCcXNWbTQ');
define('REUNION_WEEK_DAY', 'tuesday');


/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = CREDENTIALS_PATH;
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}


if (isset($_POST['token']) && $_POST['token'] == trim(fgets(fopen(APP_TOKEN_FILE, 'r')))){
	// Get the API client and construct the service object
  $new_text = $_POST['text'];
  //$new_text = "plop";
  $client = getClient();
  $service = new Google_Service_Drive($client);

  $date = new DateTime();
  $date->modify('yesterday 18:00');
  $date->modify('next '. REUNION_WEEK_DAY);
  $filename = $date->format('Y-m-d');
  setlocale(LC_TIME, 'fr_FR.utf8','fra');
  $formated_date = strftime("%A %d %B %Y", $date->getTimestamp());
  $searched_file_id = NULL;
  $search_response = $service->files->listFiles(array(
  	'q' => "name contains '".$filename."' and '".REUNION_FOLDER_ID."' in parents",
	'spaces' => 'drive',
	'fields' => 'files(id)'));
  if (count($search_response->files) == 0){
	//File does not exist
	$drive_file = new Google_Service_Drive_DriveFile(array('name' => $filename, 'mimeType' => 'application/vnd.google-apps.document', 'parents' => array(REUNION_FOLDER_ID)));
	$file = $service->files->create($drive_file, array('data' => "Réunion du ".$formated_date."\nOrdre du jour :\n", 'mimeType' => 'text/plain', 'uploadType' => 'multipart', 'fields' => 'id'));
	$searched_file_id = $file->id;
  }  else{
	$searched_file_id = $search_response->files[0]->id;
  }
  $response=NULL; 
  if (trim($new_text) != ''){
    $download_response = $service->files->export($searched_file_id, 'text/html', array('alt' => 'media'));
    file_put_contents("resources/tmp.html", $download_response->getBody()->getContents());
    exec("python3 /var/www/slack/slack/add_text_html_file.py -f 'resources/tmp.html' -t '".$new_text."'");
    $content = file_get_contents("resources/tmp.html");
    $drive_file = new Google_Service_Drive_DriveFile(array('name' => $filename, 'mimeType' => 'application/vnd.google-apps.document', ));
    $updated_file = $service->files->update($searched_file_id, $drive_file, array('data' => $content, 'mimeType' => 'text/html'));
    $response = array(
                     "response_type"=> "in_channel",
                     "text"=> "<@".$_POST['user_id']."> a ajouté un point à l'ordre du jour !",
                     "attachments" => array(array("text" => "https://docs.google.com/document/d/".$searched_file_id."/edit")));
  }else{
    $response = array(
                     "response_type"=> "ephemeral",
                     "text"=> "Lien de l'ordre du jour de la prochaine réunion (".$formated_date.") :",
		     "attachments" => array(array("text" => "https://docs.google.com/document/d/".$searched_file_id."/edit")));
  }
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($response);
}



/*
$fileId = "17bH4epl_NBnEKYHQYvTENY0YA1NdYH-8HB2NGfNinMI";
$response = $service->files->export($fileId, 'text/plain', array('alt' => 'media'));
printf("%s", $response->getBody()->getContents());
$fileMetadata = new Google_Service_Drive_DriveFile(array('name' => 'test', 'mimeType' => 'application/vnd.google-apps.document', 'parents' => array('0B3TOHzAm3I1SODVhZGUyMTctZjI2NS00OTFlLWFmMDEtN2YyMTNiYmZiMWJh')));

$content = file_get_contents('upload.txt');
$file = $service->files->create($fileMetadata, array('data' => $content, 'mimeType' => 'text/plain', 'uploadType' => 'multipart', 'fields' => 'id'));
printf("File ID: %s\n", $file->id);
 */

