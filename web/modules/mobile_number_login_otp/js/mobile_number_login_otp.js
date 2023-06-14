(function ($, Drupal, once, drupalSettings) {
  Drupal.behaviors.mobileNumberLoginOtp = {
    attach: function (context, settings) {
      once('mobileNumberLoginOtp', 'input.otp-input', context).forEach(element => {
        $('input[name="mobileradio"]').change(function() {
          if ($('input[name="mobileradio"]:checked').val() == 'mobile') {
            $('.otp-wrapper').removeClass('hidden');
            $('.pass-wrapper').addClass('hidden');
          }
          else {
            $('.otp-wrapper').addClass('hidden');
            $('.pass-wrapper').removeClass('hidden');
          }
        });
        $('.mobile-number-login').blur(function(){
          $('[name="extension"]').val($('.iti__selected-dial-code').text());
        });
        $('.otp-send a').click(function(evt) {
          if ($('.otp-counter').text().length > 0) {
            evt.preventDefault();
          }
          if ($('.mobile-number-login').val() != '') {
            $('.mobilenumber-error').text("");
            $('.mobile-number-login').removeClass('error');
            $.ajax({
              'url' : Drupal.url('send-sms'),
              'type': 'POST', 
              'data': {'mobile': $('.mobile-number-login').val(), 'extension': $('.iti__selected-dial-code').text()},
              success: function(result) {
                var messages = new Drupal.Message();
                if (result.status == 1) {
                  var messageId = messages.add(Drupal.t('Otp send successfully'));
                  $('.otp-send a').text(Drupal.t('Resend OTP'));
                  $('.otp-send a').addClass('hidden');
                }
                else if (result.status == 2) {
                  var messageId = messages.add(Drupal.t('Otp Resend successfully'));
                  $('.otp-send a').addClass('hidden');
                }
                else if (result.status == 4) {
                  var messageId = messages.add(Drupal.t('Your account is been blocked, please check the support.'));
                  $('.otp-send a').addClass('hidden');
                }
                else {
                  var messageId = messages.add(Drupal.t('Max count has been done please login again 5 minutes.'));
                  $('.otp-send a').addClass('hidden');
                }
                var count = 30;
                const id = setInterval(function() {
                  if (count == 0) {
                    clearInterval(id);
                    $('.otp-counter').text("");
                    $('.otp-send a').removeClass('hidden');
                  }
                  else {
                    $('.otp-counter').text('Resend OTP after ' + count + ' seconds');
                    count--;
                  }
                  if (count == (30 - 10)) {
                    messages.remove(messageId);
                  }
                }, 1000);
              }
            });
          }
          else {
            $('.mobilenumber-error').text(Drupal.t('Please enter the mobile number'));
            $('.mobile-number-login').addClass('error');
          }
        });
      });
    }
  }
})(jQuery, Drupal, once, drupalSettings);