/**
 * @file
 *   Javascript for the adding event tracking from advanced datalayer.
 */

(function ($, Drupal, drupalSettings) {
  // Get page title value
  var content_name = $(".content .field--name-title").text().replace(/\n/g,'');
  var rat = $(".input-group-prepend").find('.input-group-text').text().split(" ");

  // Review Detail view event.
  if ($("#views-exposed-form-dsu-ratings-node-view-block-ratings").length > 0) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: "ratingReviewEvent",
      eventCategory: "Ratings & Reviews",
      eventAction: "Detail View",
      eventLabel: content_name,
      reviewContent: content_name
    });
    //Datalayer GA4
    window.dataLayer.push({
      'event' : 'review_main',
      'event_name' : 'review_viewed',
      'review_rating' : 'Give it ' + parseInt(rat[0]) + '/5',
      'review_id' : null,
      'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'module_name' : drupalSettings.dsu_ratings_reviews.data.module_name,
      'module_version' : drupalSettings.dsu_ratings_reviews.data.module_version,
    });
  }

  // Recommend or not recommend event.
  jQuery(".comment-form .form-item-field-dsu-recommend").click(function() {
    var radioValue = $("input[name='field_dsu_recommend']:checked").val();
    if(radioValue == 1){
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "review_recommended",
        eventCategory: "Ratings & Reviews",
        eventAction: "User Recommend - Yes",
        eventLabel: content_name,
        reviewContent: content_name
      });
      //Datalayer GA4
      window.dataLayer.push({
        'event' : 'review_main',
        'event_name' : 'review_recommended',
        'review_rating' : null,
        'review_id' : null,
        'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
        'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
        'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
        'module_name' : drupalSettings.dsu_ratings_reviews.data.module_name,
        'module_version' : drupalSettings.dsu_ratings_reviews.data.module_version,
      });
    } else {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "review_not_recommended",
        eventCategory: "Ratings & Reviews",
        eventAction: "User Recommend - No",
        eventLabel: content_name,
        reviewContent: content_name
      });
      //Datalayer GA4
      window.dataLayer.push({
        'event' : 'review_main',
        'event_name' : 'review_not_recommended',
        'review_rating' : null,
        'review_id' : null,
        'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
        'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
        'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
        'module_name' : drupalSettings.dsu_ratings_reviews.data.module_name,
        'module_version' : drupalSettings.dsu_ratings_reviews.data.module_version,
      });
    }
  });

  // Preview event.
  jQuery(".comment-form #edit-preview").click(function() {
    var numItems = $(".fivestar-widget-5").children('.on').length;

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: "review_preview",
      eventCategory: "Ratings & Reviews",
      eventAction: "Preview Review Submission",
      eventLabel: content_name,
      reviewContent: content_name
    });
    //Datalayer GA4
    window.dataLayer.push({
      'event' : 'review_main',
      'event_name' : 'review_preview',
      'review_rating' : numItems,
      'review_id' : null,
      'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'module_name' : drupalSettings.dsu_ratings_reviews.data.module_name,
      'module_version' : drupalSettings.dsu_ratings_reviews.data.module_version,
    });
  });

  // Media click event.
  jQuery(".comment-form #edit-field-dsu-images-actions-ief-add").on('mousedown', function() {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: "review_attach_media",
      eventCategory: "Ratings & Reviews",
      eventAction: "Attach Media to Review",
      eventLabel: content_name,
      reviewContent: content_name
    });
    //Datalayer GA4
    window.dataLayer.push({
      'event' : 'review_main',
      'event_name' : 'review_attach_media',
      'review_rating' : null,
      'review_id' : null,
      'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'module_name' : drupalSettings.dsu_ratings_reviews.data.module_name,
      'module_version' : drupalSettings.dsu_ratings_reviews.data.module_version,
    });
  });

  // Media cancel event.
  jQuery(document).ajaxComplete(function(event) {
    jQuery(".comment-form .cancel").on('mousedown', function() {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "review_attach_media",
        eventCategory: "Ratings & Reviews",
        eventAction: "Cancel Media Attachment",
        eventLabel: content_name,
        reviewContent: content_name
      });
      //Datalayer GA4
      window.dataLayer.push({
        'event' : 'review_main',
        'event_name' : 'review_attach_media_cancel',
        'review_rating' : null,
        'review_id' : null,
        'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
        'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
        'item_id' : drupalSettings.ln_datalayer?.data?.content_id,
        'module_name' : drupalSettings.dsu_ratings_reviews.data.module_name,
        'module_version' : drupalSettings.dsu_ratings_reviews.data.module_version,
      });
    });
  });

  // Rating click event.
  jQuery(".comment-form select[name='field_dsu_ratings[0][rating]']").change(function() {
    star_rate = jQuery(this).find('option:selected').text();
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: "review_rating_selection",
      eventCategory: "Ratings & Reviews",
      eventAction: "Rating Selection -" + star_rate,
      eventLabel: content_name,
      reviewContent: content_name,
      reviewRating:  star_rate
    });
  });

  $(document).ready(function(){
    var selectBox = '<select data-drupal-selector="edit-field-dsu-ratings-0-rating" class="form-select form-control" id="edit-field-dsu-ratings-0-rating--2" name="field_dsu_ratings[0][rating]" style=" display: none;">';
    selectBox+='<option value="-">' + Drupal.t('Select rating') + '</option>';
    selectBox+='<option value="20">' + Drupal.t('Give it @star/@count', {'@star': '1', '@count': '5'}) + '</option>';
    selectBox+='<option value="40">' + Drupal.t('Give it @star/@count', {'@star': '2', '@count': '5'}) + '</option>';
    selectBox+='<option value="60">' + Drupal.t('Give it @star/@count', {'@star': '3', '@count': '5'}) + '</option>';
    selectBox+='<option value="80">' + Drupal.t('Give it @star/@count', {'@star': '4', '@count': '5'}) + '</option>';
    selectBox+='<option value="100">' + Drupal.t('Give it @star/@count', {'@star': '5', '@count': '5'}) + '</option></select>';
    $('.comment-form .fivestar-form-item .fivestar-').append($(selectBox));

    $('.comment-form .fivestar-form-item .star').hover(function(){
      $(this).addClass("hover").prevAll().addClass("hover");
      }, function(){
      $(this).removeClass("hover").prevAll().removeClass("hover");
    });
    var ratingValue = $(".comment-form .fivestar-form-item input[data-drupal-selector=edit-field-dsu-ratings-0-rating]input[type=hidden]").val();
    $(".comment-form .fivestar-form-item .star span").each(function(index) {
        if( ratingValue < ((index + 1) * 20) ) {
          $(this).removeClass("on").addClass("off");
        }
    });
    $('.comment-form .fivestar-form-item .star').click(function(){
      $(".comment-form .fivestar-form-item .star span").removeClass("on").addClass("off");
      $(this).children("span").removeClass("off").addClass('on');
      $(this).prevAll().children("span").removeClass("off").addClass("on");
      $('#edit-field-dsu-ratings-0-rating--2').val(($(this).prevAll().length+1)*20);
    });

    var recommend = $('fieldset[data-drupal-selector="edit-field-dsu-recommend"]');
    recommend.find('label').on('click', function(event) {
      event.preventDefault();
      var input = $(this).prev();

      if (input.prop('checked')) {
        input.attr('checked', false);
        input.prop('checked', false);
      }
      else {
        recommend.find('input').attr('checked', false);
        recommend.find('input').prop('checked', false);
        input.attr('checked', true);
        input.prop('checked', true);
      }
    });
  });

})(jQuery, Drupal, drupalSettings);
