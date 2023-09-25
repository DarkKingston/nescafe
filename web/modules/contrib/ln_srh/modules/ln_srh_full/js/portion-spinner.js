(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.srh_portion_spinner = {
    attach : function(context, settings) {
      let $portion_spinner = $('.srh-portion-spinner');
      let $asc = $portion_spinner.find('.action-asc');
      let $desc = $portion_spinner.find('.action-desc');
      let $serving = $portion_spinner.find('.srh-spinner-number');
      let initial_serving = parseInt($serving.data('initial-serving'), 10);

      $asc.once('srh_portion_spinner').click(function(ev){
        ev.preventDefault();
        let serving = parseInt($serving.data('serving'));
        serving++;
        $serving.data('serving', serving);
        $desc.removeClass('inactive');
        $serving.trigger('update-serving');
      });

      $desc.once('srh_portion_spinner').click(function(ev){
        ev.preventDefault();
        let $this = $(this);
        if(!$this.is('.inactive')){
          let serving = parseInt($serving.data('serving'), 10);
          serving--;
          if(serving<=1){
            serving=1;
            $this.addClass('inactive');
          }
          $serving.data('serving', serving);
          $serving.trigger('update-serving');
        }
      });

      $serving.once('srh_portion_spinner').on('update-serving', function(){
        let serving = parseInt($serving.data('serving'), 10);
        $serving.html(Drupal.formatPlural(
          serving,
          '@count Portion',
          '@count Portions',
        ));

        $('.srh-quantity-ingredients').each(function(){
          let $this = $(this);
          let quantity = $this.data('quantity');
          let fraction = $this.data('fraction');
          let initial_quantity = $this.data('initial-quantity');
          let initial_fraction = $this.data('initial-fraction');

          if(serving == initial_serving){
            quantity = initial_quantity;
            fraction = initial_fraction;
          }else{
            quantity = initial_quantity;
            if(initial_fraction){
              let fraction_parts = initial_fraction.split('/');
              if(fraction_parts[0] && fraction_parts[1]){
                quantity += parseInt(fraction_parts[0],10)/parseInt(fraction_parts[1],10);
                fraction = 0;
              }
            }
            quantity = Math.round((quantity/initial_serving*serving) * 100) / 100; //Round 2 decimals
          }

          $this.data('quantity', quantity);
          $this.data('fraction', fraction);
          $this.trigger('update-quantity-ingredient');
        });
      });
    }
  };

})(jQuery, Drupal);
