---

# SMART RECIPE HUB

---

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

**Smart Recipe Hub** (from now on SRH) it's the backend where recipes are stored. Each recipe have many tools, ingredients, macronutrients and steps.

Recipes could be ordered by some parameters. Collections are groups or recipes (like summer, fresh, cold...) and Tags are other way to group it, like (breakfast, dinner).

This module provides integration of Drupal Website to Smart Recipe Hub.
The process will import and syncronize the recipes existing in the SRH depending on the market and endpoint configured in UI configuration.

The recipes and the entities related to the recipes are stored in the **new Content-type Recipe**.
The importation process could be configurable in the options menu. Otherwise, in every time-configurable cron process, the module will check if there are new, updated or unpublished recipes and import, update or unpublish it locally.
The **Synchronization tool** allows us to apply the synchronization process to one or more recipes by their "SRH ID". If we select to sync a recipe previous imported, then the module will resynchronise it.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* Video Embed Field (https://www.drupal.org/project/video_embed_field)


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.
* Please configure the module UI and test the connection to the SRH in /admin/config/ln_srh/config
* Once the connection test has passed, the recipes will be imported/syncronized periodically each time the Cron process is run.
* Likewise, it's also possible to synchronise a specific recipe manually through the edition menu "SRH Sync" or configuration tab "Sync Recipes" at /admin/config/lightnest/srh/sync-recipes.


  To uninstall the module, be aware that the content type Recipe and it's content are erased from the site.


CONFIGURATION
-------------

The configuration it's setted in Configuration->Lightnest->Lightnest SRH (/admin/config/lightnest/srh).
In addition, "Mapping" and "Sync Recipes" tabs are included, where we can map the recipe fields and synchronise recipes from their IDs respectively.

### 1. Connection

In this tab we must configure the server call to SMART RECIPE HUB

* **"Configure the Synchronization parameters of the recipes"**: Here we must fill in the "Author", "Interval time" and "Pages sync every time" to set the username to be used as recipe's author, set the interval time to execute migration in seconds and the number of pages we want to synchronise by iteration.
  We can also check if we want to "Active the recipe synchronization" to active the "Cron Process" (Without this options, there recipes will not be syncronize periodically); and if we want "Allow to delete obsolete recipes" for recipes that no longer exist in SRH to be removed from the system.


* **"Configure the server call to SMART RECIPE HUB"**: On this section we must set the parameters "Url", "Channel ID", "API Key" and "SRH Market Code"; ask to the Nestle Digital Hub to get it.


* **"Configure the language of the connector"**: Here we must set the language market code (locale code) to synchronise all the recipes with this code, and the "Content prefix" to be used to identify the recipe contents of this language.
  As can be seen, it's possible to add several languages simultaneously for multi-language markets.

### 2. Mapping

The Mapping tab allows us to set the field mapping for Recipes.

### 3. Sync Recipes

In this tab we can introduce one or more Recipe IDs to synchronise them specifically.


FUNCTIONALITY
-------------

* User can set configuration settings to perform recipe synchronization.

* Migration of all Recipes of a specific market to local can be performed.

* A new Recipe can be added, with Paragraphs and Taxonomies integrated.

* **"Mapping"**.

* SRH Sync options allows to re-synchronise a recipe through the recipe edit operations.

* An option for mass synchronization of recipes was added to the bulk actions list on /admin/content.

* A single or multiple Recipe can be synchronized by **"Sync Recipes"** configuration tab.


### STANDARD RECIPE FIELDS

- **Title**
- **SRH Id** (field_srh_id)
- **SRH Difficulty** (field_srh_difficulty)
- **SRH Ingredients** (field_srh_ingredients)
- **SRH Media Gallery** (field_srh_media_gallery)
- **SRH Steps** (field_srh_steps)
- **Metatags** (field_meta_tags)
- **Description** (body)
- **SRH Chef** (field_srh_chef)
- **SRH Tips** (field_srh_tips)
- **SRH Nutrients** (field_srh_nutrients)


### PARAGRAPHS INCLUDED

- **SRH Ingredient**
  - Full Name (field_c_title)
  - SRH Ingredient (field_srh_ingredient)
  - SRH Quantity (field_srh_quantity)
    - Quantity
    - Display
    - Grams
    - Fraction
  - SRH Unit Type (field_srh_unit_type)
  - SRH Preparation Hint (field_srh_preparation_hint)
  - SRH Is Nestle Product (field_srh_is_nestle_product)
  - SRH Tips (field_srh_tips)


- **SRH Step**
  - Description (field_c_text)
  - SRH Tips (field_srh_tips)


- **SRH Tip**
  - Title (field_c_title)
  - Description (field_c_text)
  - SRH Media (field_srh_media)


- **SRH Nutrient**
  - SRH Nutrient (field_srh_nutrient)
  - SRH Percentage (field_srh_percentage)


### TAXONOMIES INCLUDED

- **SRH Difficulty**
  - Name
  - SRH Id (field_srh_id)


- **SRH Ingredient**
  - Name
  - SRH Id (field_srh_id)


- **SRH Unit Type**
  - Name
  - SRH Id (field_srh_id)
  - SRH Abbreviation (field_srh_abbreviation)
  - SRH Plural Abbreviation (field_srh_plural_abbreviation)
  - SRH Plural Name (field_srh_plural_name)


- **SRH Nutrient**
  - Name
  - SRH Id (field_srh_id)

- **SRH Tip**
  - SRH Title (field_c_title)
  - SRH Description (field_c_text)
  - SRH Media (field_srh_media)


MAINTAINERS
-----------

* Nestle Webcms team.
