<?php

namespace Drupal\promo_code\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Introduceți codul promoțional configuration settings form.
 */
class PromoCodeSettingsForm extends ConfigFormBase {

  /**
   * The webform token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * The webform element plugin manager.
   *
   * @var \Drupal\webform\Plugin\WebformElementManagerInterface
   */
  protected $elementManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->tokenManager = $container->get('webform.token_manager');
    $instance->elementManager = $container->get('plugin.manager.webform.element');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'promo_code_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['promo_code.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('promo_code.settings');

    $form['introduceti_codul_promotional'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Введите промо-код'),
      '#required' => TRUE,
      '#required_error' => $this->t('Это поле обязательно к заполнению'),
      '#unique' => TRUE,
      '#unique_error' => $this->t('Этот код уже недействителен'),
      '#default_value' => $config->get('introduceti_codul_promotional'),
    ];
    $form['user_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('id пользователя'),
      '#default_value' => $config->get('user_id'),
      '#prepopulate' => TRUE,
      '#custom_data' => $this->t('[current-user:uid]'),
    ];
    $form['actions'] = [
      '#type' => 'actions',
      '#tree' => TRUE,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Сохранить конфигурацию',
      '#button_type' => 'primary',
    ];

    // Process elements.
    $this->elementManager->processElements($form);

    // Replace tokens.
    $form = $this->tokenManager->replace($form);

    // Attach the webform library.
    $form['#attached']['library'][] = 'webform/webform.form';

    // Autofocus: Save details open/close state.
    $form['#attributes']['class'][] = 'js-webform-autofocus';
    $form['#attached']['library'][] = 'webform/webform.form.auto_focus';

    // Unsaved: Warn users about unsaved changes.
    $form['#attributes']['class'][] = 'js-webform-unsaved';
    $form['#attached']['library'][] = 'webform/webform.form.unsaved';

    // Details save: Attach details element save open/close library.
    $form['#attached']['library'][] = 'webform/webform.element.details.save';

    // Details toggle: Display collapse/expand all details link.
    $form['#attributes']['class'][] = 'js-webform-details-toggle';
    $form['#attributes']['class'][] = 'webform-details-toggle';
    $form['#attached']['library'][] = 'webform/webform.element.details.toggle';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get all values.
    $values = $form_state->getValues();

    // Remove Form API values.
    unset(
      $values['form_build_id'],
      $values['form_token'],
      $values['form_id'],
      $values['op'],
      $values['actions']
    );

    // Save config.
    $this->config('promo_code.settings')
      ->setData($values)
      ->save();
    var_dump('123');
    // Display message.
    parent::submitForm($form, $form_state);
  }

}
