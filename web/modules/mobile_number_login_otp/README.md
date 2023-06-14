CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Maintainers


INTRODUCTION
------------

Mobile Number Login OTP module allows the user to login with the mobile number
which helps the login system development and when the time of creation of the
account as the mobile number is mandatory we need to show it as per the
updated mobile number.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/mobile_number_login_otp

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/search/mobile_number_login_otp



INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   See https://www.drupal.org/node/895232 for further information.

 * By Using composer, composer require "drupal/mobile_number_login_otp"

USAGE
-----------

 * This service is been implemented by the thirdparty service "Kaleyra".
   See https://developers.kaleyra.io/docs/sms-api for further information.

 * The settings of the file will be included as follows
     Api Key --> Api Key for the Kaleyra to be used.
     OTP Expiry -->  Mention in seconds.
     Sender --> Add the sender ID from the Platforms like JIO, BSNL, AIRTEL.
     SID --> It will be generated from the Kaleyra.
     Welcome OTP ID --> Will help to identify the exact Template-ID
     Login OTP ID --> Will help to identify the exact Template-ID
     OTP Max Count --> Count of the OTP's need to send with in 5 minutes.
     Template content --> Will help to add all the templates with content
     and the dynamic varialbes as {#var1#},{#var2#},{#var3#},...

MAINTAINERS
-----------

Current maintainers:
 * Praveen Achanta (praveen3555) - https://www.drupal.org/u/praveen3555
