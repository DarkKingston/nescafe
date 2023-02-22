CONTENTS OF THIS FILE
----------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

 
INTRODUCTION
------------
 On Multiple submitting of reset password form causes to multiple email notifications to a user in a sequence. Any random email notification can be use to reset password, which causes to vulnerability issue i.e. only last generated notification email should be valid to reset the password, all others notifications should get expired.
 
 To solve this vulnerability issue, this module expires all the multiple notifications except last notification email sent to user. 

 * For a full description of the module visit:
   https://www.drupal.org/project/expire_reset_pass_link

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/expire_reset_pass_link

REQUIREMENTS
------------
This module requires no modules outside of Drupal core.

INSTALLATION
--------------
 * Install the Redirect after login module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.

CONFIGURATION
--------------
  None

MAINTAINERS
------------

 * Piyush (piyush1986) - https://www.drupal.org/u/piyush1986
 * Shamsher Alam (Shamsher_Alam) - https://www.drupal.org/u/Shamsher_Alam
 * Jaswinder Singh (chera.jaswinder) - https://www.drupal.org/u/chera.jaswinder

Supporting organization:

 * Sapient - https://www.drupal.org/sapient

Sapient is a marketing and consulting company that provides business, marketing,
and technology services to clients.