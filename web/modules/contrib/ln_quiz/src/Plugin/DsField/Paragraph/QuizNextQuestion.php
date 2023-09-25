<?php

namespace Drupal\ln_quiz\Plugin\DsField\Paragraph;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_quiz\LnQuizConstants;

/**
 * Plugin that renders the 'answer input'
 *
 * @DsField(
 *   id = "ln_quiz_next_question",
 *   title = @Translation("Quiz Next Question"),
 *   provider = "ln_quiz",
 *   entity_type = "paragraph",
 *   ui_limit = {"quiz_question|*"},
 * )
 */
class QuizNextQuestion extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    /** @var \Drupal\paragraphs\ParagraphInterface $quiz_question */
    $quiz_question = $this->entity();
    /** @var \Drupal\paragraphs\ParagraphInterface $quiz */
    $quiz = $quiz_question->getParentEntity();
    /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $item */
    if($quiz->hasField(LnQuizConstants::FIELD_QUIZ_QUESTIONS)){
      foreach ($quiz->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS) as $key => $item){
        if($item->entity->id() == $quiz_question->id()){
          if($quiz->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS)->offsetExists($key + 1)){
            return [
              '#type' => 'link',
              '#title' => $this->t($config['text_next']),
              '#url' => Url::fromRoute('ln_quiz.process',[
                'quiz' => $quiz->id(),
                'question' => $quiz->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS)->get($key + 1)->entity->id(),
                'action' => LnQuizConstants::QUIZ_ACTION_QUESTION]
              ),
              '#attached' => [
                'library' => [
                  'ln_quiz/quiz-localstorage-command'
                ]
              ],
              '#attributes' => [
                'class' => ['quiz-ajax'],
              ],
            ];
          }else{
            return [
              '#type' => 'link',
              '#title' => $this->t($config['text_finish']),
              '#url' => Url::fromRoute('ln_quiz.process',[
                'quiz' => $quiz->id(),
                'action' => LnQuizConstants::QUIZ_ACTION_SUMMARY]
              ),
              '#attached' => [
                'library' => [
                  'ln_quiz/quiz-localstorage-command'
                ]
              ],
              '#attributes' => [
                'class' => ['quiz-ajax'],
              ],
            ];
          }
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
    $form['text_next'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text next question'),
      '#default_value' => $config['text_next'],
      '#required' => TRUE,
    ];
    $form['text_finish'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text finish quiz'),
      '#default_value' => $config['text_finish'],
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
    $summary[] = 'Text next question: ' . $config['text_next'];
    $summary[] = 'Text finish quiz: ' . $config['text_finish'];
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = [
      'text_next' => 'Next question',
      'text_finish' => 'Finish quiz',
    ];
    return $configuration;
  }

}
