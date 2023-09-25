/**
 * @file
 */

(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.dsuGallery = {
    attach: function (context, settings) {
      var wrap_id, iframe_html, parent_id;
      $('.dsu-gallery').each(function () {
        wrap_id = $(this).attr('id');
        iframe_html = $(this).find('.dsu-gallery-item.active').html();
        $('#' + wrap_id + ' .dsu-gallery-full-preview').html(iframe_html);
      });

      $('.dsu-gallery-content--preview').once().on('click', function () {
        parent_id = $(this).parents('.dsu-gallery').attr('id');
        var attr = $(this).attr('target-gallery');
        if (typeof attr !== typeof undefined && attr !== false) {
          var galleryId = attr.split('-')[5];
          $('#' + parent_id + ' .dsu-gallery-full-preview')
          .html($('#' + parent_id + ' #dsu-gallery-item-' + galleryId).html());
        }
      });
    }
  };

})(jQuery, Drupal);
