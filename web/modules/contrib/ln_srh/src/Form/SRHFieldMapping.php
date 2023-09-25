<?php

namespace Drupal\ln_srh\Form;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHProcessInterface;
use Drupal\ln_srh\SRHProcessManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SRHFieldMapping extends ConfigFormBase{
  use StringTranslationTrait;

  /** @var string Config settings */
  const SETTINGS = 'ln_srh.mapping';

  /**
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * @var string
   *   Node type bundle.
   */
  protected $bundle;

  /**
   * Constructs a \Drupal\system\SRHFieldMapping object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityFieldManagerInterface $entityFieldManager, SRHProcessManager $srh_process_manager) {
    parent::__construct($config_factory);
    $this->entityFieldManager = $entityFieldManager;
    $this->srhProcessManager = $srh_process_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.srh_process')
    );
  }

  /**
   * @return string[]
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_srh_recipe_mapping';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bundle = NULL) {
    if (!$bundle) {
      $bundle = SRHConstants::SRH_RECIPE_BUNDLE;
    }
    $this->bundle = $bundle;
    $config = $this->config(static::SETTINGS);
    $recipe_fields = $this->entityFieldManager->getFieldDefinitions('node', $bundle);
    foreach ($recipe_fields as $field) {
      $configPreffix = $bundle . '.' . $field->getName();
      $plugin_id = $config->get("{$configPreffix}.plugin.id");
      $plugin_configuration = $config->get("{$configPreffix}.plugin.settings") ?? [];
      $plugins_options = $this->getPluginsOptions($configPreffix, $field);
      $form[$field->getName()] = [
        '#type' => 'details',
        '#title' => $field->getLabel(),
        '#open' => FALSE,
        '#tree' => TRUE,
        'enable_mapping' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable field mapping'),
          '#disabled' => empty($plugins_options),
          '#default_value' => !empty($plugins_options) ? $config->get("{$configPreffix}.enable_mapping") : FALSE,
        ],
        'plugin' => [
          '#type' => 'container',
          '#tree' => TRUE,
          '#states' => [
            'visible' => [
              ':input[name="'. $field->getName() .'[enable_mapping]"]' => ['checked' => TRUE]
            ]
          ],
          'id' => [
            '#type' => 'select',
            '#title' => t('Plugin Process'),
            '#options' => $plugins_options,
            '#default_value' => $plugin_id,
            '#field_name' => $field->getName(),
            '#states' => [
              'required' => [
                ':input[name="'. $field->getName() .'[enable_mapping]"]' => ['checked' => TRUE]
              ]
            ],
            '#ajax' => [
              'callback' => '::getPluginSettingsForm',
              'wrapper' => "plugin-settings-{$field->getName()}",
            ],
          ],
          'settings' => [
            '#type' => 'container',
            '#tree' => TRUE,
            '#attributes' => ['id' => "plugin-settings-{$field->getName()}"],
          ]
        ],
      ];
      if (!empty($form_state->getValue([$field->getName(), 'plugin', 'id']))) {
        $plugin_id = $form_state->getValue([$field->getName(), 'plugin', 'id']);
        $plugin_configuration = [];
      }
      if ($plugin = $this->getPluginInstance($plugin_id, $plugin_configuration)) {
        $form[$field->getName()]['plugin']['settings'] += $plugin->settingsForm($form, $form_state);
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $this->bundle);
    foreach ($fields as $field) {
      $config->set($this->bundle . '.' . $field->getName(), $form_state->getValue($field->getName()));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * @param $field_name
   * @return array
   */
  private function getPluginsOptions($field_name,FieldDefinitionInterface $field) {
    $plugins = $this->srhProcessManager->getDefinitions();
    $plugins = array_filter($plugins, function ($plugin_definition) use ($field_name,$field) {
      /** @var SRHProcessInterface $pluginClass */
      $pluginClass = DefaultFactory::getPluginClass($plugin_definition['id'],$plugin_definition);
      return $pluginClass::isApplicable($field,$plugin_definition);
    });
    $options = array_column($plugins,'label','id');

    return $options;
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return mixed
   */
  public function getPluginSettingsForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $field_name = $triggering_element['#field_name'];
    return $form[$field_name]['plugin']['settings'];
  }

  /**
   * @param $plugin_id
   * @param $configuration
   * @return false|object
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getPluginInstance($plugin_id, $configuration) {
    if($this->srhProcessManager->hasDefinition($plugin_id)){
      return $this->srhProcessManager->createInstance($plugin_id,$configuration);
    }
    return FALSE;
  }
}
