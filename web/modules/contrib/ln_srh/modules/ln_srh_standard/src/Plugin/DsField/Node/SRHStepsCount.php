<?php

namespace Drupal\ln_srh_standard\Plugin\DsField\Node;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\node\NodeInterface;

/**
 * Plugin that renders steps number
 *
 * @DsField(
 *   id = "srh_steps_count",
 *   title = @Translation("SRH Steps Count"),
 *   provider = "ln_srh_basic",
 *   entity_type = "node",
 *   ui_limit = {"srh_recipe|*"},
 * )
 */

class SRHStepsCount extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $elements['singular_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Singular Prefix'),
      '#default_value' => $config['singular_prefix'],
    ];

    $elements['plural_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Plural Prefix'),
      '#default_value' => $config['plural_prefix'],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $singular_prefix = $config['singular_prefix'];
    $plural_prefix = $config['plural_prefix'];
    $summary[] = $this->t('Singular Prefix: @singular_prefix', ['@singular_prefix' => $singular_prefix]);
    $summary[] = $this->t('Plural Prefix: @plural_prefix', ['@plural_prefix' => $plural_prefix]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = [
      'singular_prefix' => 'Step',
      'plural_prefix' => 'Steps',
    ];
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    /** @var NodeInterface $recipe */
    $recipe = $this->entity();
    if($recipe->hasField('field_srh_steps')){
      $count = $recipe->get('field_srh_steps')->count();
      $singular_prefix = $config['singular_prefix'];
      $plural_prefix = $config['plural_prefix'];
      $output = \Drupal::translation()->formatPlural($count, "1 @singular_prefix", '@count @plural_prefix',['@singular_prefix' => $singular_prefix, '@plural_prefix' => $plural_prefix]);
      return  [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $output,
      ];
    }
    return [];
  }

}
