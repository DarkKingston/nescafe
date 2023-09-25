<?php

namespace Drupal\ln_quiz\Plugin\DsField\Paragraph;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Drupal\ds\Plugin\DsField\Title;

/**
 * Plugin that renders the 'quiz parent title'
 *
 * @DsField(
 *   id = "ln_quiz_title",
 *   title = @Translation("Quiz Title"),
 *   provider = "ln_quiz",
 *   entity_type = "paragraph",
 *   ui_limit = {"quiz_question|*"},
 * )
 */
class QuizTitle extends Title {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $settings = parent::settingsForm($form,$form_state);
    unset($settings['link']);
    return $settings;
  }
  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $summary = [];
    $summary[] = 'Wrapper: ' . $config['wrapper'];
    if (!empty($config['class'])) {
      $summary[] = 'Class: ' . $config['class'];
    }
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\paragraphs\ParagraphInterface $quiz_question */
    $quiz_question = $this->entity();
    $quiz = $quiz_question->getParentEntity();
    $config = $this->getConfiguration();

    // Initialize output.
    $output = $quiz->get('field_quiz_title')->getString();

    if (empty($output)) {
      return [];
    }

    $template = <<<TWIG
{% if wrapper %}
<{{ wrapper }}{{ attributes }}>
{% endif %}
  {{ output }}
{% if wrapper %}
</{{ wrapper }}>
{% endif %}
TWIG;
    // Build the attributes.
    $attributes = new Attribute();
    if (!empty($config['class'])) {
      $attributes->addClass($config['class']);
    }
    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'wrapper' => !empty($config['wrapper']) ? $config['wrapper'] : '',
        'attributes' => $attributes,
        'output' => $output,
      ],
    ];
  }

}
