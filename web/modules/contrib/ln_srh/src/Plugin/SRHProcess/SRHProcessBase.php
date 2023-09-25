<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_srh\SRHProcessInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Each SRHProcess will extend this base.
 */
abstract class SRHProcessBase extends PluginBase implements SRHProcessInterface, ConfigurableInterface{

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  abstract public function process(ContentEntityInterface $entity, $srh_data, $field_name);

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $definition = $this->getPluginDefinition();
    return $definition['label'];
  }

  /**
   * Get the bundle for which the process is running.
   */
  public function getProcessBundle() {
    $config = $this->getConfiguration();
    return $config['srh_bundle'] ?? NULL;
  }

  /**
   * Process needs to use multilanguage content translations.
   *
   * @return false|mixed
   */
  public function isMultilanguage() {
    $config = $this->getConfiguration();
    return $config['srh_multilanguage'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    $field_name = $field_definition->getName();
    // This process is only available for field_name.
    return $plugin_definition['field_name'] == $field_name;
  }
}
