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
 *   id = "ln_quiz_reset",
 *   title = @Translation("Quiz Reset Button"),
 *   provider = "ln_quiz",
 *   entity_type = "paragraph",
 *   ui_limit = {"quiz|*"},
 * )
 */
class QuizReset extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    return [
      '#type' => 'link',
      '#title' => $this->t($config['text']),
      '#url' => Url::fromRoute('ln_quiz.process',[
        'quiz' => $this->entity()->id(),
        'action' => LnQuizConstants::QUIZ_ACTION_RESET
      ]),
      '#attached' => [
        'library' => [
          'ln_quiz/quiz-localstorage-command'
        ]
      ],
      '#attributes' => [
        'class' => ['quiz-ajax']
      ]
    ];
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
      'text' => 'Retake quiz',
    ];
    return $configuration;
  }
}
