<?php

namespace Drupal\lightnest\Installer\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the site configuration form.
 */
class IntegrationConfigureForm extends ConfigFormBase {


  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs a ModuleConfigureForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandler $module_handler) {
    $this->setConfigFactory($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightnest_integrations_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Please select the integrations that you would like to install'),
    ];
    $form['install_modules'] = [
      '#type' => 'container',
    ];
    // List of optional modules.
    $profile = $this->moduleHandler->getModule('lightnest');
    $module_file = Yaml::decode(file_get_contents( "{$profile->getPath()}/config/integrations.yml"));
    foreach ($module_file as $module) {
      $form['install_modules_' . $module['id']] = [
      '#type' => 'checkbox',
      '#title' => $module['name'],
      '#description' => $module['description'],
      '#default_value' => $module['default_checked'],
      '#disabled' => $module['disabled'],
      ];
    }
    $form['#title'] = $this->t('Install & configure modules');
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $installModules = [];
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'install_modules') !== false && $value) {
        preg_match('/install_modules_(?P<name>\w+)/', $key, $values);
        $installModules[] = $values['name'];
      }
    }
    $buildInfo = $form_state->getBuildInfo();
    $install_state = $buildInfo['args'];
    $install_state[0]['lightnest_additional_integration'] = $installModules;
    $install_state[0]['form_state_values'] = $form_state->getValues();
    $buildInfo['args'] = $install_state;
    $form_state->setBuildInfo($buildInfo);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }
}
