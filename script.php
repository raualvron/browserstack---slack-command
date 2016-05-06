<?php

//Getting autoload from composer
include 'vendor/autoload.php';

//Declaring classes to use in the script
use Alexschwarz89\Browserstack\Screenshots\Api;
use Alexschwarz89\Browserstack\Screenshots\Request;
use Alexschwarz89\Browserstack\Screenshots\CheckSlack;

// Default values for BrowserSlack
const BROWSERSTACK_ACCOUNT   = '';
const BROWSERSTACK_PASSWORD  = '';

// Getting value from POST method
$text = $_POST['text'];

//Split a string by string. Returns an array of strings
$textPos = explode(" ", $text);

// Getting value from POST method
$token = $_POST['token'];
$channel = $_POST['channel_id'];
$user = $_POST['user_name'];

//Creating new API object
$api  = new Api(BROWSERSTACK_ACCOUNT, BROWSERSTACK_PASSWORD);

//Creating new CheckSlack object 
$slackto = new CheckSlack();

//Browser JSON file
$file = 'browsers.json';

//If the token and channel (screenshot channel) are false, are you a bot?
if (!($slackto->checkSlack($token,$channel))) {
    echo "Are you a bot?..";
    exit();
}

//Getting the website text
if (isset($text) && !empty($text)) {

    //Getting the browsers device available from BrowserStack
    if($textPos[0] == "browsers") {
        $message = "Hi " . $user . ", you can see all the browsers <http://raul.dev.waracle.net/browsers.php|available>";
        $slackto->postToSlack($message, null);
        exit();

    } else if ($textPos[0] == "commands") {
        $message = "Hi " . $user . ", the slack commands available with /test are: 
        /test browsers ~ browsers available from BrowserStack 
        /test commands ~ all slack command available
        /test updateb ~ update browser.json with all device available from BrowserStack
        /test www.example.org ios ~ testing the website by ios device
        /test www.example.org android ~ testing the website by android device
        /test www.example.org web ~ testing the website by browsers laptops";
        $slackto->postToSlack($message, null);
        exit();
     }

    $message = "Hello " . $user . ", you screenshots for " . $textPos[0] . " tested by " . $textPos[1]  . " devices are";
    //Detecting type of the operating system
    if($textPos[1] == "ios") {

        // URL, os, os_version, browser, browser_version, device
        $request[0]    = Request::buildRequest($textPos[0], 'ios', '8.3', 'Mobile Safari', null, 'iPad Mini 2');
        $request[1]    = Request::buildRequest($textPos[0], 'ios', '7.0', 'Mobile Safari', null, 'iPad Mini');
        $request[2]    = Request::buildRequest($textPos[0], 'ios', '6.0', 'Mobile Safari', null, 'iPhone 5');
        $request[3]    = Request::buildRequest($textPos[0], 'ios', '5.1', 'Mobile Safari', null, 'iPhone 4S');
        $request[4]    = Request::buildRequest($textPos[0], 'ios', '5.0', 'Mobile Safari', null, 'iPad 2 (5.0)');

        //Screens each os
        $screen = ["iOs 8.3 ~ iPad Mini 2 ~ Mobile Safari" , "iOs 7.0 ~ iPad Mini ~ Mobile Safari" , "iOs 6.0 ~ iPhone 5 ~ Mobile Safari" , "iOs 5.1 ~ iPhone 4S ~ Mobile Safari", "iOs 5.0 ~ iPad 2 ~ Mobile Safari"];

    } else if ($textPos[1] == "android") {

        //Request each operating system
        // URL, os, os_version, browser, browser_version, device
        $request[0]    = Request::buildRequest($textPos[0], 'android', '5.0', 'Android Browser', null, 'Google Nexus 9');
        $request[1]    = Request::buildRequest($textPos[0], 'android', '5.0', 'Android Browser', null, 'Google Nexus 5');
        $request[2]    = Request::buildRequest($textPos[0], 'android', '4.4', 'Android Browser', null, 'Samsung Galaxy S5');
        $request[3]    = Request::buildRequest($textPos[0], 'android', '4.1', 'Android Browser', null, 'Samsung Galaxy S3');
        $request[4]    = Request::buildRequest($textPos[0], 'android', '4.0', 'Android Browser', null, 'Amazon Kindle Fire HD 8.9');

        //Screens each os
        $screen = ["Google Nexus 9 ~ 5.0 ~ Android Browser" , "Google Nexus 5 ~ 5.0 ~ Android Browser" , "Samsung Galaxy S5 ~ 4.4 ~ Android Browser" , "Samsung Galaxy S3 ~ 4.1 ~ Android Browser", "Amazon Kindle Fire HD 8.9  ~ 4.0 ~ Android Browser"];

    } else if ($textPos[1] == "web") {

        //Request each operating system
        // URL, os, os_version, browser, browser_version, device
        $request[0]    = Request::buildRequest($textPos[0], 'Windows', '10', 'ie', '11.0', null);
        $request[1]    = Request::buildRequest($textPos[0], 'Windows', '10', 'firefox', '44.0', null);
        $request[2]    = Request::buildRequest($textPos[0], 'Windows', '8.1', 'chrome', '49.0', null);
        $request[3]    = Request::buildRequest($textPos[0], 'OS X', 'Yosemite', 'safari', '8.0', null);
        $request[4]    = Request::buildRequest($textPos[0], 'OS X', 'El Capitan', 'chrome', '49.0', null);
        $request[5]    = Request::buildRequest($textPos[0], 'OS X', 'Mavericks', 'firefox', '44.0', null);

        //Screens each os
        $screen = ["Windows10 ~ ie ~ 11.0" , "Windows10 ~ firefox ~ 44.0" , "Windows8.1 ~ chrome ~ 49.0" , "OS X Yosemite ~ safari ~ 8.0", "OS X El Capitan ~ chrome ~ 49.0" , "OS X Mavericks ~ firefox ~ 44.0"];

    } else {

        $message = "Are you a bot?.. try it: e.g ./test www.example.org android|ios|web";
        echo $message;
        exit();
    }

    //Colors for the message
    $color = ["#2FA44F" , "#DE9E31" , "#D50200" , "#28D7E5" , "#2FA44F" , "#DE9E31" , "#D50200", "#28D7E5" , "#2FA44F"];
    
    $attachmentsA = array();

    //For each request
    for ($i=0; $i < count($request); $i++) { 

        $response = $api->sendRequest($request[$i]);

        if ($response->isSuccessful) {
            do {
                $status = $api->getJobStatus($response->jobId);
                if ($status->isFinished()) {
                    foreach ($status->finishedScreenshots as $screenshot) {
                        //Passing color, screen, url screenshot.
                        $attachments = array(  
                            "color" => $color[$i],
                            "author_name" => $screen[$i],
                            "title" => "Screenshot",
                            "title_link" => $screenshot->image_url
                            );

                        //Push into the array
                        array_push($attachmentsA, $attachments);

                    }
                    break;
                }

            } while (true);

        //Response is not successful.
        } else {
            echo 'Job creation failed. Reason: ' . $response->errorMessage . "\n";
            exit();
        }
    }
    
    $slackto->postToSlack($message, $attachmentsA);

//if text is empty..
} else {

    $message = "Are you a bot?..";
    echo $message;
    exit();

}