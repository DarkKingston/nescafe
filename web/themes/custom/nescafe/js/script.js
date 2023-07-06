
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

        $(".btn_register").append("<a class='use-ajax webform-dialog webform-dialog-narrow btn_reset_pass' data-dialog-type='modal'  href='/user/password' >Reset password</a>");

      let phone;

      let currLang = document.querySelector('.curr_lang_item').innerText;

      if(currLang == "RO"){
        phone = "Numar de telefon";
      }else if (currLang == "RU"){
        phone = "Номер телефона";
      }

      document.querySelector('.js-form-item-name').document.querySelector('label').innerHTML = phone;

    }
  };
})(jQuery);
