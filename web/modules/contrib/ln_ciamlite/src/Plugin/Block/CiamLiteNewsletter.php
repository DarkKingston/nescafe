<?php

namespace Drupal\ln_ciamlite\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ln_ciamlite\Services\CiamLiteHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Newsletter' block.
 *
 * @Block(
 *   id = "ln_ciamlite_newsletter",
 *   admin_label = @Translation("CiamLite Newsletter"),
 *   category = @Translation("Lightnest CiamLite")
 * )
 */
class CiamLiteNewsletter extends CiamLiteNewsletterScreen  implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'ln_ciamlite_gigya_newletter_block',
      '#title' => $this->label(),
      '#subtitle' => $this->configuration['subtitle'] ?? '',
      '#gigya_screen' => $this->ciamLiteHelper->buildGigyaNewsletterScreen($this->pluginId),
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaultConfig = parent::defaultConfiguration();
    $defaultConfig['subtitle'] = '';

    return $defaultConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#default_value' => $this->configuration['subtitle'],
      '#weight' => -1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form,$form_state);
    $this->configuration['subtitle'] = $form_state->getValue('subtitle');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label']['#access'] = TRUE;

    return $form;
  }

}
