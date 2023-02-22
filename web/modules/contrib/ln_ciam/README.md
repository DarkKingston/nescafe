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

This module help us to integrate GConnector (Gigya connector) in drupal with all dependency.

REQUIREMENTS
------------

This module requires the gigya connector modules to be included in the modules/contrib folder.

Check below the installation instructions for D8 and D9.

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.

CONFIGURATION For Drupal 8 Gigya Setup
---------------------------------------

* Download latest 8.x tag for gigya connector (https://github.com/gigya/drupal8/tags)

* Download zip folder and rename it to gigya.

* Place this folder in the drupal/modules/contrib/*

* Enable the ln_ciam module as stated in the "installation" point.

* Once enabled the module go to '/admin/config/lightnest/ln-ciam/settings' URLs.

	* Update the gigya encryption key path.

* Configure the gigya API details '/admin/config/gigya/keys'.

	* The Gigya API Key for your site, as it is displayed in the Gigya admin console in the Dashboard screen.

	* Gigya Application Key : A Gigya Application Key is a key that you create specifically for your Drupal implementation.

	* Gigya Application Secret: The Gigya Application Secret key.

*  Configure the Gigya screen-Sets '/admin/config/gigya/screensets'.

	* REGISTRATION–LOGIN SCREEN-SET

	* EDIT PROFILE SCREEN-SET

* Implement RaaS Screen-Sets.

	*  Go to Structure > Block Layout.

	* Choose the desired region in the page in which you want the Gigya interface element to be displayed, and select the Place Block button next to that region.

	* In the Place Block window, type "gigya" to filter the blocks.

	* The "Gigya RaaS links" block will add Gigya screens as a pop-up window. Any of the other options, e.g. "Gigya RaaS Login", embeds the relevant Gigya screen into the page.

* Gigya RaaS Links: This block displays Gigya RaaS links that can be clicked by the site user to open any of the RaaS screen-sets in a pop-up window.

	* When the site user is not logged in, the block displays Login and Registration links. Clicking a link opens the Login or Registration screen-set, respectively, in a pop-up window.

	* When the site user is logged in, the block displays a Profile link. Clicking this link pops up the Edit Profile screen-set.

* For Implementing the custom logic on registration page.

	* Go in SAPCDC console (https://console.gigya.com/) and setup Extensions.

	* If you don't have access the SAPCDC. You can take help from gigya team to configure it.

	* First thing to do is to enable extensions on Parent ApiKey.

	* Go to the Parent Site. Click on Extension in Vertical tab and add Extension.

	* Extension URL: https://mydrupalenv.com/gigya-api/postextension.php

	* That the extension will execute before allowing registration.


CONFIGURATION For Drupal 9 Gigya Setup
---------------------------------------
* In the root composer please add these extension of dependency and run composer update.
	- "ext-json": "*",
	- "ext-openssl": "*",
	- "gigya/php-sdk": "^3.0"

* Once you add these composer dependency on the root composer file. Now download latest 9.x tag for gigya connector (https://github.com/gigya/drupal8/tags)

* Download zip folder and rename it to gigya.

* Place this folder in the drupal/modules/contrib/*

* Enable Lightnest ln_ciam module and other gigya module.

* Set the key path and all require configuration of the module.

* Once enabled the module go to '/admin/config/lightnest/ln-ciam/settings' URLs.

	* Update the gigya encryption key path.

* Configure the gigya API details '/admin/config/gigya/keys'.

	* The Gigya API Key for your site, as it is displayed in the Gigya admin console in the Dashboard screen.

	* Gigya Application Key : A Gigya Application Key is a key that you create specifically for your Drupal implementation.

	* Gigya Application Secret: The Gigya Application Secret key.

*  Configure the Gigya screen-Sets '/admin/config/gigya/screensets'.

	* REGISTRATION–LOGIN SCREEN-SET

	* EDIT PROFILE SCREEN-SET

* Implement RaaS Screen-Sets.

	*  Go to Structure > Block Layout.

	* Choose the desired region in the page in which you want the Gigya interface element to be displayed, and select the Place Block button next to that region.

	* In the Place Block window, type "gigya" to filter the blocks.

	* The "Gigya RaaS links" block will add Gigya screens as a pop-up window. Any of the other options, e.g. "Gigya RaaS Login", embeds the relevant Gigya screen into the page.

* Gigya RaaS Links: This block displays Gigya RaaS links that can be clicked by the site user to open any of the RaaS screen-sets in a pop-up window.

	* When the site user is not logged in, the block displays Login and Registration links. Clicking a link opens the Login or Registration screen-set, respectively, in a pop-up window.

	* When the site user is logged in, the block displays a Profile link. Clicking this link pops up the Edit Profile screen-set.

* For Implementing the custom logic on registration page.

	* Go in SAPCDC console (https://console.gigya.com/) and setup Extensions.

	* If you don't have access the SAPCDC. You can take help from gigya team to configure it.

	* First thing to do is to enable extensions on Parent ApiKey.

	* Go to the Parent Site. Click on Extension in Vertical tab and add Extension.

	* Extension URL: https://mydrupalenv.com/gigya-api/postextension.php

	* That the extension will execute before allowing registration.

FUNCTIONALITY
-------------

* With the GConnector, We easily implement such features as registration, authentication, profile management, data analytics and third-party integrations.

* Increase registration rates and identify customers across devices, consolidate data into rich customer profiles, and provide better service, products and experiences by integrating data into marketing and service applications.

* Customer Identity (RaaS) is Gigya's end-to-end identity management system that offers cloud-based user registration as well as social login, allowing sites to maintain a unified user database for both social and traditional site authentication.

* Define the end point and build own logic as per the requirement.

TROUBLESHOOTING
---------------

* If Gigya Rass Link is not visiblev on Drupal site.

	- Check the Gigya API configuration details '/admin/config/gigya/keys'.

	- Screen-Sets is defind or not '/admin/config/gigya/screensets'.

	- Gigya Raas Link block is Place or not '/admin/structure/block'.

	- If everything is configured properly but Gigya Raas Link is not visible. Check https://developers.gigya.com/display/GD/Drupal+8 document and validate everything is configure in proper way.

	- Connect with with SAPCDC/gigya team for checking the API details confirmation.

EXTEND
------

* hook_ln_ciam_query_alter for overriding the query.
* Read ln_ciam_api.php for more informations to alter the query.

MAINTAINERS
-----------

* Nestle Webcms team.
