/**
 * @file
 * Handles AJAX login and register events.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  let onLnLogin = function (res) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event' : 'login',
      'event_name' : 'login',
      'method' : res.loginMode,
      'social_network' : res.social_network,
      'login_status' : res.login_status,
      'user_account_type' : res.user_account_type,
      'user_type' : res.user_type,
      'user_id' : res.UID,
      'form_id' : res.form_id,
      'form_name' : res.form,
      'form_type' : res.source,
      'module_name' : drupalSettings.ln_ciam.data.module_name,
      'module_version' : drupalSettings.ln_ciam.data.module_version,
    });
  };
  let onLnAfterValidation = function (res) {
    if(res.formErrors){
      window.dataLayer = window.dataLayer || [];
      for(let error_code in res.formErrors){
        let error = res.formErrors[error_code];
        window.dataLayer.push({
          'event' : 'gigya_form_error',
          'event_name' : 'gigya_form_error',
          'form_id' : res.form_id,
          'form_name' : res.form,
          'form_type' : res.source,
          'module_name' : drupalSettings.ln_ciam.data.module_name,
          'module_version' : drupalSettings.ln_ciam.data.module_version,
          'error_code' : error.errorCode,
          'error_name' : error.errorMessage
        });
      }
    }
  };

  let customScreensets = function () {
    if (drupalSettings.ln_ciam && drupalSettings.ln_ciam.customScreenSets) {
      for(let selector in drupalSettings.ln_ciam.customScreenSets){
        let screen = drupalSettings.ln_ciam.customScreenSets[selector];
        if($(selector).length){
          screen.onAfterValidation = onLnAfterValidation;
          if(screen.display_type && screen.display_type == 'popup'){
            $(selector).once('ln-ciam-raas').click(function (e) {
              e.preventDefault();
              gigya.accounts.showScreenSet(screen);
            });
          } else {
            if($(selector).attr('id')){
              screen['containerID'] = $(selector).attr('id');
            }

            gigya.accounts.showScreenSet(screen);
          }
        }
      }
    }
  };

  Drupal.behaviors.ln_ciam_gigya_api = {
    attach: function (context, settings) {
      if (drupalSettings.gigya.enableRaaS && drupalSettings.gigya.isRaasInit) {
        if (!('isLnCiamRaasInit' in drupalSettings.gigya)) {
          let gigyaFunctions = window.onGigyaServiceReady !== undefined ? window.onGigyaServiceReady : (() => void 0);
          window.onGigyaServiceReady = function (serviceName) {
            gigyaFunctions();
            customScreensets();
          };
          gigyaHelper.addGigyaFunctionCall('accounts.addEventHandlers', {
            onLogin: onLnLogin
          });
          drupalSettings.gigya.isLnCiamRaasInit = true;
        } else {
          customScreensets();
        }
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
