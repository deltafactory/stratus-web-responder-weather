# stratus-weather-ivr

This sample creates a Web Responder for our Stratus platform that:

1. Causes Stratus to play an announcement and wait for a 5 digit US ZIP code.
2. Queries https://openweathermap.org/ with that ZIP code and gets back the current weather.
3. Uses Amazon Polly text to speech to create a wav file of the returned data.
4. Causes Stratus to play that wav file
5. Repeats waiting for another zip.

Note:  Rename includes/creds.sample.php to includes/creds.php and update it with your keys

