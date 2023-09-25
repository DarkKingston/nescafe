/**
 * @file
 * Hook slick slider slick/js/slick.load.js
 */
(function (jQuery, Drupal, drupalSettings) {

    'use strict';
   
    Drupal.behaviors.slickConfig = {
        attach: function (context) {
            window.onresize = function(event) {
                if(jQuery(".node-dsu-component-page-edit-form .paragraph--type--c-entitycycle").length) {
                    var editLeftColumnWidth = jQuery(".node-dsu-component-page-edit-form .layout-region-node-main").width();
                    jQuery(".paragraph--view-mode--preview").css("width", (editLeftColumnWidth-72));
                }

                var title_height = 0;
                var image_height = 0;
                jQuery(".paragraph--type--c-entitycycle").find(".slick__slider.slick-initialized").find("article .field--name-title").height("");
                jQuery(".paragraph--type--c-entitycycle").find(".slick__slider.slick-initialized").find("article .field--type-image").height("");
                jQuery(".slick__slider").find(".slick-slide").each(function() {
                    if (jQuery(this).find("article .field--name-title").height() > title_height) {
                        title_height = jQuery(this).find("article .field--name-title").height();
                    }
                    if (jQuery(this).find("article img").parent().height() > image_height) {
                        image_height = jQuery(this).find("article img").parent().height();
                    }
                });
                jQuery(".paragraph--type--c-entitycycle").find(".slick__slider.slick-initialized").find("article .field--name-title").height(title_height);
                jQuery(".paragraph--type--c-entitycycle").find(".slick__slider.slick-initialized").find("article .field--type-image").height(image_height);
            };

            jQuery(document).ready(function(){
                    var title_height = 0;
                    var image_height = 0;
                    jQuery(".slick__slider").find(".slick-slide").each(function() {
                        if (jQuery(this).find("article .field--name-title").height() > title_height) {
                            title_height = jQuery(this).find("article .field--name-title").height();
                        }
                        if (jQuery(this).find("article img").parent().height() > image_height) {
                            image_height = jQuery(this).find("article img").parent().height();
                        }
                        
                    });

                    console.log(title_height, image_height);

                    jQuery(".paragraph--type--c-entitycycle").find(".slick__slider.slick-initialized").find("article .field--name-title").height(title_height);

                    jQuery(".paragraph--type--c-entitycycle").find(".slick__slider.slick-initialized").find("article .field--type-image").height(image_height);
            });
        }
    };
})(jQuery, Drupal, drupalSettings);