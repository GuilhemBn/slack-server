ENAC Robotique Slack Server
===========================

Provides services to be used with Slack commands.

- Search and send a Kaamelott quote (from Wikiquote) to Slack
- Create and update a Google doc for meetings planning

Installation notes
------------------

Prerequisites : [docker](http://www.docker.com/)

Set the wanted commands on the [slack app configuration panel](https://api.slack.com/apps) to hit `http://<your-ip>:8282/slack/kaamelott.php` and `http://<your-ip>:8282/slack/reunion_drive.php`

Set the your application token in `src/slack/kaamelott.php` and `src/slack/reunion_drive.php`

Set the root of your file Drive folder ID to `src/slack/reunion_drive.php`

Set the your incoming webhook url in `src/slack/kaamelott_quotes_to_robot_slack.py`

Build and run the container by doing :

```bash
make
```
