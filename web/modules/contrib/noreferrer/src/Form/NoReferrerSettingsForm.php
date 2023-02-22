<?php

namespace Drupal\noreferrer\Form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a noreferrer Config form.
 */
class NoReferrerSettingsForm extends ConfigFormBase {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface|null
   */
  protected $fileUrlGenerator;

  /**
   * The private key service.
   *
   * @var \Drupal\Core\PrivateKey
   */
  protected $privateKey;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, PrivateKey $private_key, FileSystemInterface $file_system, ?FileUrlGeneratorInterface $file_url_generator = NULL) {
    parent::__construct($config_factory);
    $this->privateKey = $private_key;
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('private_key'),
      $container->get('file_system'),
      // @phpstan-ignore-next-line Core 9.2 compatibility shim.
      $container->has('file_url_generator') ? $container->get('file_url_generator') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'noreferrer_settings_form';
  }

  /**
   * {@inheritdoc}
   *
   * @return string[]
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['noreferrer.settings'];
  }

  /**
   * {@inheritdoc}
   *
   * @param mixed[] $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed[]
   *   The settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['noopener'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Add <code>rel="noopener"</code> if link has a target'),
      '#default_value' => $this->config('noreferrer.settings')->get('noopener'),
      '#description'   => $this->t('If checked, <code>rel="noopener"</code> will be added to links with a target attribute.'),
    ];
    $form['noreferrer'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Add <code>rel="noreferrer"</code> to non-allowed links'),
      '#default_value' => $this->config('noreferrer.settings')->get('noreferrer'),
      '#description'   => $this->t('If checked, <code>rel="noreferrer"</code> will be added to non-allowed external links.'),
    ];
    $form['allowed_domains'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Allowed domains'),
      '#default_value' => $this->config('noreferrer.settings')->get('allowed_domains'),
      '#description'   => $this->t('Enter a space-separated list of domains to which referrer URLs will be sent (e.g. <em>example.com example.org</em>). Links to all other domains will have <code>rel="noreferrer"</code> added.'),
      '#maxlength'     => NULL,
    ];
    $form['publish'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Publish list of allowed domains'),
      '#default_value' => $this->config('noreferrer.settings')->get('publish'),
      '#description'   => $this->t('If checked, the list of allowed domains will be published at <a href="@url">@url</a> when saving this form.', [
        // @phpstan-ignore-next-line Core 9.2 compatibility shim.
        '@url' => $this->fileUrlGenerator ? $this->fileUrlGenerator->generateAbsoluteString($this->publishUri()) : file_create_url($this->publishUri()),
      ]),
    ];
    $form['subscribe_url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('Subscribe to external list of allowed domains'),
      '#default_value' => $this->config('noreferrer.settings')->get('subscribe_url'),
      '#description'   => $this->t('If configured, the list of allowed domains will be retrieved from the given URL during each cron run.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @param mixed[] $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('noreferrer.settings')
      ->set('noopener', $form_state->getValue('noopener'))
      ->set('noreferrer', $form_state->getValue('noreferrer'))
      ->set('publish', $form_state->getValue('publish'))
      ->set('subscribe_url', $form_state->getValue('subscribe_url'))
      ->set('allowed_domains', $form_state->getValue('allowed_domains'))
      ->save();
    if ($form_state->getValue('publish')) {
      $this->publish();
    }
    if ($url = $form_state->getValue('subscribe_url')) {
      noreferrer_subscribe($url);
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * Publishes domain allowlist.
   */
  public function publish(): void {
    if ($allowed_domains = $this->config('noreferrer.settings')->get('allowed_domains')) {
      $allowed_domains = json_encode(explode(' ', $allowed_domains));
      $this->fileSystem->saveData($allowed_domains, $this->publishUri(), FileSystemInterface::EXISTS_REPLACE);
    }
  }

  /**
   * Returns domain allowlist URI.
   */
  public function publishUri(): string {
    // For security through obscurity purposes, the allowlist URL is secret.
    return 'public://noreferrer-allowlist-' . Crypt::hmacBase64('noreferrer-allowlist', $this->privateKey->get()) . '.json';
  }

}
