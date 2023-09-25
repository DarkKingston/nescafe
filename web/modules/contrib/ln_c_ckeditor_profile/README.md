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

This module helps us to add space between two or more components.

The Lightnest Ckeditor Profile module is used to add basic CKEditor profile in components


REQUIREMENTS
------------

This module requires the following modules:

* drupal/linkit

* drupal/editor_advanced_link

* drupal/editor_file

* drupal/ckwordcount

* drupal/colorbutton

* drupal/ckeditor_font

* drupal/anchor_link


Libraries are required for this module is

* https://download.ckeditor.com/font/releases/font_4.10.1.zip
* https://github.com/w8tcha/CKEditor-WordCount-Plugin/archive/v1.17.6.zip
* https://download.ckeditor.com/colorbutton/releases/colorbutton_4.14.1.zip
* https://download.ckeditor.com/panelbutton/releases/panelbutton_4.14.1.zip
* https://download.ckeditor.com/fakeobjects/releases/fakeobjects_4.14.1.zip
* https://download.ckeditor.com/link/releases/link_4.14.1.zip


Add these keys in your root composer.json files to get these libraries.
* "w8tcha/ckeditor-wordcount-plugin": "v1.17.6",
* "ckeditor-plugin/font": "^4.10",
* "ckeditor/colorbutton": "4.14.1",
* "ckeditor/fakeobjects": "4.14.1",
* "ckeditor/link": "4.14.1",
* "ckeditor/panelbutton": "4.14.1",
* "ckeditor-plugin/font": "^4.10",

Please add these repositories on your root composer.json file.

* Go to ln_c_ckeditor_profile/composer.json
* Check composer.json repository key with all libraries.
* Add this on your root composer and run update.


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Go to any content type and add Ckeditor Profile type fields for text formated type.

* Go to that Node type page under crate a node form and check the editor.


FUNCTIONALITY
-------------

* User can type and check word count on editor.
* User can upload images/documents/video/files.
* User can choose indentations/color/fonts/size
* User can choose link/media/internal link and external link


TROUBLESHOOTING
---------------

* If the content does not display, check the following:
- libraries properly added and check console if any error is coming.



EXTEND
------

* Hook_theme for extending default template of Lightnest paragraph Ckeditor Profile.
* Override default css libraries to change default UI/UX.
 
MAINTAINERS
-----------

* Nestle Webcms team.