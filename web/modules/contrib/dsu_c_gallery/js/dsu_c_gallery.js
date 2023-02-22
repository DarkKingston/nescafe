/**
 * @file
 */

(function ($, Drupal) {
    "use strict";

    $('.paragraph--type--c-gallery').addClass('slide-for').wrap('<div class="dsu_gallery_wrapper"></div>');

    $('.paragraph--type--c-gallery.slide-for').each(function () {
        var gallery = $(this);
        // select gallery jquery ref and add a wrapper
        var config = gallery.data();
        if (config.navi !== 'dots') {
            if (config.navi === 'dots_images') {
                gallery.closest('.dsu_gallery_wrapper').addClass("dots_images");
            }
            var thumData = gallery.clone().removeClass('slide-for').addClass('slide-nav');

            thumData.find('li').html(function () {
                var source = $(this).data('thumb-style');

                if (source.length) {
                    source = source.split(',');
                    return (
                        '<picture>' +
                        '<source media="(min-width: 992px)" srcset="' + source[0] + '">' +
                        '<source media="(min-width: 768px)" srcset="' + source[1] + '">' +
                        '<source media="(min-width: 0px)" srcset="' + source[2] + '">' +
                        '<img src="' + source[0] + '" style="width:auto;" alt="' + $(this).data('thumb_alt') + '">' +
                        '</picture>'
                    );
                } else {
                    var img = $('<img>');
                    img.attr('src', $(this).data('thumb'));
                    img.attr('alt', $(this).data('thumb_alt'));
                    return img;
                }
            });

            thumData.find('.caption').remove(); // remove caption from thumbs
            thumData.insertAfter(gallery); // attach thumbdata to dom

            // add slider
            gallery.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                adaptiveHeight: false,
                mobileFirst: true,
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            arrows: false
                        }
                    }
                ],
                //asNavFor: thumData,
                //dots: config.navi !== 'images',
            });


            thumData.on("init", function(event, slick){
                let currentSlide  = thumData.find("li.slick-current.slick-active");
                currentSlide.addClass("slide-selected").click();
            });

            // add thumb navigation as per config
            thumData.slick({
                slidesToShow: 4,
                slidesToScroll: 4,
                infinite: false,
                dots: config.navi == 'dots_images',
                arrows: false,
                accessible: true,
                mobileFirst: true,
                centerModel: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            arrows: true
                        }
                    }
                ]
                //asNavFor: gallery,
                /*focusOnSelect: true,
                centerMode: false,*/
            });

            thumData.on("click", ".slick-track li", function() {
                thumData.find("li").removeClass("slide-selected");
                $(this).addClass("slide-selected");
                let $selected = thumData.find("li.slide-selected");
                if($selected.length){
                  let image_index;
                  if($selected.is('.slick-slide')){
                    image_index = $selected.index();
                  }else{
                    image_index = $selected.parents('.slick-slide').index();
                  }
                  gallery.slick("slickGoTo", image_index);
                }

            });

            thumData.on("keyup", "li:focus", function(e) {
                if (e.keyCode === 13) {
                    $(this).trigger("click");
                }
            });

            gallery.on("afterChange", function(event, slick, currentSlide) {
                let image_index = gallery.find("li.slick-active").index();
                thumData.find("li.slick-slide").removeClass("slide-selected").eq(currentSlide).addClass("slide-selected");
                var active_index = thumData.find(".slide-selected").index();
                thumData.slick("slickGoTo", active_index);
            });
        } else {
            gallery.addClass('slide-for');
            gallery.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                dots: true,
                adaptiveHeight: false,
            });
        }

        gallery.on('beforeChange', function (event, slick, currentSlide, nextSlide) {
            $('.slick-current iframe').attr('src', $('.slick-current iframe').attr('src'));
        })
    });

    if ($(".node-dsu-component-page-edit-form .paragraph--type--c-gallery").length) {
        var editLeftColumnWidth = $(".node-dsu-component-page-edit-form .layout-region-node-main").width();
        $(".paragraph--view-mode--preview").css("width", (editLeftColumnWidth - 73));
    }
    $(window).resize(function () {
        if ($(".node-dsu-component-page-edit-form .paragraph--type--c-gallery").length) {
            var editLeftColumnWidth = $(".node-dsu-component-page-edit-form .layout-region-node-main").width();
            $(".paragraph--view-mode--preview").css("width", (editLeftColumnWidth - 73));
        }
    });

})(jQuery, Drupal);
