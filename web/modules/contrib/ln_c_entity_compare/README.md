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

The Lightnest Entity Compare module adds a new components that allows displaying comparison tables in any page


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Visit admin/config/lightnest/ln-c-entity-compare and choose which entity types and bundles should be available for comparison, as well as the view mode that should be used to display them

* Optional: visit the display settings page of the entity bundles enabled and configure which fields should be displayed and in which order. If this step is skipped, fields will be rendered using the default display of the entity bundle.

FUNCTIONALITY
-------------

* A new type of paragraph "Content: Entity Compare" will be created

* Add the "Content: Entity Compare" paragraph to a component page

* Select the type and bundle of the entities you would like to display in the comparison tables

* Choose how many entities should be compared at the same time

* Optional: Select specific entities that should be available for comparison. Leave empty to allow all entities

MAINTAINERS
-----------

* Nestle Webcms team.
