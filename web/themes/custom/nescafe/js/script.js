
(function ($) {
  Drupal.behaviors.navbarBehavior = {
    attach: function (context, settings) {
      $('.burger,.mob_menu_overlay,.close_menu',context).on('click',function(){
        $('.mobile_menu').slideToggle();
        $('.mob_menu_overlay').fadeToggle();
      });

      $('.current_lang',context).on('click',function(){
        $('.langs').slideToggle();
      });

      if($('.cookie_success') == true){
        $('#block-webform-5').hide();
      }
    }
  };
})(jQuery);
