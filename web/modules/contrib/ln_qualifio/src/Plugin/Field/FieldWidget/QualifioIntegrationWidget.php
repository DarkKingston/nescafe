<?php

namespace Drupal\ln_qualifio\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'QualifioIntegrationWidget' widget.
 *
 * @FieldWidget(
 *   id = "QualifioIntegrationWidget",
 *   label = @Translation("Qualifio Integration - Widget"),
 *   description = @Translation("Qualifio Integration  - Widget"),
 *   field_types = {
 *     "QualifioIntegrationParams",
 *   },
 *   multiple_values = False,
 * )
 */
class QualifioIntegrationWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $integrationList = isset($items[$delta]->qualifiointegrationType) ? $items[$delta]->qualifiointegrationType : '';

    $campaign_feed_url = \Drupal::config('ln_qualifio.settings')->get('campaigns_feed');
    $file = simplexml_load_file($campaign_feed_url);
    $json = $file;

    $options = ['_none' => '- None -'];

    foreach ($json->channels as $integration_key => $integration_val) {
      if (!empty($integration_val->campaign->campaignId)) {
        $unique_key = 'campaign_' . (int) $integration_val->campaign->campaignId;
        $options[$unique_key] = (string) $integration_val->campaign->campaignTitle;
      }
    }

    $element['qualifiointegrationType'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Qualifio'),
      '#description'   => $this->t('Select the Camapaign'),
      '#options'       => $options,
      '#default_value' => $integrationList,
    ];
    return $element;
  }

}
