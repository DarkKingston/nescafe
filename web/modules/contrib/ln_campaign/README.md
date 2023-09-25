CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Maintainers

INTRODUCTION
------------

Lightnest campaign provides the functionality to create promotions. By default the module creates four types of promotions "Cashback", "Lottery", "Lottery Buyer", "Winning Moment", "Winning Moment Buyer". In addition to this, an administration will be available that will allow us to consult the participations of the users in said promotions, allowing an administrator to make modifications in said participations such as changing the status of said promotions.
Participations have a workflow that allows us to keep track of the current status of promotions. For Lightnest campaign it provides a workflow with four available states "Pending", "Valid", "Invalid" and "Paid". Depending on which type of campaign the participation belongs to, these states will have one meaning or another. In all promotions the initial status will be "Pending" and later an administrator will be in charge of changing the status of said promotion. In the particular case of "Winning Moment" and "Winning Moment Buyer", if at the time of participation we are not in a "winning moment" or there is already a winner at said "winning moment", the participation will automatically go to status " Invalid".


REQUIREMENTS
------------

This module requires the following modules:

* Ds (https://www.drupal.org/project/ds)
* Webform (https://www.drupal.org/project/webform)
* Webform Iban Field (https://www.drupal.org/project/webform_iban_field)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

* To create a campaign you must access "/admin/content/ln-campaign" and click on the "Add campaign" button. A menu will appear and the type of campaign you want to create is chosen. In that same url you can manage the campaigns, each campaign has a menu with a series of available operations, "Edit", "Results" and "Delete".
  * Edit: allows you to edit the campaign.
  * Result: redirects to the list of all the participations of said campaign.
  * Delete: allows you to delete the campaign.
* To manage the results of a campaign, go to "/admin/structure/webform/manage/{campaign_type}/results/submissions". In this administration page we have available a series of massive operations that allow us to eliminate the selected participations and change their status.
* There are four types of participation forms, one for each type of campaign. If you need to modify any of them, you must access "/admin/structure/webform", a list of available forms appears. The forms added by the module are identified with the category "ln_campaign". To modify any of them, click on the "build" button and access the administration of said form, allowing you to add/remove fields, modify their labels, modify the emails they send to participating users...
* In product purchase promotions we have an "Establishment" field in which the purchase establishment is chosen. To add the establishments that will be available, you must access "/admin/structure/webform/options/manage/establishment/edit", add the options and save.

FUNCTIONALITY
-------------

* A new entity type "Campaign" will be created.
* A new type of campagin "Cashback" will be created.
* A new type of campagin "Lottery" will be created.
* A new type of campagin "Lottery Buyer" will be created.
* A new type of campagin "Winning Moment" will be created.
* A new type of campagin "Winning Moment Buyer" will be created.
* A new view "Campaigns" of "Campaigns" teasers will be created.
* A new webform "Cashback" will be created.
* A new webform "Lottery" will be created.
* A new webform "Lottery Buyer" will be created.
* A new webform "Winning Moment" will be created.
* A new webform "Winning Moment Buyer" will be created.

MAINTAINERS
-----------

* Nestle Webcms team.
