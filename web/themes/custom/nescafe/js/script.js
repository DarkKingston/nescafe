
(function ($) {
  Drupal.behaviors.navbarBehavior = {
    attach: function (context, settings) {

      function toggleMobileMenu(){
        $('.mobile_menu').slideToggle();
        $('.mob_menu_overlay').fadeToggle();
      };

      function toggleLangs(){
        $('.langs').slideToggle();
      };
    }
  };
})(jQuery);
