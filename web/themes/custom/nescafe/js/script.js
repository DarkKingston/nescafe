
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

      let phone;

      let currLang = document.querySelector('.curr_lang_item').innerText;

      if(currLang == "RO"){
        phone = "Numar de telefon";
      }else if (currLang == "RU"){
        phone = "Номер телефона";
      }
      document.querySelector('.user-login-form .js-form-item-name label').innerHTML = phone;

      document.querySelector('.user-register-form .js-form-item-name label').innerHTML = phone;


    }
  };
})(jQuery);
