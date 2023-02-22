<?php

namespace Drupal\ln_srh_menuiq\Plugin\DsField\Paragraph;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;

/**
 * Plugin that renders a link action
 *
 * @DsField(
 *   id = "srh_action_sidedish",
 *   title = @Translation("SRH Action Sidedish"),
 *   provider = "ln_srh_menuiq",
 *   entity_type = "paragraph",
 *   ui_limit = {"srh_sidedish|*"},
 * )
 */

class SRHActionSidedish extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $sideDish = $this->entity();
    $type = 'complement';
    if($recipe = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->entity){
      $type = 'recipe';
    }
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['srh-sidedish-action'],
        'data-addtext' => $this->t($config['add_text'],['@type'=> $type]),
        'data-removetext' => $this->t($config['remove_text'],['@type'=> $type]),
      ],
      '#value' => $this->t($config['add_text'],['@type'=> $type]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::settingsForm($form, $form_state);
    $form['add_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add Text'),
      '#default_value' => $config['add_text'],
      '#description' => $this->t('Available token @type to show the type of sidedish, recipe or complement.'),
      '#required' => TRUE,
    ];
    $form['remove_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Remove text'),
      '#default_value' => $config['remove_text'],
      '#description' => $this->t('Available token @type to show the type of sidedish, recipe or complement.'),
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
    $summary[] = 'Add Text: ' . $config['add_text'];
    $summary[] = 'Remove Text: ' . $config['remove_text'];
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = [
      'add_text' => 'Add @type',
      'remove_text' => 'Remove @type',
    ];
    return $configuration;
  }
}
