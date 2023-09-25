(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.srh_quantity_ingredientes = {
    attach : function(context, settings) {
      let $data = $('.srh-quantity-ingredients');
      $data.once('srh_quantity_ingredients').on('update-quantity-ingredient',function(ev){
        let $this = $(this);

        let $output = $this.siblings('.srh-quantity-ingredients-output');
        if(!$output.length){
          $output = $('<span/>', {
            'class': 'srh-quantity-ingredients-output',
          }).insertAfter($this);
          $data.hide();
        }

        let quantity = $this.data('quantity');
        let fraction = $this.data('fraction');
        let singular = $this.data('singular');
        let plural = $this.data('plural');

        let use_singular = (quantity == 1 && !fraction);
        let value = fraction ? (quantity + ' ' + fraction) : quantity;
        let label = use_singular ? singular : plural;
        $output.html(value + ' ' + label);
      });

      $data.trigger('update-quantity-ingredient');
    }
  };

})(jQuery, Drupal);
