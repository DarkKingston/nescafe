
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

      $('input').attr('#data-drupal-selector').val(8);
    }
  };
})(jQuery);

window.addEventListener("DOMContentLoaded", (event) => {
  let cookieBox = document.getElementById('block-webform-5');
  let cookieSuccess = document.querySelector('.cookie_success');
  if (cookieSuccess) {
    cookieBox.classList.add('cookie_hidden');
  }
});
