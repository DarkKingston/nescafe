(function ($, Drupal, drupalSettings) {

  'use strict';

  let customScreensets = function () {
    $('.ln-ciamlite-gigya-block').once('ln-ciamlite-gigya-init').each(function(){
      let screen = $(this).data('screen-id');
      if (screen && typeof drupalSettings.gigya.raas[screen] !== 'undefined') {
        let id = $(this).attr('id');
        drupalSettings.gigya.raas[screen].containerID = id;
        drupalSettings.gigya.raas.linkId = id;
        drupalSettings.gigya.raas[screen].customLang = {
          account_is_disabled: Drupal.t('Invalid login or password'),
        };
        gigya.accounts.showScreenSet(drupalSettings.gigya.raas[screen]);
      }
    });
    drupalSettings.gigya.isCustomRaasInit = true;
  };

  Drupal.behaviors.ogilvy_gigya = {
    attach: function (context, settings) {
      if (drupalSettings.gigya.enableRaaS && !('isCustomRaasInit' in drupalSettings.gigya)) {
        let gigyaFunctions = window.onGigyaServiceReady !== undefined ? window.onGigyaServiceReady : (() => void 0);
        window.onGigyaServiceReady = function (serviceName) {
          gigyaFunctions();
          customScreensets();
        };
      }
    }
  };

})(jQuery, Drupal, drupalSettings);

