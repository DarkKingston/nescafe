<?php

namespace Drupal\advanced_datalayer\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;

/**
 * Defines the configuration export form.
 */
class AdvancedDatalayerSettingsForm extends ConfigFormBase {

  /**
   * The advanced_datalayer.manager service.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface
   */
  protected $datalayerManager;

  /**
   * The entity_type.bundle.info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface $datalayer_manager
   *   Advance datalayer manager object.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity_type.bundle.info service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AdvancedDatalayerManagerInterface $datalayer_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    StateInterface $state
  ) {
    parent::__construct($config_factory);
    $this->datalayerManager = $datalayer_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('advanced_datalayer.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'datalayer_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['advanced_datalayer.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($this->state->get('system.maintenance_mode')) {
      $this->messenger()->addMessage($this->t('Please note that while the site is in maintenance mode none of the datalayer tags will be output.'));
    }

    $config = $this->config('advanced_datalayer.settings');
    $entity_types_settings = $config->get('entity_types');
    $settings = $config->get('entity_type_tags');

    $form['entity_types'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Entity types'),
      '#options' => AdvancedDatalayerDefaultsForm::getSupportedEntityTypes(),
      '#default_value' => $entity_types_settings ?? ['node', 'taxonomy_term'],
      '#description' => $this->t('Select entity types to configure availability of datalayer tags. If no entities are selected, all tags will appear.'),
    ];

    $form['entity_type_tags'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Entity type / Group Mapping'),
      '#description' => $this->t('Identify which datalayer tags should be available on which entity type / bundle combination. Unselected groups will not appear on the configuration form for that entity type, reducing the size of the form and increasing performance. If no tags are selected for a type, all tags will appear.'),
      '#tree' => TRUE,
    ];

    $datalayer_tags = $this->datalayerManager->sortedTags();
    $entity_types = AdvancedDatalayerDefaultsForm::getSupportedEntityTypes();
    foreach ($entity_types as $entity_type => $entity_label) {
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
      foreach ($bundles as $bundle_id => $bundle_info) {
        // Create an option list for each bundle.
        $options = [];
        foreach ($datalayer_tags as $tag_name => $tag_info) {
          if (!$tag_info['global']) {
            $options[$tag_name] = $tag_info['label'];
          }
        }
        // Format a collapsible fieldset for each group for easier readability.
        $form['entity_type_tags'][$entity_type][$bundle_id] = [
          '#type' => 'details',
          '#title' => $entity_label . ': ' . $bundle_info['label'],
          '#states' => [
            'visible' => [
              'select[name="entity_types[]"]' => [['value' => [$entity_type]]],
            ],
          ],
        ];
        $form['entity_type_tags'][$entity_type][$bundle_id][] = [
          '#type' => 'checkboxes',
          '#options' => $options,
          '#default_value' => $settings[$entity_type][$bundle_id] ?? [],
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->config('advanced_datalayer.settings');
    $types = $form_state->getValue('entity_types');
    $value = $form_state->getValue('entity_type_tags');
    $value = static::arrayFilterRecursive($value);
    $processed_values = [];
    // Remove the extra layer created by collapsible fieldsets.
    foreach ($value as $entity_type => $bundle) {
      if (in_array($entity_type, $types, TRUE)) {
        foreach ($bundle as $bundle_id => $tags) {
          $processed_values[$entity_type][$bundle_id] = $tags[0];
        }
      }
    }
    $settings->set('entity_types', $types);
    $settings->set('entity_type_tags', $processed_values);
    $settings->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Recursively filter results.
   *
   * @param array $input
   *   The array to filter.
   *
   * @return array
   *   The filtered array.
   */
  public static function arrayFilterRecursive(array $input) {
    foreach ($input as &$value) {
      if (is_array($value)) {
        $value = static::arrayFilterRecursive($value);
      }
    }
    return array_filter($input);
  }

}
