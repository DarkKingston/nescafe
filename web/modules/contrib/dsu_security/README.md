CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Troubleshooting
 * Maintainers

INTRODUCTION
------------

This module help us provide security configuration on the site.


REQUIREMENTS
------------

This module requires the following modules:

* Password Policy(https://www.drupal.org/project/password_policy)
* Username Enumeration Prevention (https://www.drupal.org/project/username_enumeration_prevention)
* Honeypot(https://www.drupal.org/project/honeypot)
* Session Limit (https://www.drupal.org/project/session_limit)
* Login Security(https://www.drupal.org/project/login_security)
* Automated Logout(https://www.drupal.org/project/autologout)
* Password Reset Landing Page (PRLP) (https://www.drupal.org/project/prlp)
* Security Kit (https://www.drupal.org/project/seckit)
* Security Review (https://www.drupal.org/project/security_review)
* Noreferrer (https://www.drupal.org/project/noreferrer)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

* No configuration required.


FUNCTIONALITY
-------------

* Disable reqire messages for security from user login form.

* Disable reqire messages for security from reset password form.

* Validate password policy on registration form.

* Send an email, if user already exist and try to register again with same email or username.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Are the "Access administration menu" and "Use the administration pages
     and help" permissions enabled for the appropriate roles?


MAINTAINERS
-----------

* Nestle Webcms team.