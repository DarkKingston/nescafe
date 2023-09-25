(function ($, Drupal, drupalSettings) {

  Drupal.srh_mymenuiq_commands_helper = {};

  Drupal.srh_mymenuiq_commands_helper.isMobile = function () {
    if ($(window).width() < 769) {
      return true;
    }
    return false;
  };

  Drupal.srh_mymenuiq_commands_helper.MenuIqSidedishRefreshScore = function ($sidedish, scoreDiff) {
    let $scoreDiffText = $sidedish.find('.score-diff');
    if (!$scoreDiffText.length) {
      $sidedish.find(".srh-sidedish-title").after('<div class="score-diff"></div>');
      $scoreDiffText = $sidedish.find('.score-diff');
    }
    // Refresh sidedish score
    $sidedish.data('score', scoreDiff);
    $sidedish.attr('data-score', scoreDiff);
    $scoreDiffText.text(scoreDiff);
    if (scoreDiff > 0) {
      $scoreDiffText.text("+" + scoreDiff);
    }
    $scoreDiffText.toggleClass('negative', scoreDiff < 0);
    $scoreDiffText.toggleClass('positive', scoreDiff >= 0);
  };

  Drupal.srh_mymenuiq_commands_helper.MenuIqRefreshScoreDiffCommand = function (element, sidedishesScoreDiff) {
    $.each(sidedishesScoreDiff, function (index, value) {
      let $sidedish = element.find('.srh-sidedish[data-id="' + index + '"]');
      Drupal.srh_mymenuiq_commands_helper.MenuIqSidedishRefreshScore($sidedish, value);
    });
  };

  Drupal.srh_mymenuiq_commands_helper.updateCategoryCountSelected = function (element, sidedish){
    let $selectedSidedishes = JSON.parse(localStorage.getItem('Drupal.srh_menuiq_selected_sidedishes'));
    let $emptyText = drupalSettings.mymenuiq.menu_sidedishes.empty_category_text;

    // Refresh category score
    var category, type;
    for (category in $selectedSidedishes) {
      let score = 0;
      let $currentCategory = element.find('.category.' + category);
      let countSelected = $selectedSidedishes[category]['recipe'].length + $selectedSidedishes[category]['complement'].length;
      if (countSelected) {
        let countSelectedText = Drupal.formatPlural(countSelected, '1 item selected', '@count items selected');
        $currentCategory.find('.category-sidedishes-selected').text(countSelectedText);
        for (type in $selectedSidedishes[category]) {
          $.each($selectedSidedishes[category][type], function (index, sidedishId) {
            let $selectedSidedish = element.find('.srh-sidedish[data-id="' + sidedishId + '"][data-category="' + category + '"]');
            score += $selectedSidedish.data('score');
          });
        }
        $currentCategory.find('.score').text(score);
        if (score > 0) {
          $currentCategory.find('.score').text("+" + score);
        }
        $currentCategory.find('.score').toggleClass('negative', score < 0);
        $currentCategory.find('.score').toggleClass('positive', score >= 0);
      } else {
        $currentCategory.find('.category-sidedishes-selected').text($emptyText);
        $currentCategory.find('.score').text('');
        $currentCategory.find('.score').removeClass('negative positive');
      }
    }
  };

  Drupal.srh_mymenuiq_commands_helper.getAddSidedishPanelBox = function ($myMenuIq) {
    let $addSidedishPanel = $('<div class="srh-add-sidedish-panel srh-sidedish"></div>');
    $addSidedishPanel.click(function() {
      $myMenuIq.removeClass('full-summary');
      Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_add_recipe_checkout');
    });
    return $addSidedishPanel;
  };

  Drupal.srh_mymenuiq_commands_helper.MenuIqHighlightSidedish = function ( element, sidedish) {
    let $summarySidedishes = element.find('.panel-combination .sidedishes-selected');

    Drupal.srh_mymenuiq_commands_helper.updateCategoryCountSelected(element, sidedish);
    $summarySidedishes.append(sidedish.clone(true));
    const totalSelected = $summarySidedishes.find('.srh-sidedish:not(.srh-add-sidedish-panel)').length;
    if (this.isMobile()) {
      $summarySidedishes.find('.srh-add-sidedish-panel').remove();
      if (totalSelected < 7) {
        let $addSidedishPanel = this.getAddSidedishPanelBox(element);
        $summarySidedishes.append($addSidedishPanel);
      }
    }
    else {
      $summarySidedishes.find('.srh-add-sidedish-panel').remove();
    }

    let countRecipes = $summarySidedishes.find('.srh-sidedish[data-id]').length;
    element.find('.srh-toggle-summary.view-summary').text(Drupal.formatPlural(countRecipes, drupalSettings.mymenuiq.summary.button_open_text_singular, drupalSettings.mymenuiq.summary.button_open_text_plural));
    element.find('.srh-view-summary-w').addClass('show');
    $("#srh-panel-combination .title").text(Drupal.t('Your Selection (@count)', {'@count': totalSelected}));
  };

  Drupal.srh_mymenuiq_commands_helper.MenuIqRemoveHighlightSidedish = function ( element, sidedish ) {
    let $summarySidedishes = element.find('.panel-combination .sidedishes-selected');
    let $summarySidedisSelected = $summarySidedishes.find('.srh-sidedish[data-id="' + sidedish.data('id') + '"]');
    let $viewSummary = element.find('.view-summary');

    Drupal.srh_mymenuiq_commands_helper.updateCategoryCountSelected(element, sidedish);
    $summarySidedisSelected.remove();
    if ($summarySidedishes.find('.srh-sidedish.selected').length <= 0){
      element.removeClass('full-summary');
      element.find('.srh-view-summary-w').removeClass('show');
    }
    else {
      const totalSelected = $summarySidedishes.find('.srh-sidedish').length;
      $("#srh-panel-combination .title").text(Drupal.t('Your Selection (@count)', {'@count': totalSelected}));

      // Add + panel
      if (!$summarySidedishes.find('.srh-add-sidedish-panel').length) {
        let $addSidedishPanel = this.getAddSidedishPanelBox(element);
        $summarySidedishes.append($addSidedishPanel);
      }
    }
    let countRecipes = $summarySidedishes.find('.srh-sidedish[data-id]').length;
    $viewSummary.text(Drupal.formatPlural(countRecipes < 1 ? 1 : countRecipes, drupalSettings.mymenuiq.summary.button_open_text_singular, drupalSettings.mymenuiq.summary.button_open_text_plural));
  };

  Drupal.srh_mymenuiq_commands_helper.MenuIqUpdateMainScore = function ( element) {
    let $mainProgressBar = element.find('.srh_progress-bar');
    let $selectedSidedishesCategories = JSON.parse(localStorage.getItem('Drupal.srh_menuiq_selected_sidedishes'));
    var $scoreDiffTotal = 0;
    for(category in $selectedSidedishesCategories) {
      for(type in $selectedSidedishesCategories[category]){
        let sidedishes = $selectedSidedishesCategories[category][type];
        $.each(sidedishes,function (index, sidedishId){
          let $selectedSidedish = element.find('.srh-sidedish[data-id="' + sidedishId + '"][data-category="' + category + '"]');
          $scoreDiffTotal += $selectedSidedish.data('score');
        });
      }
    }
    let $scoreResult = $scoreDiffTotal + $mainProgressBar.data('orgpercent');
    $mainProgressBar.data('percent', $scoreResult);
    $mainProgressBar.attr('data-percent', Math.min(Math.max($scoreResult, 0), 100));
    Drupal.srh_mymenuiq_commands_helper.MenuIqUpdateBalance(element);
  };

  Drupal.srh_mymenuiq_commands_helper.MenuIqUpdateBalance = function (element ) {
    const balance = drupalSettings.mymenuiq.balance;
    const score = element.find('.srh_progress-bar').data('percent');
    $.each(balance, function (index, value) {
      if (score >= value.min && score <= value.max) {
        element.data('balance', index);
        element.attr('data-balance', index);
        element.find('.srh_progress-bar__progress').css('stroke', value.color);
        element.find('.balance .title, .summary-title').text(Drupal.t(value.title));
        element.find('.balance .subtitle, .summary-subtitle').text(Drupal.t(value.subtitle));
        return false;
      }
    });
    $("#srh-balanced-100").toggleClass('show', score >= 100);
  };

  Drupal.AjaxCommands.prototype.MenuIqRemoveSidedishCommand = function (ajax, response, status ) {
    let $menuIq =  $('.my-menu-iq');
    let $sidedish = $menuIq.find('.srh-sidedish[data-id="' + response.sidedishId + '"][data-category="' + response.sidedishCategory + '"]');
    let $selectedSidedishes = JSON.parse(localStorage.getItem('Drupal.srh_menuiq_selected_sidedishes'));
    let $sidedishesScoreDiff = JSON.parse(response.sidedishesScoreDiff);
    let $sidedishType = $sidedish.data('type');
    let $sidedishAction = $sidedish.find('.srh-sidedish-action');

    $sidedish.removeClass('selected');
    $sidedishAction.text($sidedishAction.data('addtext'));
    $selectedSidedishes[response.sidedishCategory][$sidedishType].splice($.inArray($sidedish.data('id'), $selectedSidedishes[response.sidedishCategory][$sidedishType]), 1);
    localStorage.setItem('Drupal.srh_menuiq_selected_sidedishes', JSON.stringify($selectedSidedishes));
    Drupal.srh_mymenuiq_commands_helper.MenuIqRefreshScoreDiffCommand($menuIq, $sidedishesScoreDiff);
    Drupal.srh_mymenuiq_commands_helper.MenuIqRemoveHighlightSidedish($menuIq, $sidedish);
    Drupal.srh_mymenuiq_commands_helper.MenuIqUpdateMainScore($menuIq);
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event' : 'MyMenuIQEvent',
      'eventCategory' : 'MyMenuIQ',
      'eventAction' : 'MyMenuIQ Remove Complementary Dish',
      'eventLabel' : $sidedish.data('name') + ' Removed | ' + $menuIq.find('.category.' + response.sidedishCategory +' .label').text()
    });
    Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_remove_recipe');
  };

  Drupal.AjaxCommands.prototype.MenuIqAddSidedishCommand = function (ajax, response, status ) {
    let $menuIq =  $('.my-menu-iq');
    let $sidedish = $menuIq.find('.srh-sidedish[data-id="' + response.sidedishId + '"][data-category="' + response.sidedishCategory + '"]');
    let $selectedSidedishes = JSON.parse(localStorage.getItem('Drupal.srh_menuiq_selected_sidedishes'));
    let $sidedishesScoreDiff = JSON.parse(response.sidedishesScoreDiff);
    let $sidedishType = $sidedish.data('type');
    let $sidedishAction = $sidedish.find('.srh-sidedish-action');

    $sidedish.addClass('selected');
    $sidedishAction.text($sidedishAction.data('removetext'));
    if (!$selectedSidedishes[response.sidedishCategory]) {
      $selectedSidedishes[response.sidedishCategory] = {recipe: [], complement: []};
    }
    $selectedSidedishes[response.sidedishCategory][$sidedishType].push($sidedish.data('id'));
    localStorage.setItem('Drupal.srh_menuiq_selected_sidedishes', JSON.stringify($selectedSidedishes));
    Drupal.srh_mymenuiq_commands_helper.MenuIqRefreshScoreDiffCommand($menuIq, $sidedishesScoreDiff);

    Drupal.srh_mymenuiq_commands_helper.MenuIqHighlightSidedish($menuIq, $sidedish);
    Drupal.srh_mymenuiq_commands_helper.MenuIqUpdateMainScore($menuIq);
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event' : 'MyMenuIQEvent',
      'eventCategory' : 'MyMenuIQ',
      'eventAction' : 'MyMenuIQ Add Complementary Dish',
      'eventLabel' : $sidedish.data('name') + ' Added | ' + $menuIq.find('.category.' + response.sidedishCategory +' .label').text(),
    });
    Drupal.srh_mymenuiq_helper._trigger_ga_event('my_menu_iq_add_recipe');
  };

})(jQuery, Drupal, drupalSettings);
