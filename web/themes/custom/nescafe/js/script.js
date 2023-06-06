
(function ($) {
  Drupal.behaviors.navbarBehavior = {
    attach: function (context, settings) {
      $('.burger').on('click',function(){
        $('.mobile_menu').slideToggle();
      });

      $('.current_lang').on('click',function(){
        $('.current_lang').toggleClass('active');
      })
    }
  };
})(jQuery);
