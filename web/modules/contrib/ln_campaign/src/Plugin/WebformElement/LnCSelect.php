<?php

namespace Drupal\ln_campaign\Plugin\WebformElement;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_campaign\Entity\LnCampaignType;
use Drupal\webform\Plugin\WebformElement\Select;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'select' element.
 *
 * @WebformElement(
 *   id = "ln_campaign_select",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Select.php/class/Select",
 *   label = @Translation("LnC Select"),
 *   description = @Translation("Provides a form element for a drop-down menu or scrolling selection box."),
 *   category = @Translation("Options elements"),
 * )
 */
class LnCSelect extends Select {

  /**
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var ContentEntityStorageInterface
   */
  protected $lnCampaignStorage;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->lnCampaignStorage = $container->get('entity_type.manager')->getStorage('ln_campaign');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
      'field_source_options' => '',
      ] + parent::defineDefaultProperties();
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $ln_campaign_types = LnCampaignType::loadMultiple();
    $options = [];
    foreach ($ln_campaign_types as $bundle){
      $fields = $this->entityFieldManager->getFieldDefinitions('ln_campaign', $bundle->id());
      foreach ($fields as $field_name => $field){
        if($fieldStorage = $field->getFieldStorageDefinition()){
          if($fieldStorage->isMultiple() && $fieldStorage->getType() == 'string'){
            $options[$field_name] = $field->label();
          }
        }
      }
    }
    $form = parent::form($form, $form_state);
    unset($form['options']['options']);
    $form['options']['field_source_options'] = [
      '#type' => 'select',
      '#title' => 'Field Source',
      '#options' => $options,
      '#weight' => -1,
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function getElementSelectorSourceValues(array $element) {
    $element['#options'] = [];
    return parent::getElementSelectorSourceValues($element);
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    $element['#options'] = [];
    parent::prepare($element, $webform_submission);
    $field_source_options = $element['#field_source_options'] ?? '';
    $data = $webform_submission->getData();
    $ln_campaign_id = $data['ln_campaign_id'] ?? '';
    if($ln_campaign = $this->lnCampaignStorage->load($ln_campaign_id)){
      if($ln_campaign->hasField($field_source_options)){
        $values = $ln_campaign->get($field_source_options)->getValue();
        $options = [];
        foreach ($values as $item){
          $options[$item['value']] = $item['value'];
        }
        $element['#options'] = $options;
      }
    }
  }
}
