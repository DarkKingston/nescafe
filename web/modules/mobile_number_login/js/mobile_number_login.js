(function ($, Drupal, once, drupalSettings) {
  Drupal.behaviors.mobileNumberLogin = {
    attach: function (context, settings) {
      once('mobileNumberLogin', 'input.mobile-number-login', context).forEach(function (element) {
        var code = '';
        if (drupalSettings.default_countries_mobile_login != '') {
          code = $.trim(drupalSettings.default_countries_mobile_login.replaceAll(' ,', ',').replaceAll(', ', ',')).split(',');
        }
        else {
          code = [geoplugin_countryCode().toLowerCase()];
        }
        window.intlTelInput(document.querySelector(".mobile-number-login"), {
          preferredCountries: code,
          separateDialCode: true,
          utilsScript:
            "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
        $('.user-login-form .form-submit').click(function(e) {
          e.preventDefault();
          if ($('.mobile-number-login').val() != '') {
            $('.mobile-number-login').val($('.iti__selected-dial-code').text() + '-' + $('.mobile-number-login').val());
          }
          $('.user-login-form').submit();
        });
        setTimeout(function() {
          $('.user-register-form .mobile-number-login').blur();
        }, 100);
        $('.mobile-number-login').keyup(function() {
          this.value = this.value.replace(/[^0-9]/g, '');
        }).blur(function() {
          if (this.value != '') {
            if (this.value.indexOf('-') > 0) {
              this.value = this.value.split('-')[1];
            }
            $('.user-register-form .mobile-number-login').val($('.iti__selected-dial-code').text() + '-' + this.value);
            $('.user-login-form [name="name"]').val($('.iti__selected-dial-code').text() + '-' + this.value);
          }
        }).focus(function() {
          if (this.value.indexOf('-') > 0) {
            this.value = this.value.split('-')[1];
          }
          $(this).val(this.value);
        });
        $('.user-login-form input[name="mobileradio"]').change(function() {
          $('.mobile-number-login, [name="name"]').val('');
          if ($('input[name="mobileradio"]:checked').val() == 'mobile') {
            $('.name-wrapper').addClass('hidden');
            $('.mobile-wrapper').removeClass('hidden');
          }
          else {
            $('.name-wrapper').removeClass('hidden');
            $('.mobile-wrapper').addClass('hidden');
          }
        });
      });
    }
  };
})(jQuery, Drupal, once, drupalSettings);