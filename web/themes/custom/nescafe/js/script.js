
(function ($) {
  Drupal.behaviors.navbarBehavior = {
    attach: function (context, settings) {
      $('.burger,.mob_menu_overlay').on('click',function(){
        $('.mobile_menu').once().slideToggle();
        $('.mob_menu_overlay').once().fadeToggle();
      });

      $('.current_lang').on('click',function(){
        $('.langs').once().slideToggle();
      })
    }
  };
})(jQuery);
