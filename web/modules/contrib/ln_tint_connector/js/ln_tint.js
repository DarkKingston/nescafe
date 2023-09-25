/**
 * @file
 *   Javascript for the adding event tracking from advanced datalayer.
 */

(function ($, Drupal, settings) {
  'use strict';

  Drupal.behaviors.ln_tint_connector = {
    attach: function attach(context, settings) {
      $('.tint-social').once('ln-tint-events').each(function(){
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event:"ugcEvent",
          eventCategory:"UGC",
          eventAction:"View UGC",
        });
        window.dataLayer.push({
          'event' : 'ugc_content_visibility',
          'event_name' : 'content_visibility',
          'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
          'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
          'module_name' : drupalSettings.ln_tint_connector.data.module_name,
          'module_version' : drupalSettings.ln_tint_connector.data.module_version,
        });
      });
    }
  }

})(jQuery, Drupal, drupalSettings);


