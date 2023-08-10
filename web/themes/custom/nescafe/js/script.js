
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
      $('.closeError',context).on('click',function(){
        $('.error_popup').hide();
      });
      $('.closePopupTombola',context).on('click',function(){
        $('.popup_final').hide();
      });

      let phone;
      let login;
      let reset;
      let name;
      let last_name;
      let loginLink;
      let label;
      let email;
      let pass;
      let pass2;
      let confirm;
      let reglament;
      let currLang = document.querySelector('.curr_lang_item').innerText;

      if(currLang == "RO"){
        label = "Creează un cont nou";
        name = "Nume";
        last_name = "Prenume";
        email = "Adresă de e-mail";
        pass = "Parola";
        pass2 = "Confirmare parolă";
        confirm = "Verificare parolă:";
        phone = "Numar de telefon";
        login = "Înregistrare/Autentificare";
        reset = "Resetează parola";
        loginLink = "Înregistrare";
        reglament = "Am luat cunoștință cu regulamentul";
      }else if (currLang == "RU"){
        label = "Создать новый аккаунт";
        name = "Фамилия";
        last_name = "Имя";
        email = "Адрес электронной почты";
        pass = "Пароль";
        pass2 = "Подтверждение пароля";
        confirm = "Проверка пароля:";
        phone = "Номер телефона";
        login = "Регистрация/Вход";
        reset = "Восстановить пароль";
        reglament = "Ознакомился с регламентом";
        loginLink = "Регистрация";
      }
      document.querySelector('.user-login-form .js-form-item-name label').innerHTML = phone;
      document.querySelector('.user-register-form .js-form-item-name label').innerHTML = phone;
      document.querySelector(".ui-dialog .ui-dialog-title").innerHTML = label;
      document.querySelector(".field--name-field-name label").innerHTML = name;
      document.querySelector(".field--name-field-lastname label").innerHTML = email;
      document.querySelector(".form-item-mail label").innerHTML = last_name;
      document.querySelector(".form-item-pass label").innerHTML = pass;
      document.querySelector(".form-item-pass-pass2 label").innerHTML = pass2;
      document.querySelector(".password-confirm-message").innerHTML = confirm;
      document.querySelector("#block-vkhodnasayt h2").innerHTML = login;
      document.querySelector("#login-link").innerHTML = loginLink;
      document.querySelector(".btn_reset_pass").innerHTML = reset;
      document.querySelector(".reglamentAccept").innerHTML = reglament;


      // document.querySelector('.form-item-field-reglament-value input').disabled = true;


      if(document.querySelector(".reg_accept")){
        document.querySelector('.reglament_accept').style.display = 'none';
      }
    }
  };
})(jQuery);
