(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.srh_mymenuiq_loading = {};

  Drupal.Ajax.prototype.setProgressIndicatorSrhmenuiqloading = function () {
    this.progress.element = Drupal.srh_mymenuiq_loading.addLoading();
  };
  Drupal.srh_mymenuiq_loading = {
    'addLoading': function(){
      var $loading = $('.my-menu-iq .srh-mymenuiq-loading');
      if($loading.length){
        $loading.data('counter', $loading.data('counter') + 1);
      }else{
        $loading = $('<div/>',{
          'class': 'srh-mymenuiq-loading',
          'data-counter': 0
        });
        $('.my-menu-iq').append($loading);
      }
      return $loading;
    },
    'removeLoading': function(){
      var $loading = $('.my-menu-iq .srh-mymenuiq-loading');
      if($loading.length){
        if($loading.data('counter')){
          $loading.data('counter', $loading.data('counter') - 1);
        }else{
          $loading.remove();
        }
      }
    }
  }

  Drupal.srh_mymenuiq_helper = {
    _collapseShow: function (element){
      element.removeClass('teaser');
      element.addClass('expanded');
      this._trigger_ga_event('my_menu_iq_expand');
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "MyMenuIQEvent",
        eventCategory: "MyMenuIQ",
        eventAction: "MyMenuIQ Expand",
        eventLabel: drupalSettings.mymenuiq.recipe.name
      });
    },
    _collapse: function (element){
      element.removeClass('expanded');
      element.addClass('teaser');
      Drupal.srh_mymenuiq_helper._panelInfoCollapse(element);
    },
    _panelInfoCollapse: function (element) {
      element.find('.panel-info').hide();
      element.removeClass('full-info');
    },
    _panelInfoToggleCollapse: function (element) {

      // Force display expanded
      if (element.hasClass('teaser')) {
        element.removeClass('teaser');
        element.addClass('expanded');
      }

      if ($(window).width() <= 768) { // mobile
        let vh = $(window).height();
        if (element.hasClass('full-info')) {
          // hide on mobile
          element.find('.panel-info').animate({
            maxHeight: 0,
            marginTop: 0
          }, 500, 'swing', function () {
            $(this).hide();
          });
        } else {
          // show on mobile
          let info_btn_position = element.find('.expanded.info-button').offset();
          let top_of_opened_sceondary_part = info_btn_position.top - 15;
          let secondary_part_height = vh - top_of_opened_sceondary_part;
          element.find('.panel-info')
            .css({
              maxHeight: 0,
              marginTop: 0,
            }).show();
          element.find('.panel-info').animate({
            maxHeight: secondary_part_height,
            marginTop: - element.find('.expanded.menu-sidedishes').outerHeight()
          }, 500, 'swing', function () {
          });
        }
      } else {
        element.find('.panel-info')
          .css('height', element.find('.main-content').height() + 'px')
          .toggle({effect: "slide", direction: "right"}, 100);
      }

      element.toggleClass('full-info');
      // Close any active category.
      element.find('.category.active a').trigger('click');
    },
    _categoriesCollapse: function (element) {
      var $menuIqExpanded = $('.my-menu-iq.expanded');
      $menuIqExpanded.removeClass('full-sidedishes');
      let tabs = element.find('.category');
      element.removeClass('summary');
      tabs.removeClass('active');
      tabs.each(function () {
        let tab = $(this);
        let tabContent = $(tab.find('a').attr('href'));
        tab.find('a').removeClass('active');
        tabContent.removeClass('active show');
      });
    },
    _operationSideDishAjax: function (element,sidedish,operation){
      let $data = {
        sidedish: {
          id: sidedish.data('id'),
          type: sidedish.data('type'),
          category: sidedish.data('category'),
        },
        menuIq: {
          sidedishes: localStorage.getItem('Drupal.srh_menuiq_selected_sidedishes'),
          id: element.data('id'),
        }
      }
      Drupal.ajax({
        url: Drupal.url('mymenuiq/sidedish/' + operation),
        progress: {
          type: 'srhMenuIqLoading'
        },
        submit: {
          data: $data,
        }
      }).execute();
    },
    _accordionPush: function (element,accordion,state){
      let accordion_flag = (state == "Open" ? "Expand" : "Collapse");
      let title = element.find('a[href="#' + accordion.attr('id') +'"]').text();
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "MyMenuIQEvent",
        eventCategory: "MyMenuIQ",
        eventAction: "MyMenuIQ Accordion "+ accordion_flag,
        eventLabel: title + ' | ' + state
      });

      const eventName = state == "Open" ? 'my_menu_iq_accordion_expand' : 'my_menu_iq_accordion_collapse';
      this._trigger_ga_event(eventName, {event: 'mmiq_accordion'});
    },
    _trigger_ga_event: function(eventName, attributes) {
      window.dataLayer = window.dataLayer || [];
      var expandEventData = {
        'event': 'mmiq_recipe',
        'event_name': eventName,
        'recipe_name': drupalSettings.mymenuiq.recipe.name,
        'recipe_course': drupalSettings.mymenuiq.recipe.course,
        'module_name': 'ln_srh_menuiq',
      };
      if (drupalSettings.mymenuiq.recipe.srh_id) {
        expandEventData.recipe_id = drupalSettings.mymenuiq.recipe.srh_id;
      }
      if (drupalSettings.mymenuiq.recipe.total_time) {
        expandEventData.recipe_total_time = drupalSettings.mymenuiq.recipe.total_time;
      }
      if (drupalSettings.mymenuiq.recipe.brand) {
        expandEventData.recipe_brand = drupalSettings.mymenuiq.recipe.brand;
      }
      if (drupalSettings.mymenuiq.recipe.chef) {
        expandEventData.recipe_chef = drupalSettings.mymenuiq.recipe.chef;
      }
      if (drupalSettings.mymenuiq.recipe.serving) {
        expandEventData.recipe_servings = drupalSettings.mymenuiq.recipe.serving;
      }
      if (drupalSettings.mymenuiq.recipe.difficulty) {
        expandEventData.recipe_difficulty = drupalSettings.mymenuiq.recipe.difficulty;
      }
      if (drupalSettings.mymenuiq.version) {
        expandEventData.module_version = drupalSettings.mymenuiq.version;
      }
      if (attributes) {
        for (var attrname in attributes) {
          expandEventData[attrname] = attributes[attrname];
        }
      }
      window.dataLayer.push(expandEventData);
    }
  };

  Drupal.behaviors.srh_mymenuiq = {
    attach: function (context, settings) {
      var $menuIqInit = function (menuIq){
        localStorage.setItem('Drupal.srh_menuiq_selected_sidedishes',JSON.stringify({}));
        Drupal.srh_mymenuiq_commands_helper.MenuIqUpdateMainScore(menuIq);
        Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_recipes_load');
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "MyMenuIQEvent",
          eventCategory: "MyMenuIQ",
          eventAction: "MyMenuIQ Load",
          eventLabel: drupalSettings.mymenuiq.recipe.name
        });
      };
      $('.my-menu-iq').once('my-menu-iq').each(function () {
        var $myMenuIq = $(this);
        var $close = $myMenuIq.find('.close');
        var $infoButton = $myMenuIq.find('.info-button');
        var $infoBack = $myMenuIq.find('.panel-info .back');
        var $sideDisheAction = $myMenuIq.find('.srh-sidedish .srh-sidedish-action');
        var $categories = $myMenuIq.find('.category');
        var $closeCategory = $myMenuIq.find('.close-category');
        $menuIqInit($myMenuIq);
        $myMenuIq.on('click', function (ev) {
          if ($(this).hasClass('teaser')) {
            Drupal.srh_mymenuiq_helper._collapseShow($myMenuIq);
          }
        });
        $(".srh-mymenuiq-toogle", $myMenuIq).on('click', function (ev) {
          ev.preventDefault();
          ev.stopPropagation();
          let targetId = $(this).attr('href') || ("#" + $(this).attr('aria-controls'));
          let $target = $(targetId);
          let action = 'close';
          if ($target.hasClass('show')) {
            $target.removeClass('show');
            $(this).attr('aria-expanded', false);
          } else {
            action = 'open';
            $target.addClass('show');
            $(this).attr('aria-expanded', true);
          }
          if ($(this).hasClass('srh-accordion-toggle')) {
            Drupal.srh_mymenuiq_helper._accordionPush($myMenuIq, $(this), action == 'open' ? 'Open' : 'Close');
          }
        });
        $closeCategory.on('click', function () {
          Drupal.srh_mymenuiq_helper._categoriesCollapse($myMenuIq);
          Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_go_back');
        });
        $('a', $categories).on('click', function (ev) {
          ev.preventDefault();
          ev.stopPropagation();
          let $category = $(this).parent();
          if ($category.hasClass('active')) {
            Drupal.srh_mymenuiq_helper._categoriesCollapse($myMenuIq);
          } else {
            Drupal.srh_mymenuiq_helper._panelInfoCollapse($myMenuIq);
            let activeCat = $category.addClass('active').siblings('.active');
            if (activeCat.length) {
              Drupal.srh_mymenuiq_helper._categoriesCollapse($myMenuIq);
            }
            $($(this).attr('href')).addClass('active show');
            if ($myMenuIq.hasClass('full-info')) {
              $myMenuIq.removeClass('full-info');
            }
            $myMenuIq.addClass("full-sidedishes summary");
            $category.addClass('active').siblings().removeClass('active');
            Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_select_course', {
              event: 'mmiq_select_course',
              item_name: $category.data('name')
            });
          }
        });
        $myMenuIq.find('.close-full-summary').on('click', function (ev) {
          $myMenuIq.removeClass('full-summary');
          ev.preventDefault();
          ev.stopPropagation();
        });
        $close.on('click', function (ev) {
          if ($myMenuIq.hasClass('full-summary')) {
            $myMenuIq.removeClass('full-summary');
          } else {
            Drupal.srh_mymenuiq_helper._collapse($myMenuIq);
          }
          ev.preventDefault();
          ev.stopPropagation();
        });
        $infoButton.on('click', function (ev) {
          Drupal.srh_mymenuiq_helper._panelInfoToggleCollapse($myMenuIq);
        });
        $infoBack.on('click', function (ev) {
          Drupal.srh_mymenuiq_helper._panelInfoToggleCollapse($myMenuIq);
        });
        // initialize score
        $myMenuIq.find('.srh-sidedish').each(function () {
          let score = $(this).data('score');
          Drupal.srh_mymenuiq_commands_helper.MenuIqSidedishRefreshScore($(this), score);
        });
        // initialize see more / less
        let seeMoreTxt = Drupal.t('See more');
        let seeLessTxt = Drupal.t('Hide');
        $myMenuIq.find('.srh-sidedish-attributes').each(function () {
          if ($(this).children().length) {
            $(this).after('<div class="srh-sidedish-toggle-attributes">' + seeMoreTxt + '</div>');
          }
        });

        // Close srh-balanced-100 info panel.
        $myMenuIq.find('.srh-balanced-100-close').click(function() {
          $("#srh-balanced-100").hide();
        });

        $myMenuIq.on("click", ".srh-sidedish-toggle-attributes", function () {
          var $this = $(this);
          if ($this.data('visible')) {
            $this.data('visible', false).text(seeMoreTxt);
            $this.parent().find('.srh-sidedish-attributes').removeClass('show');
            $this.parents('.paragraph--type-srh-sidedish').removeClass('srh-sidedish-w-attr');
          } else {
            $this.data('visible', true).text(seeLessTxt);
            $this.parent().find('.srh-sidedish-attributes').addClass('show');
            $this.parents('.paragraph--type-srh-sidedish').addClass('srh-sidedish-w-attr');
          }
        });

        $sideDisheAction.on('click', function (ev) {
          let $sideDish = $(this).parents('.srh-sidedish');
          if ($sideDish.hasClass('selected')) {
            Drupal.srh_mymenuiq_helper._operationSideDishAjax($myMenuIq, $sideDish, 'remove');
          } else {
            Drupal.srh_mymenuiq_helper._operationSideDishAjax($myMenuIq, $sideDish, 'add');
          }
          ev.preventDefault();
          ev.stopPropagation();
        });

        // Add view summary inside each category.
        const $viewSummaryW = $('.srh-view-summary-w', $myMenuIq);
        $myMenuIq.find('.categories > .tab-content').each(function () {
          $viewSummaryW.clone().appendTo(this);
        });
        $(".srh-toggle-summary", $myMenuIq).on('click', function(ev) {
          ev.preventDefault();
          ev.stopPropagation();
          if ($(this).hasClass('view-summary')) {
            $myMenuIq.addClass('full-summary');
            Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_see_combination');
          }
          else {
            $myMenuIq.removeClass('full-summary');
          }
        });

        // Inject media if media is missing
        $myMenuIq.find('.srh-sidedish .paragraph--type-srh-sidedish').each(function() {
          console.log($(this).find('> .field--name-field-srh-media').length);
          if (!$(this).find('> .field--name-field-srh-media').length) {
            $(this).prepend('<div class="field--name-field-srh-media field--type-entity-reference">' +
              '  <div class="field--name-field-media-image field--type-image">' +
              '   <img loading="lazy" src="' + Drupal.url(drupalSettings.mymenuiq.defaultImg) +'" alt="">' +
              '</div></div>');
          }
        });

      });

      $(document).mouseup(function (e) {
        var $menuIqExpanded = $('.my-menu-iq.expanded');
        // if the target of the click isn't the container nor a descendant of the container
        if (!$menuIqExpanded.is(e.target) && $menuIqExpanded.has(e.target).length === 0) {
          Drupal.srh_mymenuiq_helper._collapse($menuIqExpanded);
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
