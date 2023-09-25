(function ($, Drupal) {

  Drupal.behaviors.RatingsAutoSubmit = {
    attach: function(context) {

      // the change event bubbles so we only need to bind it to the outer form
      $('.auto-submit-click', context)
        .once('ratings-auto-submit')
        .change(function (e) {
          var formButton = $(this).closest('form').find('.form-submit');
          if (formButton) {
            formButton.click();
          };
        });

      $('input[data-drupal-selector="edit-recommend-checkbox"]', context)
        .once('ratings-recommend')
        .change(function (e) {
          if ($(this).prop('checked')) {
            $('input[data-drupal-selector="edit-recommend"]').val(1);
          }
          else {
            $('input[data-drupal-selector="edit-recommend"]').val('All');
          }
          var formButton = $(this).closest('form').find('.form-submit');
          if (formButton) {
            formButton.click();
          };
        });

      $('input[data-drupal-selector="edit-sort-by-useful-checkbox"]', context)
        .once('ratings-useful')
        .change(function (e) {
          if ($(this).prop('checked')) {
            $('input[data-drupal-selector="edit-sort-by"]').val('count');
            $('input[data-drupal-selector="edit-sort-order"]').val('DESC');
          }
          else {
            $('input[data-drupal-selector="edit-sort-by"]').val('created');
            $('input[data-drupal-selector="edit-sort-order"]').val('DESC');
          }
          var formButton = $(this).closest('form').find('.form-submit');
          if (formButton) {
            formButton.click();
          }
        });

    }
  };
}(jQuery, Drupal));
