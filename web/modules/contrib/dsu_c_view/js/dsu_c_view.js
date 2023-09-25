(function ($, Drupal) {
  'use strict';

  /**
   * Initialize viewbuildercomponent functionality.
   */
  Drupal.behaviors.viewbuildercomponent = {
    attach: function (context, settings) {
      $('#viewbuilder-article-type').hide();
      $('#viewbuilder-content-type select').on('change', function () {
        if (this.value === 'article') {
          $('#viewbuilder-article-type').show();
        }
        else {
          $('#viewbuilder-article-type').hide();
        }
      });
    }
  };

})(jQuery, Drupal);
