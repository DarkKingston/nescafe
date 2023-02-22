/**
 * @file
 *   Javascript for the event tracking on social share.
 */

(function ($, Drupal, drupalSettings) {
  $ ('.social-media-sharing.dsu').find ('a').click (function () {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push ({
        event: 'socialShare',
        eventCategory: 'Social Share',
        eventAction: $(this).attr ('title'),
        eventLabel: $(this).closest('li').data ('contenttype'),
        sharePageName:$(this).closest('li').data ('contentname'),
        contentShared: 1,
      });
    window.dataLayer.push({
      'event' : 'share',
      'event_name' : 'share',
      'social_network' : $(this).attr('title'),
      'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'content_type' : drupalSettings.ln_datalayer?.data?.content_type,
      'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'item_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'module_name' : drupalSettings.dsu_c_sharebuttons.data.module_name,
      'module_version' : drupalSettings.dsu_c_sharebuttons.data.module_version,
    });
  });
})(jQuery, Drupal, drupalSettings);
