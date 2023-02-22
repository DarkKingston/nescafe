<?php

namespace Drupal\dsu_security_user_profile\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\Role;

/**
 * Defines a configuration form for UserProfile.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dsu_security_user_profile_settings_form';

  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['dsu_security_user_profile.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dsu_security_user_profile.settings');
    $options = [];
    $roles = Role::loadMultiple();
    foreach ($roles as $role) {
      $options[$role->id()] = $role->label();
    }
    unset($options[AccountInterface::ANONYMOUS_ROLE]);
    unset($options['administrator']);
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Update user edit URL'),
      '#description' => $this->t('Select Roles to which redirect on new URL for profile update.'),
      '#options' => $options,
      '#default_value' => $config->get('roles') ?? [],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('dsu_security_user_profile.settings')
      ->set('roles', array_filter($form_state->getValue('roles')))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
