CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * For developers
 * TODO

INTRODUCTION
------------

The Advanced Datalayer provides flexible possibility to manipulate datalayer
page variables https://developers.google.com/tag-manager/devguide.
This module is API module and provides base plugins code and admin part
to create and manipulate your own datalayer page variables (tags).

REQUIREMENTS
------------

This module requires the following module:

 * Token - https://www.drupal.org/project/token

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.


CONFIGURATION
-------------

 1. Configure main defaults at
    Administration >> Configuration >> Search and metadata >> Datalayer tags
    (/admin/config/search/advanced-datalayer/page-variables).
 2. Additional bundle defaults can be added by clicking on "Add default
    datalayer tags".
 3. Tokens can be used in fields values to make tags values dynamic
 3. Module uses inherit and override system to build final tags values for any
    level (globals, entity type, entity bundle)
 4. Page (/admin/config/search/advanced-datalayer/page-variables/settings)
    settings that allowed control which datalayer tags are available for
    each entity bundle.
 5. To manipulate datalayer tags in specific entity, add "Datalayer tags" field
    to this entity
 6. Submodule 'Context Advanced Datalayer' integrate context and Advanced
    Datalayer, it allowed override
    Datalayer tags based on different conditions from context.

FOR DEVELOPERS
-------
 - basic approach, inherit logic and architecture are similar to the metatag
   module.
 - module based on plugin system, any datalayer tag is a plugin.
 - tags can be grouped, groups are also plugins.
 - global tags only available in global default edit form
 - out-of-box module provided GroupBase class and Root group
   (for root elements).
 - for tags exist base classes DatalayerNameBase and DatalayerDynamicNameBase
   for standard and dynamic calculated tags.
 - see Annotation for every type of plugin for details.
 - see Advanced Datalayer example module for examples

TODO
-------
 - functionality and Ajax command to work with GTM events
 - add possibility to add simple base tags via admin part
 - integrate tests
