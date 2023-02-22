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

Creates sample content to Lightnest components.

REQUIREMENTS
------------

This module requires the following modules:

   * dsu_c_core

INSTALLATION
------------

   * Install as you would normally install a contributed Drupal module. Visit https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

   * No configuration require.

FUNCTIONALITY
-------------

   * Creates a node of type "Component page" with sample content. The sample content is taken from Yaml files located in
      the modules that are implementing the hook_ln_sample_content.
   
   * The first time we create sample content, both a taxonomy term named "Sample content" and some demo teaser nodes will be created.
     This content will be deleted when uninstall the module.
	 
   * A "Sample Color" taxonomy will create for CTA button background color in the components. Content and term will be deleted when uninstall the module.

   * "Sample media 1" and  "Sample media 2" taxonomy will create for social buttons. Content and term will be deleted when uninstall the module.

INSTRUCTIONS TO CREATE TEMPLATES
--------------------------------
   * Every component needs their own template in Yaml format.
   * Common fields and their value:
   
            type: machine_name_component
            
            component_description: Text to show a description of the component, it will show above every component.
            
   * How to structure fields:
     - Date fields:
     
            field_date_name: "2025-08-07T08:00:00"
          
     - Timezone fields:
          
            field_timezone_name: "Europe/Madrid"
          
     - Text fields
     
            field_name: field_text
            
     - Boolean fields
            
            field_name: field_value
            
     - Numeric fields (decimal, float, integer,...)
     
            field_name: field_value
        
     - Paragraph fields:
     
            field_paragraph_name: 
                paragraph:
                    item_0:
                        type: machine_name_paragraph
                        field_1: value
                        field_2: value
                        ...
                    item_1:
                        type: machine_name_paragraph
                        field_1: value
                        field_2: value
                        ...
                            
     - Entity relation fields. This structure takes and show random nodes about CT passed in every item:
            
            field_entity_relation_name:
              entity_relation:
                item_0: content_type_machine_name
                item_1: content_type_machine_name
                item_2: content_type_machine_name
                ...                    
     
     - Link fields:
            
            field_link_name:
                links:
                    item_0:
                        uri: value
                        title: value
                        options:
                            attributes:
                                target: _blank
     
     - Tax term fields:
     
            field_tax_term_name:
                tax_term_name: term_name
                
     - Image field. The image_name value, have to match with the name of the image inside media folder.
     
            field_image_name:
                image_name: image_name.extension (nestle.jpg)
                image_title: value
     
     - Document field. The document_name value, have to match with the name of the document inside media folder. 
            
            field_document_name:
                document_name: docuement_name.extension (document.pdf)
                document_title: value                                                
                        

TROUBLESHOOTING
---------------

   * If there are empty fields, you have to check the Yaml file in the component module that has the field/s empty.

   * If there are images or documents empty in the components, you have to check that the name is correctly mapped in Yaml files.

MAINTAINERS
-----------

   * Nestle Webcms team.
