<?php

namespace Drupal\ln_quiz\Plugin\DsField\Paragraph;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that renders the 'start button'
 *
 * @DsField(
 *   id = "ln_quiz_question_check_label",
 *   title = @Translation("Quiz Check Label"),
 *   provider = "ln_quiz",
 *   entity_type = "paragraph",
 *   ui_limit = {"quiz_question|*"},
 * )
 */
class QuizQuestionCheckLabel extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t($config['text']),
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
      'text' => '',
    ];
    return $configuration;
  }

}
