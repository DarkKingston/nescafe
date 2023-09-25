(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.dsuEngageOptions = {
    attach: function (context, settings) {
      // Get list of prefix and change phone_prefix for selected country based.
      if (typeof drupalSettings.phone_prefix !== 'undefined') {
        var country = $('#edit-country').val();
        if (typeof country !== 'undefined' && country !== '0') {
          var phone = drupalSettings.phone_prefix[country];
		  $("#edit-phone-prefix").val(country);
          //$("#edit-phone-prefix").val(phone);
        }
      }

      $('.questionbutton1', context).click(function () {
        $(this, context).addClass('active');
        $('.questionbutton2', context).removeClass('active');
        $('.questionbutton3', context).removeClass('active');
        $("label[for=edit-request-type-question]").click();

        var contactus_type = $(this, context).data("eventlabel");
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "contact_form_type_change",
          eventCategory: "Contact Us",
          eventAction: "Contact Us Type Changed",
          eventLabel: contactus_type
        });
        window.dataLayer.push({
          'event' : 'contact_interaction',
          'event_name' : 'contactus_type_changed',
          'topic' : contactus_type,
          'module_name' : drupalSettings.dsu_engage.data.module_name,
          'module_version' : drupalSettings.dsu_engage.data.module_version,
        });

      });
      $('.questionbutton2', context).click(function () {
        $(this).addClass('active');
        $('.questionbutton1').removeClass('active');
        $('.questionbutton3').removeClass('active');
        $("label[for=edit-request-type-complaint]").click();

        var contactus_type = $(this, context).data("eventlabel");
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "contact_form_type_change",
          eventCategory: "Contact Us",
          eventAction: "Contact Us Type Changed",
          eventLabel: contactus_type
        });
        window.dataLayer.push({
          'event' : 'contact_interaction',
          'event_name' : 'contactus_type_changed',
          'topic' : contactus_type,
          'module_name' : drupalSettings.dsu_engage.data.module_name,
          'module_version' : drupalSettings.dsu_engage.data.module_version,
        });

      });
      $('.questionbutton3', context).click(function () {
        $(this).addClass('active');
        $('.questionbutton1').removeClass('active');
        $('.questionbutton2').removeClass('active');
        $("label[for=edit-request-type-praise]").click();

        var contactus_type = $(this, context).data("eventlabel");
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "contact_form_type_change",
          eventCategory: "Contact Us",
          eventAction: "Contact Us Type Changed",
          eventLabel: contactus_type
        });
        window.dataLayer.push({
          'event' : 'contact_interaction',
          'event_name' : 'contactus_type_changed',
          'topic' : contactus_type,
          'module_name' : drupalSettings.dsu_engage.data.module_name,
          'module_version' : drupalSettings.dsu_engage.data.module_version,
        });

      });
    }
  }


  try {

    var formCheck = function () {
      $('[data-drupal-states]').each(function () {
        var elem = this;
        if (typeof $(this).data('drupal-states').required === 'object') {

          $.each($(this).data('drupal-states').required, function (key, val) {
            let valueToCheck = [];
            let elmLabel = $('label[for="' + $(elem).attr('id') + '"]');

            $.each(val, function (index, data) { valueToCheck.push(data.value || data); });

            let selectedValue = false;

            switch ($(key).attr('type')) { case 'radio': selectedValue = $(key + ':checked').val(); break; default: selectedValue = $(key).val(); break; }

            var $input = elmLabel.closest('.form-group').find('[placeholder]');

            $(elem).closest('form').attr('novalidate', 'novalidate');

            if (selectedValue && valueToCheck.indexOf(selectedValue) !== -1) {
              elmLabel.text(elmLabel.text().replace(/^\s+|\s+\*|\s+$/g, '') + '');
              $input.attr('placeholder') && $input.attr('placeholder', $input.attr('placeholder').replace(/^\s+|\s+\*|\s+$/g, '') + ' *');
            } else {
              elmLabel.text(elmLabel.text().replace(/^\s+|\s+\*|\s+$/g, ''));
              $input.attr('placeholder') && $input.attr('placeholder', $input.attr('placeholder').replace(/^\s+|\s+\*|\s+$/g, ''));
              if($input.attr('required') == undefined || !$input.attr('required')){
                $input.removeClass('error is-invalid');
              };
            }
          });
        }
      });
    };

    $('body').on('change', '[name="request_type"]', function () {
      formCheck();
    });
    $(document).ready(function () {
      formCheck();
    });
  } catch (err) { window.console.log(err); }

  // bind tooltip

  try {
    $("[data-tool-tip]").each(function () {
        var $outerHTML = $('.' + $(this).data('tool-tip')).prop('outerHTML');
        if($($outerHTML).find('.tooltip-icon-block').length){
          $(this).closest('.js-form-item').addClass('has-feedback')
          .prepend('<a href="" class="glyphicon glyphicon-question-sign form-control-feedback"></a>');
          $(this).attr('data-toggle','tooltip').attr('data-placement', "right").attr('title', $('.' + $(this).data('tool-tip')).prop('outerHTML'));
        }else{
          $(this).attr('data-toggle','tooltip').attr('data-placement', "right").attr('title', '');
        }
      //$(this).attr('data-toggle','tooltip').attr('data-placement', "right").attr('title', $('.' + $(this).data('tool-tip')).prop('outerHTML'));
    });
    $('[data-tool-tip]').tooltip({ html: true });
  } catch (err) { }

  try {
    jQuery('.questionbuttons').removeClass('active').eq(jQuery('[name=request_type]:checked').closest('.js-form-item').index()).addClass('active');
  } catch(err){ }

  // Event tracking on engage form submission.
  if ($('#engage-success').length > 0) {
    var contactus_type = $("#engage-success").data("eventlabel");
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: "contact_form_submission",
      eventCategory: "Contact Us",
      eventAction: "Contact Us Submitted",
      eventLabel: contactus_type
    });
  }

  // Event tracking on engage form Submission Error.
  if ($('.engage-error').length > 0) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: "contact_form_submission_error ",
      eventCategory: "Contact Us",
      eventAction: "Form Error",
      eventLabel: 'require field missing'
    });
  }

})(jQuery, Drupal, drupalSettings);
