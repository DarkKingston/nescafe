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
-------------

The "Block component module" allows to add components in the block which will further maintain the information of component. Now, while creating custom block there will be option to add component in block level.


REQUIREMENTS
------------

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)
* Paragraphs EE (https://www.drupal.org/project/paragraphs_ee)


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

* Configure the component selection inside the Struture » Block Layout » Block Component: edit field and select component which you want to add.

* Go to that Block Content » custom block page under Block section.

* Add custom block with components and save.

FUNCTIONALITY
-------------

* Any component can be added in blocks.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does block--dsu--c--component-block.html.twig template is execute?

EXTEND
------

 * hook_theme_suggestions_block_alter for block template suggestions.
 * hook_theme for extending default template.


MAINTAINERS
-----------

* Nestle Webcms team.