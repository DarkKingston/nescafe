<?php

namespace Drupal\ln_srh;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Each SRHProcess will extend this base.
 */
interface SRHProcessInterface extends ContainerFactoryPluginInterface {

  /**
   * @param ContentEntityInterface $entity
   * @param $srh_data
   * @param $field_name
   * @return mixed
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name);

  /**
   * The form that holds the settings for this plugin.
   *
   * @param array $form
   *   The form definition array for the field configuration form.
   * @param FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The renderable form array representing the entire configuration form.
   */
  public function settingsForm($form, FormStateInterface $form_state);

  /**
   * Returns the label of the plugin.
   *
   * @return string
   *   The configured plugin label.
   */
  public function label();

  /**
   * @param FieldDefinitionInterface $field_definition
   * @param array $plugin_definition
   * @return bool
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition);
}
