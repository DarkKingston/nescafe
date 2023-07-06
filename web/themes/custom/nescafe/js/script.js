
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

      if(!$(".btn_reset_pass")){
        $("#user-login-form").append("<a class='use-ajax webform-dialog webform-dialog-narrow btn_reset_pass' data-dialog-type='modal'  href='/user/password' >Reset password</a>");
      }
      var phone;
      if($('.current_lang').text() == "RO"){
        phone = "Numar de telefon";
      }else if ($('.current_lang').text() == "RU"){
        phone = "Номер телефона";
      }

      $(".js-form-item-name").find("label").text(phone);

    }
  };
})(jQuery);
