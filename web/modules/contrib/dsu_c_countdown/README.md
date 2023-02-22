CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Troubleshooting
 * Maintainers
 * Extend

INTRODUCTION
------------

The Lightnest Countdown module provides datetime field to display the field as a timer or countdown.

REQUIREMENTS
------------

This module requires the following modules:

 * Paragraphs (https://www.drupal.org/project/paragraphs)
 * Field Timer (https://www.drupal.org/project/field_timer)
 * County (https://github.com/brilsergei/county)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

* county and countdown JS libraries is required

* Add below script in your project root composer.json file.

  * Note:- Inside `scripts` section, `libraries` path is hardcoded. It will work if `composer.json` file and `libraries` folders are in same location. Otherwise make changes in `libraries` path.

````
"repositories": {
  "brilsergei": {
    "type": "package",
    "package": {
      "name": "brilsergei/county",
      "version": "master",
      "type": "drupal-library",
      "dist": {
      "url": "https://github.com/brilsergei/county/archive/master.zip",
      "type": "zip"
      },
      "require": {
        "composer/installers": "^1.2.0"
      }
    }
  },
  "kbwood": {
    "type": "package",
    "package": {
      "name": "kbwood/countdown",
      "version": "master",
      "type": "drupal-library",
      "dist": {
      "url": "https://github.com/kbwood/countdown/archive/master.zip",
      "type": "zip"
      },
      "require": {
      "composer/installers": "^1.2.0"
      }
    }
  }
},
"require": {
  "brilsergei/county": "master",
  "kbwood/countdown": "master",
},
"scripts": {
  "pre-install-cmd": [
    "php -r \"shell_exec('rm -rf libraries/jquery.countdown');\""
  ],
  "post-install-cmd": [
    "php -r \"shell_exec('mv libraries/countdown libraries/jquery.countdown');\"",
    "php -r \"shell_exec('mv libraries/jquery.countdown/dist/* libraries/jquery.countdown/');\""
  ],
  "pre-update-cmd": [
    "php -r \"shell_exec('rm -rf libraries/jquery.countdown');\""
  ],
  "post-update-cmd": [
    "php -r \"shell_exec('mv libraries/countdown libraries/jquery.countdown');\"",
    "php -r \"shell_exec('mv libraries/jquery.countdown/dist/* libraries/jquery.countdown/');\""
  ]
}

````

* Execute **composer update** command on command prompt or git Bash in your project directory.

CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Countdown paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A Date and time can be added.

* Timezone can be added.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-countdown.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Countdown.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.