(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.ln_quiz_init = {
    attach: function (context, settings) {
      $('.paragraph.paragraph--type-quiz',context).once('quiz_init').each(function () {
        var $this = $(this);
        var id = $this.data('quiz-id');
        var $quizStorageJson = localStorage.getItem('Drupal.quiz_' + id);
        if($quizStorageJson !== null && settings.ln_quiz[id] && settings.ln_quiz[id].urls && settings.ln_quiz[id].urls.init){
          Drupal.ajax({
            url: settings.ln_quiz[id].urls.init,
            submit: {quizStorageJson: $quizStorageJson},
          }).execute();
        }
      });
    }
  };
  Drupal.behaviors.ln_quiz_summary = {
    attach: function (context, settings) {
      $('.paragraph.paragraph--type-quiz').find('.quiz-ajax').once('quiz_ajax_link').each(function () {
        var $this = $(this);
        var $quiz = $this.parents('.paragraph.paragraph--type-quiz');
        var $quizStorageJson = localStorage.getItem('Drupal.quiz_' + $quiz.data('quiz-id'));
        Drupal.ajax({
          url: $this.attr('href'),
          element: this,
          event: 'click',
          submit: {quizStorageJson: $quizStorageJson},
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
