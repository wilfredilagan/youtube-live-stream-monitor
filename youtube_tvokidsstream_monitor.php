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
$client_secret = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
$client_id = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
$scope = array('https://www.googleapis.com/auth/youtubepartner','https://www.googleapis.com/auth/youtube');


try{
   // YouTube Client initialization
   $client = new Google_Client();
   $client->setApplicationName($application_name);
   $client->setClientId($client_id);
   $client->setClientSecret($client_secret);
   $client->setAccessType('offline');
   $client->refreshToken('1/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
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
    $searchResponse = $youtube->search->listSearch('snippet',array('eventType'=> 'live', 'type'=>'video', 'channelId'=> 'xxxxxxxxxxxxxxxxxxxxxxxxxxx'));
    
    //if unlisted
    //$searchResponse = $youtube->videos->listVideos('snippet,contentDetails', array('id' => 'ERsxXPT4QZ0'));
    print_r($searchResponse);
    $searchResponseText = json_encode($searchResponse['modelData']);
    $totalResults = $searchResponse['pageInfo']['totalResults'];
    print_r($totalResults);

    //Private: UCl6lLijZZEeUWPFsFJQ3mwQ
    //TVOKids: UCGPPdIn67ZCLhhHABoQpNUw

    //totalResults is the number of active live streaming events
    if ($totalResults == 0){
        //totalResults == 0 if there are no active live streaming events 
        //email webencode inbox if the stream is down
        $mail->setFrom("xxxxxxxxxxxxxxx");
        $mail->Subject = "Live Stream down";
        $mail->Body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n The Live Stream is down. \r\n\r\n Please check The Live Stream encoder";
        $mail->IsSMTP();
        $mail->Host = 'xxxxxxxxxxxxxxx';
        $mail->SMTPAuth = true;
        //$mail->SMTPDebug = 2;
        $mail->Port = xx;
        $mail->Username = "xxxxxxxxxxxx";
        $mail->Password = "xxxxxxxxxxx";
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
        if (strpos($searchResponse['items'][0]['snippet']['title'],"xxxxxxxxx") !== false){
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS UP - {$searchResponseText}";
        } else{
            //if there is an active live stream up, but it doesn't have the correct title, then the stream is down, because the title will always be the same.
            $mail->setFrom("xxxxxxxxxxxx");
            $mail->Subject = "Live Stream down";
            $mail->Body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n The Live Stream is down. \r\n\r\n Please check The Live Stream encoder";
            $mail->IsSMTP();
            $mail->Host = 'xxxxxxxxxxxxxxxxxxxx';
            $mail->SMTPAuth = true;
            //$mail->SMTPDebug = 2;
            $mail->Port = 587;
            $mail->Username = "xxxxxxxxxxx";
            $mail->Password = "xxxxxxxxxxxxx";
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
        $mail->setFrom("xxxxxxxxxxxx");
        $mail->Subject = "Live Stream ";
        $mail->IsSMTP();
        $mail->Host = 'xxxxxx';
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = 2;
        $mail->Port = 587;
        $mail->Username = "xxxxxxxxxxxxx";
        $mail->Password = "xxxxxxxxxxxxx";
        //print out in the log file and email, the title of each active stream and if it's live
        foreach ($searchResponse['items'] as $item){
            $status_value .= "\r\n" . date("Y-m-d H:i:s") . " - " . $item['snippet']['title'] . " is " . $item['snippet']['liveBroadcastContent'];
            $body .= $item['snippet']['title'] . " is " . $item['snippet']['liveBroadcastContent'] . "\r\n";
        }
        $body .= "\r\n Please check The Live Stream";
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