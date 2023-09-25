/**
 * @file
 * The oembed video colorbox integration.
 */

(function($) {
  Drupal.behaviors.video_oembed_media_colorbox = {
    attach: function (context, settings) {
      $('.video-oembed-launch-modal', context).once().click(function(e) {
        // Allow the thumbnail that launches the modal to link to other places
        // such as video URL, so if the modal is sidestepped things degrade
        // gracefully.
        e.preventDefault();
        $.colorbox($.extend(settings.colorbox, {'html': $(this).data('video-oembed-media-modal')}));
      });
    }
  };
})(jQuery);
