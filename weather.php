<?php
require "creds.php";
session_start();
header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>';

function gather($digits,$action,$audio)
{
  echo "<Gather numDigits='$digits' action='$action'>";
  echo "<Play>$audio</Play>";
  echo "</Gather>";
}

function play($action,$audio)
{
  echo "<Play action='$action'>$audio</Play>";
}

function forward($location)
{
  echo "<Forward >$location</Forward>";
}



function yahooZip($zip)
{
    $BASE_URL = "http://query.yahooapis.com/v1/public/yql";
    $yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text="'.$zip.'")';
    $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json";
    // Make call with cURL
    $session = curl_init($yql_query_url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
    $json = curl_exec($session);
    // Convert JSON to PHP object
    $phpObj =  json_decode($json,true);
    //print_r($phpObj);

    $city = $phpObj['query']['results']['channel']['location']['city'];

    $currentText = $phpObj['query']['results']['channel']['item']['condition']['text'];
    $currentTemp = $phpObj['query']['results']['channel']['item']['condition']['temp'];

    $tomorrowText = $phpObj['query']['results']['channel']['item']['forecast'][1]['text'];
    $tomorrowTemp = $phpObj['query']['results']['channel']['item']['forecast'][1]['high'];

    $speech = "The current weather for ".$city." is ".$currentText." and ".$currentTemp." degrees. ";
    $speech .= "Expect a high tomorrow of ". $tomorrowTemp. " with a forcast of ". $tomorrowText;

    return $speech;

}

function awsSpeech($speech)
{
    global $aws_token;
    global $aws_key;
    
    require 'vendor/autoload.php';
    $s3 = new Aws\Polly\PollyClient([
      'version'     => 'latest',
      'region'      => 'us-west-2',
      'credentials' => [
        'key'    => $aws_token,
        'secret' => $aws_key
      ]
    ]);

    $result = $s3->synthesizeSpeech([
        'LexiconNames' => [],
        'OutputFormat' => 'mp3',
        'SampleRate' => '8000',
        'Text' => $speech,
        'TextType' => 'text',
        'VoiceId' => 'Joanna',
    ]);

    $tmpName = "polly".uniqid();
    file_put_contents("/tmp/".$tmpName.".mp3",
      $result['AudioStream']->getContents() );
    $cmd1 = '/usr/bin/mpg123 -w '."/tmp/".$tmpName.
      '.wav '."/tmp/".$tmpName.'.mp3';
    $cmd2 = '/usr/bin/sox '."/tmp/".$tmpName.'.wav '.
      ' -e mu-law -r 8000 -c 1 -b 8 '."audio/".$tmpName.".wav";
    exec($cmd1);
    exec($cmd2);
    return "audio/".$tmpName.".wav";
}



if (!isset($_REQUEST["case"])) {
  $speech = "Thank you for calling the NetSapiens UGM weather application. ";
  $speech .= "Please enter a zip code to get a weather report";
  gather(5,"weather.php?case=playzip",awsSpeech($speech));

}
else if ($_REQUEST["case"] == "playzip") {

  $speech = yahooZip($_REQUEST["Digits"]);

  $audioPath = awsSpeech($speech);

  play("weather.php",$audioPath);
}
