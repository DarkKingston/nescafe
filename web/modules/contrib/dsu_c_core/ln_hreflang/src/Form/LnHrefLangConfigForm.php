<?php

namespace Drupal\ln_hreflang\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides settings for all pages.
 */
class LnHrefLangConfigForm extends FormBase {

  /**
   * The Drupal state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The cache tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $invalidator;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Constructs an HrefLangConfigForm object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $invalidator
   *   The cache invalidator service.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager service.
   */
  public function __construct(StateInterface $state, CacheTagsInvalidatorInterface $invalidator, LanguageManager $language_manager) {
    $this->state = $state;
    $this->invalidator = $invalidator;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('cache_tags.invalidator'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_hreflang_configurtaion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add hreflang textfields for all languages.
    foreach ($this->languageManager->getLanguages() as $langKey => $langObject) {
      $hreflang = $this->state->get('ln_hreflang.hreflang.' . $langKey);
      $form['language_' . $langKey] = [
        '#type' => 'textfield',
        '#title' => $this->t('Hreflang for :name', [':name' => $langObject->getName()]),
        '#default_value' => $hreflang,
        '#description' => $this->t('Please follow the general pattern en-us or en for hreflang'),
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    foreach ($this->languageManager->getLanguages() as $langKey => $langObject) {
      $this->state->set('ln_hreflang.hreflang.' . $langKey, $form_state->getValue('language_' . $langKey));
      $this->invalidator->invalidateTags(['state:ln_hreflang.hreflang' . $langKey]);
    }
    $this->messenger()
        ->addMessage($this->t('Your configuration have been saved.'));
  }

}
