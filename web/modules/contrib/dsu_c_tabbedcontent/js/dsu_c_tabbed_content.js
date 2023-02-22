/**
 * @file
 **/

(function ($, Drupal) {
    "use strict";

    Drupal.behaviors.dsuTabbedContent = {
        attach: function (context, settings) {

            // Local Variables
            var this_parent,
                    item_index,
                    item_title,
                    item_icon,
                    tabList,
                    list;

            // Travel Repeated multiple tabbing block
            _traverseTabContentBlocks();

            // Tabbing changing event  
            $('.dsu-tabs--list a').on('click', function (e) {

                _onClickHandler($(this));

            });

            // Repeated multiple tabbing block header creating by unique ids
            function _traverseTabContentBlocks() {

                $('.dsu-tabbed-content').once().each(function (e) {
                    _createTabListing($(this).attr('id'));
                });

            }

            // Tabbing block header creating by unique ids
            function _createTabListing(root_ref) {
                list = '<ul class="dsu-tabs--list '+root_ref+'">';

                // $("#"+root_ref+" .dsu-tabbed-content--items").once().each(_createTabs(index));

                $("#" + root_ref ).children('.field--name-field-c-tab-item').children(".dsu-tabbed-content--items").once().each(function (index) {
                    tabList = _createTabs($(this));
                });
                list = tabList + "</ul>";console.log(list);

                _appendTab(root_ref,list);

            }

            // Tabbing block header item creating(params: index -> tab item reference | return :list -> list times )
            function _createTabs(index) {

                item_index = index.index();
                item_title = index.children('.paragraph--type--c-tab-item').children('.dsu-tabbed-content--tab-title').text();
                item_icon = index.children('.paragraph--type--c-tab-item').children('.dsu-tabbed-content--icon').children('.field--name-field-c-image').html();

                if (0 == item_index) {
                    list = list + "<li class='dsu-tab--item'><a class='active' href='javascript:void(0)' data-index='" + item_index + "'>" + item_icon + "" + item_title + "</a></li>";
                } else {
                    list = list + "<li class='dsu-tab--item'><a href='javascript:void(0)' data-index='" + item_index + "'>" + item_icon + "" + item_title + "</a></li>";
                }

                index.attr('data-index', item_index);
                return list;
            }

            // Tabbing header append in tabbing block (params : root_ref -> unique tab block id ref)
            function _appendTab(root_ref,list){

                $('#' + root_ref).children('.field--name-field-c-tab-item').once().before(list);
                
            }

            // Tabbing change on click event(params : click_ref -> click tab item ref) 
            function _onClickHandler(click_ref) {
                
                this_parent = click_ref.parents('.dsu-tabs--list').parent('.dsu-tabbed-content').attr('id');
                var activeItemIndex = click_ref.attr('data-index');

                click_ref.parents('.dsu-tabs--list').find('a').removeClass('active');
                click_ref.parents('.dsu-tabs--list').next('.field--name-field-c-tab-item').children('.dsu-tabbed-content--items').hide();
                //$('#' + this_parent + ' .field--name-field-c-tab-item > .dsu-tabbed-content--items').hide();
                click_ref.addClass('active');
                click_ref.parents('.dsu-tabs--list').next('.field--name-field-c-tab-item').children('.dsu-tabbed-content--items[data-index="' + activeItemIndex + '"]').show();
                
                //$('#' + this_parent + ' .field--name-field-c-tab-item .dsu-tabbed-content--items[data-index="' + activeItemIndex + '"]').show();

               // $(window).resize();
                //$(".field--name-field-c-gallery-item").slick('destroy');
                //$(".field--name-field-c-gallery-item").slick(slickoptionvariable);
                //$(".field--name-field-c-gallery-item").slick('resize');
                //$('.field--name-field-c-tab-item .dsu-tabbed-content--items[data-index="' + activeItemIndex + '"]').trigger("dsu:tab:show")
            }
            

            /*$('.field--name-field-c-tab-item .dsu-tabbed-content--items').on("dsu:tab:show", function(e) {
                if($(this).isVisible() == true && $(this).hasClass('gallery')) {}
                //console.log('Tab Shown');
                //console.log($(.field--name-field-c-tab-item .dsu-tabbed-content--items).find('.field--name-field-c-gallery-item'));
                $(".field--name-field-c-gallery-item").slick('resize');
            });*/

        }
    }

    if( $('.paragraph--type--c-tabbed-content ul li.active').data('color') ){
        $('.paragraph--type--c-tabbed-content ul li.active').addClass('tab-bg');
        $('.paragraph--type--c-tabbed-content ul li.active').css('background', $('.paragraph--type--c-tabbed-content ul li.active').data('color')).find('a').css('color', '#fff');
    }
    $('.paragraph--type--c-tabbed-content a').on('click', function (e) {
        $('.paragraph--type--c-tabbed-content ul li').removeClass('tab-bg').css({'background': 'none'}).find('a').css('color', 'inherit');
        if($(this).parent('li').data('color')){
            $(this).css('color', '#fff').parent('li').addClass('tab-bg').css({'background': $(this).parent('li').data('color')});
        }
        else{
            $('.paragraph--type--c-tabbed-content ul li').removeClass('tab-bg').css({'background': 'none'}).find('a').css('color', 'inherit');
        }    
    });

    $('.paragraph--type--c-tabbed-content ul li').hover(
        function() {
            if(!$(this).hasClass('active') && $(this).data('color')){
                console.log('Color: ', $(this).data('color'));
                $(this).addClass('tab-bg').css('background', $(this).data('color')).find('a').css('color', '#fff');;
            }
        }, function() {
            if(!$(this).hasClass('active') && $(this).data('color')){
                $(this).removeClass('tab-bg').css('background', '').find('a').css('color', 'inherit');;
            }
        }
    );

})(jQuery, Drupal);