# stratus-weather-ivr

Contact our [Help Desk](https://www.spectrumvoip.com) as we will need to work with you to get calls pointed at your Web responder.

## This sample creates a Web Responder for our Stratus platform that:

1. Causes Stratus to play an announcement and wait for a 5 digit US ZIP code.
1. Queries https://openweathermap.org/ with that ZIP code and gets back the current weather.
1. Uses Amazon Polly text to speech to create a wav file of the returned data.
1. Causes Stratus to play that wav file.
1. Repeats waiting for another zip.

Notes:
* Requires /usr/bin/mpg123 and /usr/bin/sox to convert audio files created by Polly to format compatible with Stratus.
* Rename includes/creds.sample.php to includes/creds.php and update it with your keys.

## Our Stratus platform has the possibility to use "Web Responders".

For instance, if someone dials into a DID on our system, we can cause that call to interact with your web server via HTTP/S API calls.

Stratus will browse your web server and expects to receive XML "verbs" ( i.e. directions) and wav files.  Stratus will do things based on these instructions.  This gives you the ability create IVRs that do all sorts of things.

# There are three verbs you can give Stratus:
1. Play
1. Gather
1. Forward

# Verbs
All verbs take an "action" attribute. If the "action" attribute is present then control is returned to the URL given by the "action" attribute. If not present then the application ends.

## Play
The <Play> action plays the given .wav file and posts back to the given action URL.
For example,
```
<Play action='continue.php'>
 http://www.example.com/hello-world.wav
</Play>
```
This verb plays the .wav file and posts back (i.e., returns control) to continue.php.

To end the call, omit the action parameter.

## Gather
The <Gather> action gathers the given number of DTMF digits and posts back to the given action url, optionally playing the given .wav file.
numDigits 
the number of digits to gather, e.g., '1' (default=20)
For example,
```
<Gather numDigits='3' action='handle-account-number.php'>
 <Play>
  http://www.example.com/what-is-your-account-number.wav
 </Play>
<Gather>
```
This gathers 3 digits and posts back the relative URL handle-account-number.php?Digits=555.
The <Gather> verb posts back to the given action URL with the following parameters:
Digits 
the gathered digits, for example, 123

## Forward
The <Forward> action forwards to the given destination. The <Forward> verb does NOT take an "action" parameter. The <Forward> verb is effectively a "goto".
For example,
```
<Forward>
 2125551212
</Forward>
```

