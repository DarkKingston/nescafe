<?php

namespace Drupal\ln_quiz\Plugin\Field\FieldFormatter;


use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Url;
use Drupal\ln_quiz\LnQuizConstants;


/**
 * Plugin implementation of the 'ln_Quiz_Answers' formatter.
 *
 * @FieldFormatter(
 *   id = "ln_quiz_answers_formatter",
 *   label = @Translation("Quiz Answer Formatter"),
 *   field_types = {
 *     "ln_quiz_answers"
 *   }
 * )
 */

class QuizAnswersFormatter extends StringFormatter{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
      /** @var \Drupal\paragraphs\ParagraphInterface $quiz_question */
    $quiz_question = $items->getEntity();
    /** @var \Drupal\paragraphs\ParagraphInterface $quiz */
    $quiz = $quiz_question->getParentEntity();

    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'link',
        '#title' => $this->viewValue($item),
        '#url' => Url::fromRoute('ln_quiz.process',[
          'quiz' => $quiz->id(),
          'question' => $quiz_question->id(),
          'action' => LnQuizConstants::QUIZ_ACTION_CHECK,
        ],[
          'query'=>[
            'answer' => $delta
          ]
        ]),
        '#attached' => [
          'library' => [
            'ln_quiz/quiz-localstorage-command'
          ]
        ],
        '#attributes' => [
          'class' => ['quiz-ajax']
        ],
      ];

    }
    return $elements;
  }

}
