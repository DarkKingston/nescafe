<?php

namespace Drupal\ln_srh_menuiq\Plugin\DsField\Paragraph;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\externalauth\Exception\ExternalAuthRegisterException;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin that renders a sidedish title
 *
 * @DsField(
 *   id = "srh_sidedish_title",
 *   title = @Translation("SRH Sidedish Title"),
 *   provider = "ln_srh_menuiq",
 *   entity_type = "paragraph",
 *   ui_limit = {"srh_sidedish|*"},
 * )
 */

class SRHSidedishTitle extends DsFieldBase {

  protected function isComplement($sideDish) {
    if (!$sideDish->get(SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_FIELD)->isEmpty()) {
      /** @var \Drupal\taxonomy\TermInterface $srhAssociationType */
      $srhAssociationType = $sideDish->get(SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_FIELD)->entity;
      if ($srhAssociationType->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString() == SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_COMPLEMENT) {
        return TRUE;
      }
    }

    return FALSE;
  }

  protected function getSidedishComplementEntity(EntityInterface $sideDish) {
    if (!$this->isComplement($sideDish) || $sideDish->get('field_srh_id')->isEmpty()) {
      return NULL;
    }
    $complement = NULL;
    $complementSrhId = $sideDish->get('field_srh_id')->value;
    try {
      /** @var \Drupal\ln_srh_full\Services\SRHComplementUtilsInterface $srhUtils */
      $srhUtils = \Drupal::service('ln_srh_full.complement_utils');
      $complement = $srhUtils->getComplementBySRHId($complementSrhId);
    } catch (\Exception $e) {
    }
    return $complement;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $sideDish = $this->entity();
    $config = $this->getConfiguration();
    $title = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_TITLE_FIELD)->getString();
    if ($config['link']) {
      $url = null;
      /** @var NodeInterface $recipe */
      if ($recipe = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->entity) {
        $url = $recipe->toUrl();
      }
      elseif ($complement = $this->getSidedishComplementEntity($sideDish)) {
        $url = $complement->toUrl();
      }

      if ($url) {
        $build = [
          '#type' => 'link',
          '#url' => $url,
          '#attributes' => [
            'class' => ['srh-sidedish-title'],
          ],
          '#title' => $title,
        ];
        if ($config['target'] == 'modal') {
          $build['#attributes']['class'][] = 'use-ajax';
          $build['#attributes']['data-dialog-type'] = 'modal';
          $build['#attributes']['data-dialog-options'] = Json::encode([
            'dialogClass' => 'srh-sidedish-recipe-modal',
          ]);
        }
        else {
          $build['#attributes']['target'] = $config['target'];
        }

        return $build;
      }
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['srh-sidedish-title'],
      ],
      '#value' => $title,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::settingsForm($form, $form_state);
    $form['link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link'),
      '#default_value' => $config['link'],
    ];
    $form['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Target'),
      '#options' => [
        '_blank' => 'Blank',
        '_self' => 'Self',
        '_parent' => 'Parent',
        '_top' => 'Top',
        'modal' => 'Modal',
      ],
      '#states' => [
        'visible' => [
          ':input[name="fields[srh_sidedish_title][settings_edit_form][settings][link]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config['target'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $summary = [];
    $summary[] = 'Link: ' . $config['link'] ? 'Yes' : 'No';
    $summary[] = 'Target: ' . $config['target'];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = [
      'link' => TRUE,
      'target' => 'modal',
    ];
    return $configuration;
  }

}
