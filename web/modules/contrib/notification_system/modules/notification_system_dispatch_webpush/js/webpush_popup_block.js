(function (Drupal, $) {
  Drupal.behaviors.notificationSystemDispatchWebpushPopupBlock = {
    attach: function (settings, context) {
      $('[data-notification-system-dispatch-webpushpopup]').once('initializieWebpushPopup').each(function () {
        let $block = $(this);

        let $buttonEnable = $block.find('[data-webpush-button-enable]');
        let $buttonAskLater = $block.find('[data-webpush-button-ask_later]');
        let $buttonCancel = $block.find('[data-webpush-button-cancel]');


        // Event Listeners for buttons.
        $buttonEnable.click(e => {
          e.preventDefault();

          Drupal.notificationSystemDispatchWebpush.subscribe();
        });

        $buttonAskLater.click(e => {
          localStorage.setItem('notification_system_dispatch_webpush__popup_laterClicked', new Date().getTime().toString());
          $block.addClass('hidden');
        });

        $buttonCancel.click(e => {
          localStorage.setItem('notification_system_dispatch_webpush__popup_dontAskAgain', 'true');
          $block.addClass('hidden');
        });


        // Set block visibility on state change.
        Drupal.notificationSystemDispatchWebpush.on('stateChange', newState => {
          switch (newState) {
            case 'incompatible':
              $block.addClass('hidden');
              break;

            case 'disabled':
              showBlockIfConditionsMatch();
              break;

            case 'enabled':
              $block.addClass('hidden');
              break;

            case 'computing':
              $buttonEnable.attr('disabled', true);
              $buttonEnable.text(Drupal.t('Loading...'))
              break;
          }
        });


        // We have to call this function to register service worker and other things.
        Drupal.notificationSystemDispatchWebpush.initialize()
          .then(result => {
            // Web push is supported, service worker was registered.
            showBlockIfConditionsMatch();
          })
          .catch(reason => {
            // Web push is not supported.
            console.warn('Web Push not available: ' + reason);
            showBlockIfConditionsMatch();
          });


        /**
         * Checks for multiple conditions, and if they match, it shows the block.
         */
        const showBlockIfConditionsMatch = () => {
          let askLaterDays = parseInt($block.data('webpush-ask-later-days'));

          // Holds the timestamp when the "Show later" button was clicked.
          let laterClicked = parseInt(localStorage.getItem('notification_system_dispatch_webpush__popup_laterClicked'));

          // Holds a boolean is the "Don't ask again" button was clicked.
          let dontAskAgain = localStorage.getItem('notification_system_dispatch_webpush__popup_dontAskAgain') === 'true';

          if (dontAskAgain) {
            // User previously has clicked "Don't ask again"
            return;
          }

          if (!isNaN(laterClicked) && new Date().getTime() < laterClicked + askLaterDays * 24 * 60 * 60 * 1000) {
            // User has clicked "Show later" but not enough time has passed
            return;
          }

          if (Drupal.notificationSystemDispatchWebpush.state === 'incompatible') {
            // User has blocked the notification permission
            return;
          }

          if (Drupal.notificationSystemDispatchWebpush.state === 'enabled') {
            // Push is already enabled in this browser.
            return;
          }

          if (Drupal.notificationSystemDispatchWebpush.state === null) {
            // We don't know the state yet.
            return;
          }

          $block.removeClass('hidden');
        }
      });
    }
  }
})(Drupal, jQuery);
