
(function ($) {
  Drupal.behaviors.navbarBehavior = {
    attach: function (context, settings) {

      $('.user-register-form .form-item-name input').mask('37399999999');
      $('.reset_pass').mask('37399999999');



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
      $('.closePopupTombola',context).on('click',function(){
        $('.popup_final').hide();
      });

      let phone;
      let login;

      let currLang = document.querySelector('.curr_lang_item').innerText;

      if(currLang == "RO"){
        phone = "Numar de telefon";
        login = "Logare pe sait";
      }else if (currLang == "RU"){
        phone = "Номер телефона";
        login = "Вход на сайт";
      }
      document.querySelector("#block-vkhodnasayt h2").innerHTML = login;

      document.querySelector('.user-login-form .js-form-item-name label').innerHTML = phone;

      document.querySelector('.user-register-form .js-form-item-name label').innerHTML = phone;

      // document.querySelector('.form-item-field-reglament-value input').disabled = true;


      if(document.querySelector(".reg_accept")){
        document.querySelector('.reglament_accept').style.display = 'none';
      }
    }
  };
})(jQuery);

function closePopup() {
  document.querySelectorAll('.error_popup').classList.add('hidden');
}
