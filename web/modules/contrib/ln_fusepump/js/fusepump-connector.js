/**
 * @file
 * TODO: Document.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   *
   * Attaches the JS test behavior to weight div.
   */
  Drupal.behaviors.fusepumpConnector = {
    attach: function () {
      var fusepumpButtons = drupalSettings.ln_fusepump;

      Object.keys(fusepumpButtons).forEach(function (htmlId) {
        var lightbox = new fusepump.lightbox.buynow(fusepumpButtons[htmlId]);
        var callToAction = document.getElementById(htmlId);

        callToAction.addEventListener("click", function () {
          lightbox.show();
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
