(function ($, Drupal) {
  Drupal.behaviors.notificationSystemDispatchWebpushUsersettingsBlock = {
    attach: function (settings, context) {
      $('.block-notification-system-dispatch-usersettings').once('attachWebPush').each(function () {
        let $checkbox = $(this).find('input[name="dispatcher_webpush"]');
        let $webpushSettings = $(this).find('.notification-system-dispatch-webpush');
        $webpushSettings.append('<div class="notification-system-dispatch-webpush__info"></div><button class="notification-system-dispatch-webpush__button"></button>')
        let $info = $(this).find('.notification-system-dispatch-webpush__info');
        let $button = $(this).find('.notification-system-dispatch-webpush__button');

        $checkbox.on('change', function () {
          let value = $(this).is(':checked');

          if (value) {
            $webpushSettings.removeClass('hidden');
            Drupal.notificationSystemDispatchWebpush.subscribe();
          }
          else {
            $webpushSettings.addClass('hidden');
          }
        });

        // Handle clicks on the button
        $button.click(function (e) {
          e.preventDefault();

          if ($button.attr('disabled') === true) {
            return;
          }

          if (Drupal.notificationSystemDispatchWebpush.state === 'enabled') {
            Drupal.notificationSystemDispatchWebpush.unsubscribe();
          }

          if (Drupal.notificationSystemDispatchWebpush.state === 'disabled') {
            Drupal.notificationSystemDispatchWebpush.subscribe();
          }
        });


        // Update the button state, whenever the status changes
        Drupal.notificationSystemDispatchWebpush.on('stateChange', function (newState) {

          let updateInfoText = (status) => {
            let statusText;
            if (status) {
              statusText = Drupal.t('enabled', {}, {
                context: 'webpush notification status'
              });

              $info.removeClass('is-status_disabled').addClass('is-status_enabled');
            } else {
              statusText = Drupal.t('disabled', {}, {
                context: 'webpush notification status'
              });

              $info.removeClass('is-status_enabled').addClass('is-status_disabled');
            }

            $info.html(Drupal.t('Push notifications are %status in this browser.', {
              '%status': statusText,
            }));
          }

          switch (newState) {
            case 'incompatible':
              $button.hide();
              $info.text(Drupal.t('Your browser does not support push notifications or you have not given the required permission.'));
              break;

            case 'disabled':
              $button.show();
              $button.attr('disabled', false);
              $button.text(Drupal.t('Turn on'));
              updateInfoText(false);
              break;

            case 'enabled':
              $button.show();
              $button.attr('disabled', false);
              $button.text(Drupal.t('Turn off'));
              updateInfoText(true);

              if (!Drupal.notificationSystemDispatchWebpush.unsubscribeSupported) {
                $button.hide();
              }
              break;

            case 'computing':
              $button.show();
              $button.attr('disabled', true);
              $button.text(Drupal.t('Loading...'))
              break;
          }
        });


        // We have to call this function to register service worker and other
        // things.
        Drupal.notificationSystemDispatchWebpush.initialize()
          .then(result => {
            // Web push is supported, service worker was registered.
          })
          .catch(reason => {
            // Web push is not supported.
            console.warn('Web Push not available: ' + reason);
          });
      });
    }
  }
})(jQuery, Drupal);
