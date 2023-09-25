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

This module help us to integrate third party API and send the data in API after form submission.


REQUIREMENTS
------------

This module requires the following modules:

* advanced_datalayer(https://www.drupal.org/project/advanced_datalayer)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

* Once enabled the module go to Configuration > Lightnest > Engage Contact > Engage Settings.

* Engage Form can be enabled and disabled globally.

* Enageg API: Update the setting as per site API.

* Manage Form: show, hide, required and non required fields as per requirement.

* Additional Footer Notes contact: Add footer notes if require.

* Validate http://site_url/contact_us URLs on site.


FUNCTIONALITY
-------------

* Create dynamic form as per site requirement.

* On enabled engage form, user should set relative URL for form page.

* Store 3 type data from a single form. Question/Suggestion, complaint and priase.

* Event tracking from GTM.

* Site owners can manage dynamic path and also enable/disbale contact form from the configuration.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does dsu-engage-form.html.twig template is execute?

EXTEND
------

 * Need for extending this module just use default template and markup based on UI of contact form.

MAINTAINERS
-----------

* Nestle Webcms team.