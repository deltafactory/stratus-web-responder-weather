# stratus-weather-ivr

This sample creates a Web Responder for our Stratus platform that:

1. Causes Stratus to play an announcement and wait for a 5 digit US ZIP code.
1. Queries https://openweathermap.org/ with that ZIP code and gets back the current weather.
1. Uses Amazon Polly text to speech to create a wav file of the returned data.
1. Causes Stratus to play that wav file.
1. Repeats waiting for another zip.

Notes:
* Requires /usr/bin/mpg123 and /usr/bin/sox to convert audio files created by Polly to format compatible with Stratus.
* Rename includes/creds.sample.php to includes/creds.php and update it with your keys.

