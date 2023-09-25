(function ($, Drupal, settings) {
  'use strict';

  Drupal.behaviors.ln_c_cardgrid = {
    attach: function attach(context, settings) {
      $('.paragraph--type--ln-c-grid-card-item').once('cardgrid-dismiss-focus').on('keydown', function (e) {
        if(e.key === "Escape" || e.key === "Esc"){
          $(this).blur();
        }
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
