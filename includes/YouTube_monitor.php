<?php
function youtubeMonitor($channel,$status_value){
    if ($channel == "YouTube Kids"){
        $channelId = "UCGPPdIn67ZCLhhHABoQpNUw";
    } elseif ($channel =="YouTube Preschool"){
        $channelId = "UCuT5Z5gyoh1_HzncS_gaOkw";
    } elseif ($channel == "YouTube Paw Patrol"){
        $channelId = "UCvh1WRSaV66pRnkHMf0by_g";
    } elseif ($channel == "YouTube / Main"){
        $channelId = "UCerKNh8xgFXstLdgVlikaxQ";
    } elseif ($channel == "YouTube / Main: Support TVO"){
        $channelId = "CerKNh8xgFXstLdgVlikaxQ";
    } elseif ($channel == "YouTube / TAWSP"){
        $channelId = "UCu_u-P3cBFO7D-sAjxd_I-w";
    } elseif ($channel == "YouTube / Private"){
        $channelId = "UCl6lLijZZEeUWPFsFJQ3mwQ";
    } elseif ($channel == "YouTube / TeachOntario"){
        $channelId = "kJ5v0nw03ZZO_pPxDoyRdQ";
    }
    

    //Pointer to Google API Library
    require_once '../vendor/autoload.php';

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
        $searchResponse = $youtube->search->listSearch('snippet',array('eventType'=> 'live', 'type'=>'video', 'channelId'=> $channelId));
        $searchResponseText = json_encode($searchResponse);
        $totalResults = $searchResponse['pageInfo']['totalResults'];

        //totalResults is the number of active live streaming events
        if ($totalResults == 0){
            //totalResults == 0 if there are no active live streaming events 
            //email webencode inbox if the stream is down
            /*$mail_to = "wilagan@tvo.org";
            $subject = "{$channel} 24/7 Livestream down";
            $headers = "From: wencode@tvo.org";
            $body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n {$channel} Livestream is down. \r\n\r\n Please check The '{$channel} Loop Channel' in Lightspeed live at http://lsl-tvo:8089/signin and http://www.youtube.com/tvokidspawpatrol";
            mail($mail_to,$subject,$body,$headers);*/
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS DOWN - {$searchResponseText}";
        } else if ($totalResults == 1){
            //if there is 1 active live stream up
            //check to make sure that the correct stream is up
            if (strpos($searchResponse['items'][0]['snippet']['title'],"Test Stream") !== false){
                $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS UP - {$searchResponseText}";
            } else{
                //if there is an active live stream up, but it doesn't have the correct title, then the stream is down, because the title will always be the same.
                /*$mail_to = "wilagan@tvo.org";
                $subject = "{$channel} 24/7 Livestream down";
                $headers = "From: wencode@tvo.org";
                $body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n The {$channel} Livestream is down. \r\n\r\n Please check The '{$channel} Loop Channel' in Lightspeed live at http://lsl-tvo:8089/signin and http://www.youtube.com/tvokidspawpatrol";
                mail($mail_to,$subject,$body,$headers);*/
                $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THE LIVE STREAM IS DOWN - {$searchResponseText}";
            }
        } else {
            //if there are more than 1 active live stream, notify webencode to make sure the correct Paw Patrol stream is up.
            $status_value .=  "\r\n" . date("Y-m-d H:i:s") . " - THERE MAYBE 2 OR MORE LIVESTREAMS STREAMING - {$searchResponseText}";
            /*$mail_to = "wilagan@tvo.org";
            $subject = "{$channel} 24/7 Livestream";
            $headers = "From: wencode@tvo.org";
            $body = "AUTOMATED EMAIL. PLEASE DO NOT RESPOND. \r\n\r\n There maybe 2 or more livestreams live or scheduled. \r\n\r\n";
            $liveArray = array();
            //print out in the log file and email, the title of each active stream and if it's live
            foreach ($searchResponse['items'] as $item){
                $status_value .= "\r\n" . date("Y-m-d H:i:s") . " - " . $item['snippet']['title'] . " is " . $item['snippet']['liveBroadcastContent'];
                $body .= $item['snippet']['title'] . " is " . $item['snippet']['liveBroadcastContent'] . "\r\n";
            }
            $body .= "\r\n Please check The '{$channel} Channel' in Lightspeed live at http://lsl-tvo:8089/signin and the {$channel} Channel at http://www.youtube.com/tvokidspawpatrol";
            mail($mail_to,$subject,$body,$headers);*/
        }
    return $status_value;
    //write to log file
    //file_put_contents($status_file, $status_value, FILE_APPEND | LOCK_EX);

    //catch Google exceptions and write them to log file.
    } catch(Google_Service_Exception $e) {
        $status_value .=  "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage() . "Stack trace is ".$e->getTraceAsString();
        file_put_contents($status_file, $status_value, FILE_APPEND | LOCK_EX);
    }catch (Exception $e) {
        $status_value .=  "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage() . "Stack trace is ".$e->getTraceAsString();
        file_put_contents($status_file, $status_value, FILE_APPEND | LOCK_EX);
    }
}
?>