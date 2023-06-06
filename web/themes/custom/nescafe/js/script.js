
(function ($) {
  Drupal.behaviors.navbarBehavior = {
    attach: function (context, settings) {
      function toggleMobMenu(){
        $('.mobile_menu').slideToggle();
      }

      function toggleLangs() {
        $('.current_lang').toggleClass('active');
      }
    }
  };

})(jQuery);
