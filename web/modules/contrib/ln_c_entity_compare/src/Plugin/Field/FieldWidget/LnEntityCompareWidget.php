<?php

namespace Drupal\ln_c_entity_compare\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\NestedArray;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the 'ln_c_entity_compare' field widget.
 *
 * @FieldWidget(
 *   id = "ln_c_entity_compare",
 *   label = @Translation("LN Entity Compare"),
 *   field_types = {
 *     "serialized_settings_item"
 *   },
 *   multiple_values = FALSE,
 * )
 */
class LnEntityCompareWidget extends WidgetBase {

  /**
   * The entity type manager service
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface

   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface

   */
  protected $entityTypeBundleInfo;

  /**
   * The config factory service
   * 
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config settings for the ln_c_entity_compare module
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('ln_c_entity_compare.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {

    $parents = isset($element['#parents']) ? $element['#parents'] : [];
    $field_name = $items->getName();
    $id_prefix = implode('-', array_merge($parents, [$field_name], [$delta]));
    $wrapper_id = Html::getUniqueId($id_prefix . '-ln-entity-compare-wrapper');
    $element['#field_name'] = $field_name;
    $element['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['#suffix'] = '</div>';

    $paragraph_settings = $items->getEntity()->getParagraphSettings();
    $counts = $this->initCounts($element, $paragraph_settings, $form_state);
    $element['value'] = $this->valueForm($paragraph_settings, $wrapper_id, $form, $form_state, $counts, $parents, $field_name);

    return $element;
  }

  /**
   * Helper function to build the value form element array.
   *
   * @return array
   *   The render array.
   */
  protected function valueForm($value, $wrapper_id, $form, $form_state, $counts, $parents, $field_name) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Settings'),
    ];

    $ajax_callback = [
      'callback' => [get_class($this), 'ajaxUpdateWidgetFormElement'],
      'wrapper' => $wrapper_id,
      'effect' => 'fade',
    ];

    $available_entity_types = $this->config->get('entity_bundles_per_type');

    if (empty($available_entity_types)) {
      $element['#description'] = $this->t('Please go to the <a href=":form_settings_link" target="_blank">settings page</a> of Lightnest Components: Entity compare and choose the entity types and bundles that should be available for comparison', [':form_settings_link' => Url::fromRoute('ln_c_entity_compare.settings_form')->toString()]);
      return $element;
    }

    $default_values = $this->getDefaultWidgetValues($value, $available_entity_types);

    $element['entity_type'] = [
      '#title' => $this->t('Entity type'),
      '#type' => 'select',
      '#options' => $this->getEntityTypesDropdown($available_entity_types),
      '#default_value' => $default_values['entity_type'],
      '#required' => TRUE,
      '#ajax' => $ajax_callback,
    ];

    if (empty($default_values['entity_type'])) {
      return $element;
    }

    $element['entity_bundle'] = [
      '#title' => $this->t('Entity bundle'),
      '#type' => 'select',
      '#options' => $this->getBundlesDropdown($default_values['entity_type'], $available_entity_types),
      '#default_value' => $default_values['entity_bundle'],
      '#required' => TRUE,
      '#validated' => TRUE,
      '#ajax' => $ajax_callback,
    ];

    $element['number_of_entities'] = [
      '#title' => $this->t('Number of entities that should be compared at the same time'),
      '#type' => 'number',
      '#default_value' => $default_values['number_of_entities'],
      '#required' => TRUE,
      '#min' => 2,
    ];

    $available_entity_element_template = [
      'data' => [],
      '#attributes' => [
        'class' => ['draggable']
      ],
      'weight' => [
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => [
            'draggable-weight'
          ]
        ],
      ],
      'custom_entity' => [
        '#tags' => FALSE,
        '#default_value' => NULL,
        '#validate_reference' => FALSE,
        '#type' => 'entity_autocomplete',
        '#target_type' => $default_values['entity_type'],
        '#selection_handler' => 'default',
        '#maxlength' => 1024,
        '#size' => 60,
        '#placeholder' => '',
        '#selection_settings' => [
          'target_bundles' => [$default_values['entity_bundle']],
        ],
      ],
    ];

    $element['available_entities'] = [
      '#element_validate' => [[get_class($this), 'validateAvailableEntities']],
      '#type' => 'fieldset',
      '#title' => $this->t('Entities available for comparison'),
      '#description' => $this->t('Optional. Leave empty to allow selecting all entities. Currently referencing @entity_type:@entity_bundle', ['@entity_type' => $default_values['entity_type'], '@entity_bundle' => $default_values['entity_bundle']]),
      'container' => [
        '#type' => 'table',
        '#access' => $counts['available_entities_count'] > 0,
        '#header' => [
          '',
          $this->t('Weight'),
          $this->t('Entity'),
        ],
        '#tabledrag' => [[
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'draggable-weight',
        ]],
      ],
    ];
    
    foreach ($default_values['available_entities'] as $i => $entity) {
      $element['available_entities']['container'][$i] = $available_entity_element_template;
      $element['available_entities']['container'][$i]['weight']['#default_value'] = $i;
      $element['available_entities']['container'][$i]['custom_entity']['#default_value'] = $entity;
    }
    
    for ($i = count($default_values['available_entities']); $i < $counts['available_entities_count']; $i++) {
      $element['available_entities']['container'][$i] = $available_entity_element_template;
    }

    $element['available_entities']['add_entity_button'] = [
      '#type' => 'submit',
      '#name' => 'add_entity_button',
      '#value' => $this->t('Add entity'),
      '#limit_validation_errors' => [array_merge($parents, [$field_name])],
      '#submit' => [[get_class($this), 'addEntitySubmit']],
      '#ajax' => $ajax_callback,
    ];
    return $element;
  }

  /**
   * Form API callback: validate entities available for comparison.
   * The number of entities available for comparison should not be lower than the
   * the number of entities to be compared.
   */
  public static function validateAvailableEntities(
    &$element,
    FormStateInterface $form_state,
    &$complete_form
  ) {

    if ($form_state->isRebuilding()) {
      // No need to process this validation during form rebuilds
      return;
    }

    // Get values for the whole widget (not just the available entities selected)
    $widget_values = $form_state->getValue(array_slice($element['#parents'], 0, -1));

    // Values have not been massaged at this point, so we need to loop through raw values
    $num_entities_selected = 0;
    foreach ($widget_values['available_entities']['container'] as $selected_item) {
      if ($selected_item['custom_entity']) {
        $num_entities_selected++;
      }
    }

    if ($num_entities_selected && $num_entities_selected < (int) $widget_values['number_of_entities']) {
      $form_state->setError($element, t('The number of entities available for comparison (%total_available) can not be lower than the number of entities to be compared (%num_entities).', ['%total_available' => $num_entities_selected, '%num_entities' => $widget_values['number_of_entities']]));
    }
  }

  public static function addEntitySubmit($form, &$form_state) {
    $button = $form_state->getTriggeringElement();
    // Go three levels up in the form, and get this delta value.
    $element = NestedArray::getValue(
      $form,
      array_slice($button['#array_parents'], 0, -3)
    );
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];
    // Increment the associative item count.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    $field_state['available_entities_count']++;
    static::setWidgetState($parents, $field_name, $form_state, $field_state);

    // @see \Drupal\Core\Form\FormBuilder::handleInputElement:1254
    // @see https://drupal.stackexchange.com/a/59742/6978
    // NestedArray::setValue($form_state->getUserInput(), $parents, NULL);

    $form_state->setRebuild();
  }


  /**
   * Include the element count for this item in the field state.
   *
   * @param array $element
   *   The current field element.
   * @param array $value
   *   The value of the item.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return int
   *   The current delta count.
   */
  protected function initCounts(
    array $element,
    array $value,
    FormStateInterface $form_state
  ) {
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    
    if (!isset($field_state['available_entities_count'])) {
      // Set the array element count for this delta if not set.
      $count = isset($value['available_entities']) ? count($value['available_entities']) : 0;
      $field_state['available_entities_count'] = $count;
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    $button = $form_state->getTriggeringElement();

    if ($button && empty($button['#is_button'])) {
      // When triggered by an element other than a button, we need to reset the counter,
      // notice the list of available entities is reset in such case
      // @see self::massageFormValues()
      $field_state['available_entities_count'] = 0;
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    return $field_state;
  }

  protected function getEntityTypesDropdown($widget_settings) {
    $options = [];
    foreach ($widget_settings as $entity_type => $values) {
      $options[$entity_type] = $this->entityTypeManager->getDefinition($entity_type)->getLabel();
    }
    return $options;
  }

  protected function getBundlesDropdown($entity_type_key, $widget_settings) {
    $options = [];
    if ($entity_type_key) {
      $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($entity_type_key);
      foreach ($widget_settings[$entity_type_key]['bundles'] as $bundle_key) {
        $options[$bundle_key] = $bundle_info[$bundle_key]['label'];
      }
    }
    return $options;
  }

  protected function getDefaultWidgetValues($current_values, $widget_settings) {
    $default_values = [
      'entity_type' => null,
      'entity_bundle' => null,
      'number_of_entities' => 2,
      'available_entities' => [],
    ];

    $default_values = $current_values + $default_values;

    if (empty($default_values['entity_type'])) {
      // Enable by default the first entity type available
      $default_values['entity_type'] = array_key_first($widget_settings);
    }

    // If we have a default value for entity type, we MUST provide a valid default value
    // for entity_bundle and available_entities fields
    if ($default_values['entity_type']) {
      $entity_type_bundles = $widget_settings[$default_values['entity_type']]['bundles'];
      
      // Does the current entity bundle selection belong to the selected entity type?
      if (!in_array($default_values['entity_bundle'], $entity_type_bundles)) {
        // No (or it's empty), so:

        // 1. set as default the first entity bundle available for this entity type
        $default_values['entity_bundle'] = reset($entity_type_bundles);
        
        // 2. Reset available_entities (regardless of previous selection)
        $default_values['available_entities'] = [];
      }
    }

    if ($default_values['available_entities']) {
      // The autocomplete field type expects fully loaded entity objects
      // so we need to process stored ids and load entities
      $target_entities = [];
      
      // Load and add the existing entities.
      $entities = $this->entityTypeManager
      ->getStorage($default_values['entity_type'])
      ->loadMultiple($default_values['available_entities']);

      foreach ($entities as $entity) {
        if ($entity) {
          $target_entities[] = $entity;
        }
      }

      $default_values['available_entities'] = $target_entities;
    }

    return $default_values;
  }

  /**
   * Ajax callback for the ajaxified elements in widget form
   */
  public static function ajaxUpdateWidgetFormElement(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    switch ($button['#name']) {
      case 'add_entity_button':
        $levels = -3;
        break;
      
      default:
        // By default, go TWO levels up in the form, and get this delta value.
        $levels = -2;
        break;
    }

    $element = NestedArray::getValue(
      $form,
      array_slice($button['#array_parents'], 0, $levels)
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getTargetBundle() == 'ln_c_entity_compare';
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $available_entities = &$values[0]['value']['available_entities'];

    if ($button && empty($button['#is_button'])) {
      // This was triggered by one of the select lists, reset available entities
      $available_entities = [];
    }
    elseif (!empty($available_entities['container'])) {
      uasort($available_entities['container'], 'Drupal\Component\Utility\SortArray::sortByWeightElement');
      $ids = [];
      foreach ($available_entities['container'] as $selected_entity) {
        if (!empty($selected_entity['custom_entity'])) {
          $ids[] = $selected_entity['custom_entity'];
        }
      }
      $available_entities = $ids;
    }
    else {
      // Empty container, return empty array
      $available_entities = [];
    }

    return $values;
  }
}
