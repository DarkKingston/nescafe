# Bazaarvoice Integration

This module help us to integrate bazaarvoice Reviews and star rating.

Description
===========
The Lightnest Bazaarvoice module is a suite of modules that provide a wide range of integrations with the Bazaarvoice
ratings and reviews service. Proper use of this module requires having a Bazaarvoice account and API Keys for the
Bazaarvoice Conversations API.

Usage
=====
- Allows the following bazaarvoice view modes:
  - Rating summary
  - Reviews
  - Review Highlights
  - Questions & Answers
  - Inline ratings
  - Seller Ratings
- Integration with DCC data attributes (https://knowledge.bazaarvoice.com/wp-content/conversations/en_US/Collect/DCC.html#dcc-data-attributes-1)

Installation
============
1. Install as you would normally install a contributed Drupal module. Visit https://www.drupal.org/node/1897420 for further information.
2. Add a **Bazaarvoice id** field in entity type your would like to use.
3. Make sure this new field is visible in your display and configure the formatter as you wish.
4. You can use the **Bazaarvoice summary** field if you need to display the ratings summary and reviews in same display.


Upgrading
=========
If you are using the old field *field_bv_product_id* and you want to continue using it, you just have to make sure that the field is not hidden neither in the view mode nor in the template and configure the formatter as you wish.
