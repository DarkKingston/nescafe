(function ($, Drupal) {
  "use strict";

  /**
   * Add QuizSetLocalStorage command.
   */
  Drupal.AjaxCommands.prototype.QuizSetLocalStorage = function (ajax, response, status) {
    var $localStorageKey = response.localStorageKey;
    localStorage.setItem($localStorageKey,response.localStorageValue);
  };

  /**
   * Add QuizRemoveLocalStorage command.
   */
  Drupal.AjaxCommands.prototype.QuizRemoveLocalStorage = function (ajax, response, status) {
    var $localStorageKey = response.localStorageKey;
    localStorage.removeItem($localStorageKey);
  }

})(jQuery, Drupal);
