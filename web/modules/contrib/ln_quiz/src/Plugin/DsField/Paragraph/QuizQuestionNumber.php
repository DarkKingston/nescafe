<?php

namespace Drupal\ln_quiz\Plugin\DsField\Paragraph;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_quiz\LnQuizConstants;

/**
 * Plugin that renders the 'start button'
 *
 * @DsField(
 *   id = "ln_quiz_question_number",
 *   title = @Translation("Quiz Question Number"),
 *   provider = "ln_quiz",
 *   entity_type = "paragraph",
 *   ui_limit = {"quiz_question|*"},
 * )
 */
class QuizQuestionNumber extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    /** @var \Drupal\paragraphs\ParagraphInterface $quiz_question */
    $quiz_question = $this->entity();
    $quiz = $quiz_question->getParentEntity();
    if($quiz->hasField(LnQuizConstants::FIELD_QUIZ_QUESTIONS)) {
      /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $item */
      foreach ($quiz->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS) as $key => $item) {
        if ($quiz_question->id() == $item->entity->id()) {
          return [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => $this->t($config['text'], [
              '@current' => $key + 1,
              '@total' => $quiz->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS)->count()
            ]),
          ];
        }
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::settingsForm($form, $form_state);
    $form['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#description' => $this->t('You can use @current and @total to indicate the current question number and total questions respectively.'),
      '#default_value' => $config['text'],
      '#required' => TRUE,
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $summary = [];
    $summary[] = 'Text: ' . $config['text'];
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = [
      'text' => 'Question @current / @total',
    ];
    return $configuration;
  }
}
