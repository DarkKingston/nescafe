(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.srh_change_display = {
    attach : function(context, settings) {
      let $ingredients_full = $('.srh-ingredients-full');
      let $ingredients = $ingredients_full.find('.srh-ingredients');
      let $displays = $ingredients_full.find('.srh-display');
      $displays.once('srh_change_display').click(function(ev){
        ev.preventDefault();
        let $this = $(this);
        let display = $this.data('display');

        $ingredients.removeClass (function (index, className) {
          return (className.match (/(^|\s)display-\S+/g) || []).join(' ');
        }).addClass('display-' + display);

        $displays.removeClass('active');
        $this.addClass('active');
      });

      $displays.once('srh_change_display_initial').filter('.active').click();
    }
  };

})(jQuery, Drupal);
