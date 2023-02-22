(function ($, Drupal) {
  'use strict';

  var originalActionLinkFlash = null;


  Drupal.behaviors.srh_lets_cook_it = {

    newActionLinkFlash: function (ajax, response, status) {
      if (status === 'success' && response.selector.includes('flag-srh-lets-cook-it') && response.message) {
        var para = document.createElement('P');
        var closeBtn = '<button type="button" class="close" data-dismiss="alert" aria-label="' + Drupal.t('Close') + '"><span aria-hidden="true">&times;</span></button>';
        para.innerHTML = response.message + closeBtn;
        para.classList.add('srh-lets-cook-it-message', 'alert', 'alert-success', 'alert-dismissible', 'fade', 'show', 'text-center');
        var $messageContainer = $('.field--name-field-srh-steps');
        if (!$messageContainer.length) {
          $messageContainer = $(".field--name-body");
        }
        $messageContainer.prepend(para);
      }
      else {
        originalActionLinkFlash(ajax, response, status);
      }
    },

    attach: function (context, settings) {
      $('.flag-srh-lets-cook-it a', context).click(function () {
        var $stepsSection = $('.field--name-field-srh-steps');
        if ($stepsSection.length) {
          var offset = $stepsSection.parent().offset().top;
          $([document.documentElement, document.body]).animate({scrollTop: offset}, 500);
        }
        var $count = $('.field--name-srh-cooked-it-count');
        if ($count.length) {
          var countVal = parseInt($("em", $count).text());
          if (!isNaN(countVal)) {
            $("em", $count).text(countVal + 1);
          }
        }
      });

      if(!originalActionLinkFlash && Drupal.AjaxCommands.prototype.actionLinkFlash) {
        // Override flag message behaviour.
        originalActionLinkFlash = Drupal.AjaxCommands.prototype.actionLinkFlash;
        Drupal.AjaxCommands.prototype.actionLinkFlash = this.newActionLinkFlash;
      }
    }
  };

})(jQuery, Drupal);
