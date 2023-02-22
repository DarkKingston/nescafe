/**
 * @file
 *   Javascript for the event tracking on Buy Now button from advanced datalayer.
 */
(function ($, Drupal, drupalSettings) {
  // Check Product page is exist on any content type.

  'use strict';

  Drupal.behaviors.ln_datalayer_api_events = {
    attach: function (context, settings) {
      // Call on submit drupal ratings & reviews form.
      $('.paragraph--type--ln-c-buy-now-component button').once('buy-component').click(function () {

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          'event' : 'buynow_onsite_button_click',
          'event_name' : 'buynow_onsite_button_click',
          'item_brand' : '',
          'item_category' : '',
          'item_variant' : '',
          'item_subcategory' : '',
          'item_range' : '',
          'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
          'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
          'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
          'item_name' : drupalSettings.ln_datalayer?.data?.content_name,
          'module_name' : drupalSettings.ln_c_buy_now_component.data.module_name,
          'module_version' : drupalSettings.ln_c_buy_now_component.data.module_version,
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
