(function ($, Drupal, drupalSettings) {
  'use strict';

  // Accessibility for video
  Drupal.behaviors.video_player = {
    attach: function (context) {
      if ($('.video-embed-field-lazy-play')) {
        var _playButtons = $('.video-embed-field-lazy-play');
        $(_playButtons).once().each(function () {
          var _imgAlt = $(this).prev().attr("alt") + Drupal.t(' play icon');
          $(this).attr('aria-label', _imgAlt);
        });
      }

      // Remove class for inline play video.
      $('.video-embed-field-lazy-play').on('click', function () {
        $('.video-embed-field-lazy-play').removeClass('video-popup');
        $(this).addClass('video-popup');
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          'event' : 'video_event',
          'event_name' : 'video_start',
          'video_current_time' : '0',
          'video_percent' : '0%',
          'module_name' : drupalSettings.dsu_c_externalvideo.data.module_name,
          'module_version' : drupalSettings.dsu_c_externalvideo.data.module_version,
        });
      });
    }
  };

  // Check video is click for lightbox.
  var player = null;
  $('.field--type-video-embed-field').once().on('click', function () {
    var displayOverlay = $('#cboxOverlay').css('display') || 0;
    if (displayOverlay != 'none' && displayOverlay != 0) {
      $("#cboxLoadedContent").find("iframe").attr("id", "youtube-video");

      $('body').addClass('open-video-modal');

      $('#cboxWrapper, #cboxOverlay, #cboxClose').on('click', function() {
        $('body').removeClass('open-video-modal');
        setFocusOnPlayicon();
      });
    }
  });

  $(document).keyup(function(e) {
    if (e.key === "Escape") {
        $('body').removeClass('open-video-modal');
        setFocusOnPlayicon();
    }
  });



  // Focus on play icon.
  function setFocusOnPlayicon() {
    if ($('#cboxOverlay').once().not(':visible')) {
      setTimeout(function () {
        $('body').removeClass('open-video-modal');
        $('.video-popup').focus();
      }, 500);
    }
  }
})(jQuery, Drupal, drupalSettings);
