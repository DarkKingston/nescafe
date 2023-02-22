<?php

namespace Drupal\ln_c_entity_compare\Form;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Lightnest Entity Compare Component settings for this site.
 */
class SettingsForm extends ConfigFormBase {

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
   * The entity display repository service
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface

   */
  protected $entityDisplayRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityDisplayRepositoryInterface $entity_display_repository
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_c_entity_compare_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_c_entity_compare.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = true;
    $form['#prefix'] = '<div id="entities-display">';
    $form['#suffix'] = '</div>';
        
    // Set reasonable defaults.
    // These are likely to have been setup already during installation of the module,
    // but they may have been left empty if default entity type or bundles didn't exist.
    // @see ln_c_entity_compare.install - ln_c_entity_compare_install()
    $default_entity_types = [
      'node' => [
        'bundles' => [],
        'view_mode' => 'ln_entity_compare'
      ],
    ];

    $config = $this->config('ln_c_entity_compare.settings');
    $selected_entity_types = $config->get('entity_bundles_per_type') ?: $default_entity_types;

    // Update form elements if form has been updated in an ajax request
    $current_entity_type_selection = $form_state->getValue('entity_bundles_per_type');

    if (!empty($current_entity_type_selection)) {
      $selected_entity_types = $current_entity_type_selection;
      $additional_entity_type = $form_state->getValue('additional_entity_types');
      $selected_entity_types[$additional_entity_type] = [];
    }

    $form['tab_entities_display'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-node',
    ];

    $fieldset_weight = 0;
    $additional_entity_types = ['_none_' => t('- Choose -')];
    $save_entity_types = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_key => $definition) {

      // We only allow Content Entities. Does it make any sense to compare Config Entities?
      if (!$definition->entityClassImplements(ContentEntityInterface::class)) {
        continue;
      }

      if (!array_key_exists($entity_type_key, $selected_entity_types)) {
        // Populate array with entity types that are not used at the moment
        $additional_entity_types[$entity_type_key] = $definition->getLabel();
        continue;
      }

      // Add selected entity types to vertical tab as fieldset
      $form['entity_bundles_per_type'][$entity_type_key] = [
        '#type' => 'details',
        '#title' => $definition->getLabel(),
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#tree' => TRUE,
        '#weight' => $fieldset_weight,
        '#group' => 'tab_entities_display',
      ];
      $fieldset_weight++;

      // Get available view modes for this entity type
      $ds_vm = $this->entityDisplayRepository->getViewModes($entity_type_key);
      $options = ['' => t('Default')];
      foreach ($ds_vm as $key => $item) {
        $options[$key] = $item['label'];
      }

      // Get available bundles for this entity type
      $bundle_options = [];
      foreach ($this->entityTypeBundleInfo->getBundleInfo($entity_type_key) as $bundle_key => $bundle) {
        $bundle_options[$bundle_key] = $bundle['label'];
      }

      // Create view mode select list
      $form['entity_bundles_per_type'][$entity_type_key]['view_mode'] = [
        '#type' => 'select',
        '#title' => t('View mode that will be used for the comparison'),
        '#options' => $options,
        '#default_value' => $selected_entity_types[$entity_type_key]['view_mode'] ?? '',
        '#weight' => 0,
        '#access' => !empty($bundle_options),
      ];

      $bundle_description = $this->t('Leave empty to remove this entity type from the list of available entity types.');

      if (empty($bundle_options)) {
        $bundle_description = $this->t('This entity type requires at least one entity bundle in order to create entities that can be made available for comparison. Please <a href=":entity_collection" target="_blank">add a new bundle</a> for this entity type.', [':entity_collection' => Url::fromRoute("entity.{$entity_type_key}.collection")->toString()]);
      }

      // Create bundle checkboxes
      $form['entity_bundles_per_type'][$entity_type_key]['bundles'] = [
        '#type' => 'checkboxes',
        '#options' => $bundle_options,
        '#title' => t('Choose the bundles that will be available for selection in the Entity Compare paragraph'),
        '#default_value' => $selected_entity_types[$entity_type_key]['bundles'] ?? [],
        '#weight' => 10,
        '#description' => $bundle_description,
      ];

      $save_entity_types[] = $entity_type_key;
    }

    // Create select list to allow user add further entity types to the field set and choose from its bundles
    $form['additional_entity_types'] = [
      '#type' => 'select',
      '#title' => t('Add another entity type'),
      '#options' => $additional_entity_types,
      '#description' => t('Optional. Choose additional entity types that should be available in the Entity Compare paragraph.'),
      '#default_value' => '_none_',
      '#ajax' => [
        'callback' => '::ajax_add_entity_type',
        'wrapper' => 'entities-display',
      ],
    ];
    
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_bundles_per_type = $form_state->getValue('entity_bundles_per_type');
    $clear_entity_types = [];
    foreach ($entity_bundles_per_type as $entity_type => &$values) {
      // Remove bundles set to '0' (i.e. unselected)
      $values['bundles'] = array_filter($values['bundles']);

      // Remove array keys
      $values['bundles'] = array_values($values['bundles']);

      if (empty($values['bundles'])) {
        // Store name of entity types with no bundle to clear later
        $clear_entity_types[] = $entity_type;
      }
    }

    // Remove entity types that were left empty (no bundles selected)
    foreach ($clear_entity_types as $entity_type) {
      unset($entity_bundles_per_type[$entity_type]);
    }

    // Store processed form state values:
    $this->config('ln_c_entity_compare.settings')
      ->set('entity_bundles_per_type', $entity_bundles_per_type)
      ->save();
    
    parent::submitForm($form, $form_state);
  }

  public function ajax_add_entity_type($form, FormStateInterface $form_state) {
    return [$form];
  }
}
