<?php

//variables for logging
$status_value = "";
$status_file = "event.log";

//Pointer to Google API Library
require_once __DIR__ . '/vendor/autoload.php';

//Pointer to PHPMailer and initialization of client
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$mail = new PHPMailer();

//To make sure that the script ends
set_time_limit(600);

//YouTube API variables
$application_name = 'Web client 1'; 
$client_secret = 'gKE7c3JlbcqhwWeJrsYBsTK6';
$client_id = '264073135830-r9hvg1ldtbi0jjk2lria1dhhjf25adr7.apps.googleusercontent.com';
$scope = array('https://www.googleapis.com/auth/youtubepartner','https://www.googleapis.com/auth/youtube');


try{
   // YouTube Client initialization
   $client = new Google_Client();
   $client->setApplicationName($application_name);
   $client->setClientId($client_id);
   $client->setClientSecret($client_secret);
   $client->setAccessType('offline');
   $client->refreshToken('1/KxHnDFXVuB8OfepbmEzuxFQdn46iX7Im8RwTKGplupI');
   $client->setScopes($scope);
   $client->getAccessToken();

    // YouTube Data object used to make Data API requests
    $youtube = new Google_Service_YouTube($client);

    // YouTube Partner object used to make Content ID API requests.
    $youtubePartner = new Google_Service_YouTubePartner($client);
    
    
    // Call the contentOwners.list method to retrieve the ID of the content
    // owner associated with the currently authenticated user's account.
    $contentOwnersListResponse = $youtubePartner->contentOwners->listContentOwners(
        array('fetchMine' => true));
    $contentOwnerId = $contentOwnersListResponse['items'][0]['id'];

    // Call the search.list method to retrieve the number of active live events in the Paw Patrol channel
    // Call When stream is set to public
    //$searchResponse = $youtube->search->listSearch('snippet',array('eventType'=> 'live', 'type'=>'video', 'channelId'=> 'UCl6lLijZZEeUWPFsFJQ3mwQ'));
    //Private: UCl6lLijZZEeUWPFsFJQ3mwQ
    //TVOKids: UCGPPdIn67ZCLhhHABoQpNUw


    // Call when stream is unlisted -- to be used for soft loaunch
    // Need to change to id for stream which is in the share section
    $searchResponse = $youtube->videos->listVideos('snippet,contentDetails', array('id' => 'rSduJbP31sc'));
    $searchResponseText = json_encode($searchResponse['modelData']);
    $totalResults = $searchResponse['pageInfo']['totalResults'];
    

    

    //totalResults is the number of active live streaming events
    if ($searchResponse['modelData']['items'][0]['snippet']['liveBroadcastContent'] !== "live"){
        //totalResults == 0 if there are no active live streaming events 
        //email webencode inbox if the stream is down
        $mail->setFrom("wencode@tvo.org");
        $mail->addAddress("wencode@tvo.org");
        $mail->addAddress("vtagarelli@tvo.org");
        $mail->addAddress("afernandes@tvo.org");
        $mail->addAddress("wilagan@tvo.org");
        $mail->addAddress("klennox@tvo.org");
        $mail->addAddress("rmichael@tvo.org");
        $mail->Subject = "TVOKids Live 24/7 Stream down";
        $mail->Body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n The TVOKids Live Stream is down. \r\n\r\n Please check The 'TVOKids Live Stream - Day' or 'TVOKids Live Stream - Night 1' or 'TVOKids Live Stream - Night 2'  in Lightspeed live at http://lsl-tvo:8089/signin and https://www.youtube.com/tvokids";
        $mail->IsSMTP();
        $mail->Host = 'mail.tvo.org';
        $mail->SMTPAuth = true;
        //$mail->SMTPDebug = 2;
        $mail->Port = 587;
        $mail->Username = "wencode@tvo.org";
        $mail->Password = "webencode";
        print_r($mail->send());
        /*if(!$mail->send()) {
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . 'Email is not sent. Email error: ' . $mail->ErrorInfo;
        } else {
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . "Email is sent";
        }*/
        /*$mail->Debugoutput = function($str, $level) {
            $debug = "debug level $level; message: $str";
            return $debug;
        };*/
        $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS DOWN - {$searchResponseText}";
    } else if ($totalResults == 1){
        //if there is 1 active live stream up
        //check to make sure that the correct stream is up
        if (strpos($searchResponse['items'][0]['snippet']['liveBroadcastContent'],"live") !== false){
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS UP - {$searchResponseText}";
        } else{
            //if there is an active live stream up, but it doesn't have the correct title, then the stream is down, because the title will always be the same.
            $mail->setFrom("wencode@tvo.org");
            $mail->addAddress("wencode@tvo.org");
            $mail->addAddress("vtagarelli@tvo.org");
            $mail->addAddress("afernandes@tvo.org");
            $mail->addAddress("wilagan@tvo.org");
            $mail->addAddress("klennox@tvo.org");
            $mail->addAddress("rmichael@tvo.org");
            $mail->Subject = "TVOKids Live 24/7 Stream down";
            $mail->Body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n The TVOKids Live Stream is down. \r\n\r\n Please check The 'TVOKids Live Stream - Day' or 'TVOKids Live Stream - Night 1' or 'TVOKids Live Stream - Night 2'  in Lightspeed live at http://lsl-tvo:8089/signin and https://www.youtube.com/tvokids";
            $mail->IsSMTP();
            $mail->Host = 'mail.tvo.org';
            $mail->SMTPAuth = true;
            //$mail->SMTPDebug = 2;
            $mail->Port = 587;
            $mail->Username = "wencode@tvo.org";
            $mail->Password = "webencode";
            if(!$mail->send()) {
                $status_value .=  "\r\n" . date("Y-m-d H:i:s") . 'Email is not sent. Email error: ' . $mail->ErrorInfo;
            } else {
                $status_value .=  "\r\n" . date("Y-m-d H:i:s") . "Email is sent";
            }
            /*$mail->Debugoutput = function($str, $level) {
                $debug = "debug level $level; message: $str";
                return $debug;
            };*/
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS DOWN - {$searchResponseText}";
        }
    } else {
        //if there are more than 1 active live stream, notify webencode to make sure the correct Paw Patrol stream is up.
        $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THERE MAYBE 2 OR MORE LIVESTREAMS STREAMING - {$searchResponseText}";
        $body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n There maybe 2 or more livestreams live or scheduled. \r\n\r\n";
        $mail->setFrom("wencode@tvo.org");
        $mail->addAddress("wencode@tvo.org");
        $mail->addAddress("vtagarelli@tvo.org");
        $mail->addAddress("afernandes@tvo.org");
        $mail->addAddress("wilagan@tvo.org");
        $mail->addAddress("klennox@tvo.org");
        $mail->addAddress("rmichael@tvo.org");
        $mail->Subject = "TVOKids Live 24/7 Stream ";
        $mail->IsSMTP();
        $mail->Host = 'mail.tvo.org';
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = 2;
        $mail->Port = 587;
        $mail->Username = "wencode@tvo.org";
        $mail->Password = "webencode";
        //print out in the log file and email, the title of each active stream and if it's live
        foreach ($searchResponse['items'] as $item){
            $status_value .= "\r\n" . date("Y-m-d H:i:s") . " - " . $item['snippet']['title'] . " is " . $item['snippet']['liveBroadcastContent'];
            $body .= $item['snippet']['title'] . " is " . $item['snippet']['liveBroadcastContent'] . "\r\n";
        }
        $body .= "\r\n Please check The TVOKids Live Stream Channels in Lightspeed live at http://lsl-tvo:8089/signin and the TVOKids Channel at http://www.youtube.com/tvokids";
        $mail->Body = $body;
        if(!$mail->send()) {
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . 'Email is not sent. Email error: ' . $mail->ErrorInfo;
        } else {
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . "Email is sent";
        }
        /*$mail->Debugoutput = function($str, $level) {
            $debug = "debug level $level; message: $str";
            return $debug;
        };*/
    }

//write to log file
file_put_contents($status_file, $status_value, FILE_APPEND | LOCK_EX);

//catch Google exceptions and write them to log file.
} catch(Google_Service_Exception $e) {
    $status_value .=  "\r\n" . date("Y-m-d H:i:s") ."Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage() . "Stack trace is ".$e->getTraceAsString();
    file_put_contents($status_file, $status_value, FILE_APPEND | LOCK_EX);
}catch (Exception $e) {
    $status_value .=  "\r\n" . date("Y-m-d H:i:s") ."Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage() . "Stack trace is ".$e->getTraceAsString();
    file_put_contents($status_file, $status_value, FILE_APPEND | LOCK_EX);
}

?>