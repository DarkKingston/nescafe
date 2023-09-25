<?php

namespace Drupal\ln_ciamlite\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ln_ciamlite\Services\CiamLiteHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Newsletter gigya screen' block.
 *
 * @Block(
 *   id = "ln_ciamlite_newsletter_screen",
 *   admin_label = @Translation("CiamLite Newsletter Screen"),
 *   category = @Translation("Lightnest CiamLite")
 * )
 */
class CiamLiteNewsletterScreen extends BlockBase  implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\ln_ciamlite\Services\CiamLiteHelperInterface
   */
  protected $ciamLiteHelper;


  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param CiamLiteHelperInterface $ciamLiteHelper
   */
  public function __construct(array $configuration,
                                    $plugin_id,
                                    $plugin_definition,
                              CiamLiteHelperInterface $ciamLiteHelper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ciamLiteHelper = $ciamLiteHelper;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_ciamlite.helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->ciamLiteHelper->buildGigyaNewsletterScreen($this->pluginId);
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label']['#access'] = FALSE;
    $form['label_display']['#access'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form,$form_state);
    $this->configuration['label_display'] = FALSE;
  }

}
