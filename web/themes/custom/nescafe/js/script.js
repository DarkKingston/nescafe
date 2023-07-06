
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

      if(document.querySelector('.cookie_success') != null){
        $('#block-webform-5').hide();
      }

      $('.closePopup',context).on('click',function(){
        $('.popup_gift').hide();
      });

      $("#user-login-form").append("<a class='use-ajax webform-dialog webform-dialog-narrow btn_reset_pass' data-dialog-type='modal'  href='/user/password' >Reset password</a>");

      $(".js-form-item-name").find("label").text('Phone');

    }
  };
})(jQuery);
