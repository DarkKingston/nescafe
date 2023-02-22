/**
 * @file
 * The video_embed_field lazy loading videos.
 */

(function($) {
  Drupal.behaviors.video_oembed_media_lazyLoad = {
    attach: function (context, settings) {
      $('.video-oembed-media-lazy', context).once().click(function(e) {
        // Swap the lightweight image for the heavy JavaScript.
        e.preventDefault();
        var $el = $(this);
        $el.html($el.data('video-oembed-media-lazy'));
      });
    }
  };
})(jQuery);
