/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.ln_c_newsletter = {
    attach: function (context, settings) {

      //Datalayer GA4 event - newsletter_submit cta
      if ($(".paragraph--type--c-newsletter-signup-cta").length > 0) {
        // Call on submit newsletter CTA.
        $('.paragraph--type--c-newsletter-signup-cta a').click(function () {
          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({
            'event' : 'newsletter_submit',
            'event_name' : 'newsletter_submit',
            'module_name' : drupalSettings.ln_c_newsletter.data.module_name,
            'module_version' : drupalSettings.ln_c_newsletter.data.module_version,
          });
          return false;
        });
      }
      //Datalayer GA4 event - newsletter_submit component
      if ($(".webform-submission-newsletter-email-collection-form").length > 0) {
        // Call on submit newsletter webform.
        $('.webform-submission-newsletter-email-collection-form button').click(function () {
          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({
            'event' : 'newsletter_submit',
            'event_name' : 'newsletter_submit',
            'form_id' : drupalSettings.ln_c_newsletter.data.form_id,
            'form_type' : drupalSettings.ln_c_newsletter.data.form_type,
            'module_name' : drupalSettings.ln_c_newsletter.data.module_name,
            'module_version' : drupalSettings.ln_c_newsletter.data.module_version,
          });
          return false;
        });
      }
      if ($('.ln-c-newsletter-error').length > 0) {
        //Datalayer GA4 event - newsletter_error
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          'event' : 'newsletter_error',
          'event_name' : 'newsletter_error',
          'form_id' : drupalSettings.ln_c_newsletter.data.form_id,
          'form_type' : drupalSettings.ln_c_newsletter.data.form_type,
          'module_name' : drupalSettings.ln_c_newsletter.data.module_name,
          'module_version' : drupalSettings.ln_c_newsletter.data.module_version,
        });
      }


    }
  };

})(jQuery, Drupal, drupalSettings);
