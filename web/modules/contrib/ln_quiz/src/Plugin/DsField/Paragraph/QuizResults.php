<?php

namespace Drupal\ln_quiz\Plugin\DsField\Paragraph;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_quiz\LnQuizConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Plugin that renders the 'start button'
 *
 * @DsField(
 *   id = "ln_quiz_results",
 *   title = @Translation("Quiz Results"),
 *   provider = "ln_quiz",
 *   entity_type = "paragraph",
 *   ui_limit = {"quiz|*"},
 * )
 */
class QuizResults extends DsFieldBase {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,  RequestStack $requestStack) {
    $this->request = $requestStack->getCurrentRequest();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $totalQuestions = $this->entity()->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS)->count();
    $quizStorage = Json::decode($this->request->get('quizStorageJson'));
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t($config['text'],['@corrects'=>$quizStorage['corrects'],'@total'=>$totalQuestions]),
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
      '#description' => $this->t('You can use @corrects and @total to indicate the correct answers and total questions respectively.'),
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
      'text' => 'You answered @corrects / @total correctly!',
    ];
    return $configuration;
  }


}
