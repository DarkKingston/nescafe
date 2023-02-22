<?php

namespace Drupal\ln_pdh\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\ln_pdh\PdhConnectorInterface;
use Drupal\ln_pdh\PdhImporterInterface;
use Drupal\paragraphs\Entity\ParagraphsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PdhMappingForm. Config for the Pdh module.
 *
 * @package Drupal\ln_pdh\Form
 */
class PdhMappingForm extends ConfigFormBase {

  const CONFIG_FIELD_SOURCE = 'source';
  const CONFIG_FIELD_DESTINATION = 'destination';
  const CONFIG_FIELD_ENABLED = 'enabled';
  const NEW_DESTINATION_FIELD = 'new_destination';
  const COLUMN_STATUS = 'status';
  const SELECT_VALUE_NONE = 'none';
  const FORM_VALUE_MAP = 'map';
  const PRODUCT_BUNDLE = 'dsu_product';

  /**
   * Select target field.
   *
   * @var bool
   */
  protected $destinationField = FALSE;

  /**
   * The PDH connector.
   *
   * @var \Drupal\ln_pdh\PdhConnectorInterface
   */
  protected $connector;

  /**
   * The PDH connector.
   *
   * @var \Drupal\ln_pdh\PdhImporterInterface
   */
  protected $importer;

  /**
   * The PDH connector.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Renderer definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * PdhSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\ln_pdh\PdhConnectorInterface $pdh_connector
   *   The PDH connector service.
   * @param \Drupal\ln_pdh\PdhImporterInterface $pdh_importer
   *   The PDH importer service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PdhConnectorInterface $pdh_connector, PdhImporterInterface $pdh_importer, EntityFieldManagerInterface $entity_field_manager, LoggerChannelFactoryInterface $logger_factory, RendererInterface $renderer) {
    parent::__construct($config_factory);
    $this->connector = $pdh_connector;
    $this->importer = $pdh_importer;
    $this->entityFieldManager = $entity_field_manager;
    $this->loggerFactory = $logger_factory->get('ln_pdh');
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('ln_pdh.connector'),
      $container->get('ln_pdh.importer'),
      $container->get('entity_field.manager'),
      $container->get('logger.factory'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_pdh_mapping_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_pdh.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->checkConnection();

    $config = $this->config('ln_pdh.settings');
    $saved_mapping = $config->get('map');

    // Get a list of existing fields.
    $dsu_product_info = $this->entityFieldManager->getFieldDefinitions('node', self::PRODUCT_BUNDLE);
    $default_target_fields = [self::SELECT_VALUE_NONE => ' - ' . $this->t('Do not change') . ' - '];
    foreach ($dsu_product_info as $field_name => $field_info) {
      if (strpos($field_name, 'field_') === 0) {
        $default_target_fields[$field_name] = $field_name . ' (' . $field_info->getLabel() . ')';
      }
    }

    $form['#tree'] = TRUE;

    // Table header.
    $header = [
      self::CONFIG_FIELD_SOURCE => [
        'data' => 'PDH source field',
      ],
      self::CONFIG_FIELD_DESTINATION => [
        'data' => 'Destination field',
      ],
      self::COLUMN_STATUS => [
        'data' => 'Status',
      ],
    ];
    if ($this->destinationField) {
      $header[self::NEW_DESTINATION_FIELD] = [
        'data' => 'Change destination field',
      ];
    }

    // Table rows, load fields, no need for path language here.
    $options = [];
    $pdh_fields = $this->importer->getOptionalFieldMapping('xx_XX');
    $deleted_fields = [];
    foreach ($pdh_fields as $machine_name => $field_path_mapping) {
      // Set default destination field option.
      $row_target_fields = [];
      $row_target_fields[$machine_name] = $machine_name;
      $row_target_fields += $default_target_fields;

      // Prepare source label avoiding machine name if possible.
      $source_field = isset($pdh_fields[$machine_name]['label']) ? $pdh_fields[$machine_name]['label'] : $machine_name;
      if (!empty($pdh_fields[$machine_name]['id'])) {
        $source_field = $pdh_fields[$machine_name]['id'] . ' - ' . $source_field;
      }
      if (!empty($field_path_mapping['paragraph']) && $field_path_mapping['paragraph']) {
        $source_field .= ' (Paragraph)';
      }

      // Prepare destination field and status columns.
      $destination_field = !empty($saved_mapping[$machine_name][self::CONFIG_FIELD_DESTINATION]) ? $saved_mapping[$machine_name][self::CONFIG_FIELD_DESTINATION] : $machine_name;
      $status_message = [];
      $status_message[] = isset($default_target_fields[$destination_field]) ? $default_target_fields[$destination_field] : $destination_field;

      // Prepare status label.
      $status_label = '<b>' . $this->t('New') . '</b>';
      if (!empty($dsu_product_info[$destination_field])) {
        $status_label = $this->t('Exists');
      }
      elseif (empty($dsu_product_info[$destination_field]) && !empty($saved_mapping[$machine_name][self::CONFIG_FIELD_ENABLED])) {
        $status_label = '<b>' . $this->t('Deleted') . '</b>';
        $deleted_fields[] = $field_path_mapping['label'] . ' (' . $destination_field . ')';
      }
      $status_message = array_filter($status_message);

      // Show table data.
      $options += [
        $machine_name => [
          self::CONFIG_FIELD_SOURCE => [
            'data' => [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#value' => $source_field,
            ],
          ],
          self::CONFIG_FIELD_DESTINATION => [
            'data' => [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#value' => implode(' ', $status_message),
            ],
          ],
          self::COLUMN_STATUS => [
            'data' => [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#value' => $status_label,
            ],
          ],
        ],
      ];

      if ($this->destinationField) {
        $options[$machine_name][self::NEW_DESTINATION_FIELD] = [
          'data' => [
            '#type' => 'select',
            '#title' => 'Change destination field',
            '#title_display' => 'invisible',
            '#options' => $row_target_fields,
            '#name' => self::NEW_DESTINATION_FIELD . '[' . $machine_name . ']',
            '#value' => self::SELECT_VALUE_NONE,
          ],
        ];
      }
    }

    // Prepare tableselect default values and show.
    $enabled_checkboxes = [];
    foreach ($saved_mapping as $key => $saved_values) {
      $enabled_checkboxes[$key] = !empty($saved_values[self::CONFIG_FIELD_ENABLED]) ? $key : "0";
    }
    $form[self::FORM_VALUE_MAP] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No field is available for import.'),
      '#default_value' => $enabled_checkboxes,
    ];

    // Notify about deleted fields.
    if (!empty($deleted_fields)) {
      $item_list = [
        '#items' => $deleted_fields,
        '#theme' => 'item_list',
      ];
      $render_list = $this->renderer->render($item_list);
      $this->messenger()->addWarning($this->t('One or more fields have been deleted while their synchronization is still enabled. Please either disable them or re-create them by submitting this form: <br/>@fields', [
        '@fields' => $render_list,
      ]));
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ln_pdh.settings');

    $fields = $form_state->getValue([self::FORM_VALUE_MAP]);
    $user_input = $form_state->getUserInput();

    // Ensure config uses keyed array mode.
    $result = [];
    $config_map = $config->get('map');
    foreach ($config_map as $loaded_config) {
      if (!empty($loaded_config[self::CONFIG_FIELD_SOURCE])) {
        $result[$loaded_config[self::CONFIG_FIELD_SOURCE]] = $loaded_config;
      }
    }

    // Extract form_state checkboxes and widgets within from input.
    foreach ($fields as $field_name => $checkbox) {
      if (empty($result[$field_name])) {
        $result[$field_name] = [];
      }
      $result[$field_name][self::CONFIG_FIELD_ENABLED] = $field_name === $checkbox;
      $result[$field_name][self::CONFIG_FIELD_SOURCE] = $field_name;
      if (!isset($result[$field_name][self::CONFIG_FIELD_DESTINATION])) {
        $result[$field_name][self::CONFIG_FIELD_DESTINATION] = $field_name;
      }

      // Change target field if select widget was used.
      $new_field = $user_input[self::NEW_DESTINATION_FIELD][$field_name] ?? self::SELECT_VALUE_NONE;
      if (!empty($new_field) && $new_field !== self::SELECT_VALUE_NONE) {
        $result[$field_name][self::CONFIG_FIELD_DESTINATION] = $new_field;
      }
    }
    $config->set('map', $result);
    $config->save();

    // Create missing fields.
    $pdh_fields = $this->importer->getOptionalFieldMapping('xx_XX');
    foreach ($result as $field_name => $field_info) {
      if (!empty($field_info[self::CONFIG_FIELD_ENABLED])) {
        $length = $pdh_fields[$field_name]['length'] ?? 255;
        $label = $pdh_fields[$field_name]['label'] ?? '';
        $cardinality = $pdh_fields[$field_name]['cardinality'] ?? 1;
        if (isset($pdh_fields[$field_name]['paragraph']) && $pdh_fields[$field_name]['paragraph'] === TRUE) {
          $this->createSelectedParagraph($field_name, $pdh_fields[$field_name]['label'], 'node');
          $this->createSelectedParagraphField($field_name, 'node', SELF::PRODUCT_BUNDLE, $length, $label, $cardinality);
        }
        else {
          $this->createSelectedField($field_name, 'node', SELF::PRODUCT_BUNDLE, $length, $label, $cardinality);
        }
      }
    }

    // Link to form display (as close as possible) and warn user.
    $url_object = Url::fromRoute('entity.node_type.edit_form', ['node_type' => 'dsu_product'], []);
    $link = Link::fromTextAndUrl($this->t('link'), $url_object)->toString();
    $this->messenger()->addWarning($this->t("Please remember to update the form visibility for dsu_product in 'Manage form display' tab via this @link. New fields will be disabled by default. If needed, you will also need to update the visibility for the new paragraph types.", [
      '@link' => $link,
    ]));

    parent::submitForm($form, $form_state);
  }

  /**
   * Create field config and storage.
   *
   * @param string $field_name
   *   Field machine name.
   * @param string $entity_type
   *   The entity type: paragraph or bundle.
   * @param string $bundle
   *   The bundle: product or the paragraph type.
   * @param int $length
   *   Length of the text field.
   * @param string $label
   *   Label of the field from PDH service.
   * @param int $cardinality
   *   Cardinality of the field.
   */
  protected function createSelectedField($field_name, $entity_type, $bundle, $length, $label, $cardinality = 1) {
    // Make sure the field storage doesn't already exist.
    $field = FieldStorageConfig::loadByName($entity_type, $field_name);
    if (empty($field)) {
      // Create the field.
      try {
        $field = FieldStorageConfig::create([
          'entity_type' => $entity_type,
          'field_name' => $field_name,
          'cardinality' => $cardinality,
          'type' => 'string',
          'settings' => [
            'max_length' => $length,
          ],
        ]);
        $field->save();
      } catch (\Exception $e) {
        $this->loggerFactory->error('An error occurred while creating field storage for @field_name (@entity_type): @e', [
          '@e' => $e->getMessage(),
          '@field_name' => $field_name,
          'entity_type' => $entity_type,
        ]);
      }
    }

    // Make sure the field config doesn't already exist.
    $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
    if (empty($field_config)) {
      try {
        $field_config = FieldConfig::create([
          'field_name' => $field_name,
          'label' => $label,
          'entity_type' => $entity_type,
          'bundle' => $bundle,
          'required' => FALSE,
          'settings' => [],
        ]);
        $field_config->save();
      }
      catch (\Exception $e) {
        $this->loggerFactory->error('An error occurred while creating new field @field_name for @entity_type:@bundle : @e', [
          '@e' => $e->getMessage(),
          '@field_name' => $field_name,
          '@entity_type' => $entity_type,
          '@bundle' => @bundle,
        ]);
      }
    }
  }

  /**
   * Creates a paragraph and its fields.
   *
   * @param string $field_name
   *   The field name.
   * @param string $label
   *   The label of the field and paragraph.
   * @param string $entity_type
   *   The entity type to attach the field to.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createSelectedParagraph(string $field_name, string $label, string $entity_type) {
    $paragraph_info = $this->importer->getParagraphDefinition($field_name, '');

    if (!ParagraphsType::load($paragraph_info['#paragraph_type'])) {
      $pt = ParagraphsType::create([
        'label' => $label,
        'id' => $paragraph_info['#paragraph_type'],
      ]);
      $pt->save();
    }

    foreach (Element::children($paragraph_info) as $paragraph_field) {
      $field = $paragraph_info[$paragraph_field];
      $length = $field['length'] ?? 255;
      $label = $field['label'] ?? '';
      $cardinality = $field['cardinality'] ?? 1;

      if (isset($field['#paragraph']) && $field['#paragraph'] === TRUE) {
        $this->createSelectedParagraph($paragraph_field, $field['label'], 'paragraph');
        $this->createSelectedParagraphField($paragraph_field, 'paragraph', $paragraph_info['#paragraph_type'], $length, $label, $cardinality);
      }
      else {
        $this->createSelectedField($paragraph_field, 'paragraph', $paragraph_info['#paragraph_type'], $length, $label, $cardinality);
      }
    }

    // Configure the form display.
    $form_display = \Drupal::service('entity_display.repository')->getFormDisplay('paragraph', $paragraph_info['#paragraph_type']);
    foreach (Element::children($paragraph_info) as $paragraph_field) {
      if (isset($field['#paragraph']) && $field['#paragraph'] === TRUE) {
        $widget_type = 'paragraphs';
      }
      else {
        $widget_type = 'string_textfield';
      }
      $form_display = $form_display->setComponent($paragraph_field, ['type' => $widget_type]);
    }

    $form_display->save();
  }

  /**
   * Checks connection to PDH status and shows a message to inform.
   */
  protected function checkConnection() {
    if ($this->getRequest()->getMethod() == 'GET') {
      if ($this->connector->testConnection()) {
        $this->messenger()->addStatus($this->t('Connection to PDH successful.'));
      }
      else {
        $this->messenger()->addError($this->t('Unable to connect to PDH. Please check your credentials.'));
      }
    }
  }

  /**
   * Create field config and storage for a paragraph.
   *
   * @param string $field_name
   *   Field machine name.
   * @param string $entity_type
   *   The entity type: paragraph or bundle.
   * @param string $bundle
   *   The bundle: product or the paragraph type.
   * @param int $length
   *   Length of the text field.
   * @param string $label
   *   Label of the field from PDH service.
   * @param int $cardinality
   *   Cardinality of the field.
   */
  protected function createSelectedParagraphField(string $field_name, string $entity_type, string $bundle, int $length, string $label, int $cardinality = 1): void {
    // Make sure the field doesn't already exist.
    $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);
    if (empty($field_storage)) {
      // Create the field.
      try {
        // Add a paragraph field to the entity type.
        $field_storage = FieldStorageConfig::create([
          'field_name' => $field_name,
          'entity_type' => $entity_type,
          'type' => 'entity_reference_revisions',
          'cardinality' => $cardinality,
          'settings' => [
            'target_type' => 'paragraph',
            'max_length' => $length,
          ],
        ]);
        $field_storage->save();
      } catch (\Exception $e) {
        $this->loggerFactory->error('An error occurred while creating new field @field_name: @e', [
          '@e' => $e->getMessage(),
          '@field_name' => $field_name,
        ]);
      }
    }

    $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
    if (empty($field_config)) {
      try {
        $field_config = FieldConfig::create([
          'field_storage' => $field_storage,
          'bundle' => $bundle,
          'field_name' => $field_name,
          'label' => $label,
          'required' => FALSE,
          'settings' => [],
        ]);
        $field_config->save();
      } catch (\Exception $e) {
        $this->loggerFactory->error('An error occurred while creating new field @field_name: @e', [
          '@e' => $e->getMessage(),
          '@field_name' => $field_name,
        ]);
      }
    }
  }

}
