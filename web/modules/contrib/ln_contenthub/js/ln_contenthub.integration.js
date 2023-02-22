/**
 * @file ln_contenthub.results.js
 */
(function ($, Drupal) {

  "use strict";

  /**
   * Registers behaviours related to  widget.
   */
  Drupal.behaviors.ContentHubResults = {
    attach: function (context) {

      if (window.sessionStorage.getItem("lastScrollPosition") !== null) {
        var scrollTo = window.sessionStorage.getItem("lastScrollPosition");
        ($(".ln_contenthub-search-results").closest("html").parent()[0]).defaultView.scrollTo(0, scrollTo);
        window.sessionStorage.removeItem("lastScrollPosition");
      }

      $('.js-form-submit').on("click", function(){

        if ($(this).hasClass("contenthub-load-more")) {
          var result_container = $(this).parent();
          
          if (result_container.find(".ajax-progress-fullscreen").length) {
            result_container.find(".ajax-progress-fullscreen").remove();
          }
          result_container.append('<div class="ajax-progress-fullscreen"></div>');

          window.sessionStorage.removeItem("lastScrollPosition");
          window.sessionStorage.setItem("lastScrollPosition", $(this).offset().top);
        }  else {
          var parent_fieldset = $(this).closest("fieldset");
          var search_field_value = parent_fieldset.find("[type='search']").val();
  
          if (search_field_value) {
            if (parent_fieldset.find(".ajax-progress-fullscreen").length) {
              parent_fieldset.find(".ajax-progress-fullscreen").remove();
              parent_fieldset.find(".ajax-progress-background-mask").remove();
            }
            parent_fieldset.append('<div class="ajax-progress-fullscreen"></div><div class="ajax-progress-background-mask"></div>');
          }
        }
      });

      $('.ln_contenthub-grid-item').once('bind-click-event').click(function () {
          $('.form-type-checkbox').each(function () {
            $('.ln_contenthub-grid-item').removeClass('checked');
            $('.form-type-checkbox input').prop('checked', false);
        });

        var input = $(this).find('.form-type-checkbox input');
        input.prop('checked', !input.prop('checked'));
        if (input.prop('checked')) {
          $(this).addClass('checked');
        }
        else {
          $(this).removeClass('checked');
        }
      });
    }
  };

}(jQuery, Drupal));
