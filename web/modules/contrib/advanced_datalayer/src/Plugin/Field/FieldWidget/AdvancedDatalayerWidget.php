<?php

namespace Drupal\advanced_datalayer\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerTagPluginManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Advanced widget for datalayer field.
 *
 * @FieldWidget(
 *   id = "advanced_datalayer_widget",
 *   label = @Translation("Advanced datalayer tags form"),
 *   field_types = {
 *     "advanced_datalayer"
 *   }
 * )
 */
class AdvancedDatalayerWidget extends WidgetBase {

  /**
   * Instance of dvancedDatalayerManager service.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface
   */
  protected $datalayerManager;

  /**
   * Instance of AdvancedDatalayerTagPluginManager service.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerTagPluginManager
   */
  protected $datalayerPluginManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    AdvancedDatalayerManagerInterface $manager,
    AdvancedDatalayerTagPluginManager $plugin_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->datalayerManager = $manager;
    $this->datalayerPluginManager = $plugin_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('advanced_datalayer.manager'),
      $container->get('plugin.manager.advanced_datalayer.tag'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'sidebar' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['sidebar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Place field in sidebar'),
      '#default_value' => $this->getSetting('sidebar'),
      '#description' => $this->t('If checked, the field will be placed in the sidebar on entity forms.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    if ($this->getSetting('sidebar')) {
      $summary[] = $this->t('Use sidebar: Yes');
    }
    else {
      $summary[] = $this->t('Use sidebar: No');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $item = $items[$delta];
    $default_tags = advanced_datalayer_get_default_tags($items->getEntity());

    // Retrieve the values for each datalayer tag from the serialized array.
    $values = [];
    if (!empty($item->value)) {
      $values = unserialize($item->value);
    }

    // Populate fields which have not been overridden in the entity.
    if (!empty($default_tags)) {
      foreach ($default_tags as $tag_id => $tag_value) {
        if (!isset($values[$tag_id]) && !empty($tag_value)) {
          $values[$tag_id] = $tag_value;
        }
      }
    }

    // Retrieve configuration settings.
    $settings = $this->configFactory->get('advanced_datalayer.settings');
    $entity_type_tags = $settings->get('entity_type_tags');

    // Find the current entity type and bundle.
    $entity_type = $item->getEntity()->getentityTypeId();
    $entity_bundle = $item->getEntity()->bundle();

    // See if there are requested groups for this entity type and bundle.
    $tags = [];
    if (!empty($entity_type_tags[$entity_type]) && !empty($entity_type_tags[$entity_type][$entity_bundle])) {
      $tags = $entity_type_tags[$entity_type][$entity_bundle];
    }

    // Limit the form to requested groups, if any.
    if (!empty($tags)) {
      $element = $this->datalayerManager->form($values, $element, TRUE, [$entity_type], $tags);
    }
    else {
      $element = $this->datalayerManager->form($values, $element, TRUE, [$entity_type]);
    }

    // If the "sidebar" option was checked on the field widget, put the
    // form element into the form's "advanced" group. Otherwise, let it
    // default to the main field area.
    $sidebar = $this->getSetting('sidebar');
    if ($sidebar) {
      $element['#group'] = 'advanced';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Flatten the values array to remove the groups and then serialize all the
    // tags into one value for storage.
    $tag_manager = $this->datalayerPluginManager;
    foreach ($values as &$value) {
      $flattened_value = [];
      foreach ($value as $group) {
        // Exclude the "original delta" value.
        if (is_array($group)) {
          foreach ($group as $tag_id => $tag_value) {
            $tag = $tag_manager->createInstance($tag_id);
            $tag->setValue($tag_value);
            if (!empty($tag->value())) {
              $flattened_value[$tag_id] = $tag->value();
            }
          }
        }
      }
      $value = serialize($flattened_value);
    }

    return $values;
  }

}
