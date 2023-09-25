/**
 * @file
 **/

(function ($, Drupal) {
    "use strict";

    Drupal.behaviors.dsuTabbedContent = {
        attach: function (context, settings) {
            $('a[data-toggle="tab"]').once().on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("href") 
//                $(target + ' ul.slick-dots li:first button').trigger('click');
            });
        }
    }

})(jQuery, Drupal);
