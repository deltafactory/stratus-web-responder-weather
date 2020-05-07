<?php
require "includes/creds.php";
session_start();
header("Content-Type: text/xml");

# The current weather for Coppell is sunny  and 90 degrees. Expect a high tomorrow of 91 with a forcast of clouds.

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

function getWeather($zip)
{
    global $openweathermapkey;

    $BASE_URL = "https://api.openweathermap.org/data/2.5/weather?zip=$zip,us&appid=$openweathermapkey";
    $session = curl_init($BASE_URL);
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
    $json = curl_exec($session);
    $phpObj =  json_decode($json,true);

#    echo "$BASE_URL\n";
#    print_r($phpObj);

    if ( isset($phpObj['name']) ) {
        $temp         = round( 9/5*($phpObj['main']['temp']-273.15)+32 );    # F
        $city         = $phpObj['name'];
        $weather_desc = $phpObj['weather'][0]['description'];
        $wind         = round( $phpObj['wind']['speed'] );
        $humidity     = $phpObj['main']['humidity'];

        $speech = "The current temperature for $city is $temp degrees.     $weather_desc with humidity at $humidity percent    and wind of $wind miles per hour.";
    } else {
        $speech = "Sorry but we could not find that ZIP Code.";
    }
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
#        'VoiceId' => 'Joanna',
        'VoiceId' => 'Salli',
    ]);

    $tmpName = "polly".uniqid();
    file_put_contents("/tmp/".$tmpName.".mp3",
      $result['AudioStream']->getContents() );
    $cmd1 = '/usr/bin/mpg123 -w '."/tmp/".$tmpName.
      '.wav '."/tmp/".$tmpName.'.mp3';
    $cmd2 = '/usr/bin/sox '."/tmp/".$tmpName.'.wav '.
      ' -e mu-law -r 8000 -c 1 -b 8 '."audio/".$tmpName.".wav";
    $out1 = exec($cmd1);
    $out2 = exec($cmd2);
#    echo "out1: $out1\n";
#    echo "out2: $out2\n";
    return "audio/".$tmpName.".wav";
}



if (!isset($_REQUEST["case"])) {
  gather(5,"index.php?case=playzip","audio_perm/weather_announcement.wav");

}
else if ($_REQUEST["case"] == "playzip") {

  $speech = getWeather($_REQUEST["Digits"]);

  # Try to create the wav file
  try {
    $audioPath = awsSpeech($speech);
  } catch ( Exception $e ) {
    $audioPath = "audio_perm/aws_error.wav";
  }

  play("index.php",$audioPath);
}

?>
