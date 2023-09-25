<?php

namespace Drupal\dsu_security_node\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dsu_security\Form\SecuritySettingsForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NdeSecuritySettingsForm.
 */
class NodeSecuritySettingsForm extends SecuritySettingsForm {

  const CONFIG = 'dsu_security_node.settings';

  const FIELD_HTTP_METHOD_OVERRIDE = 'http_method_override';

  const FIELD_JQUERY_URL = 'jquery_url';

  const FIELD_JQUERY_DOWNLOAD = 'jquery_download';

  const FIELD_JQUERY_VERSION = 'jquery_version';

  const FOLDER = 'public://jquery/';

  /**
   * Filesystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $fileSystem) {
    parent::__construct($config_factory);
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('file_system'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(self::CONFIG);

    $form[self::FIELD_HTTP_METHOD_OVERRIDE] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('HTTP method override'),
      '#description'   => $this->t('Allow HTTP method override using headers (e.g. X-HTTP-Method-Override) or query parameter (e.g. _method).'),
      '#default_value' => $config->get(self::FIELD_HTTP_METHOD_OVERRIDE),
      '#required'      => FALSE,
    ];

    $form[self::FIELD_JQUERY_URL] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('jQuery URL'),
      '#description'   => $this->t('You can specify the URL where jQuery will be obtained from. If empty, fallback one will be used.'),
      '#default_value' => $config->get(self::FIELD_JQUERY_URL),
      '#required'      => FALSE,
    ];

    $form[self::FIELD_JQUERY_VERSION] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('jQuery version'),
      '#description'   => $this->t('Please specify the version of jQuery you are using on "jQuery URL" field above.'),
      '#default_value' => $config->get(self::FIELD_JQUERY_VERSION),
      '#required'      => FALSE,
    ];

    $downloaded_file = $config->get(self::FIELD_JQUERY_DOWNLOAD);
    if (!empty($downloaded_file) && file_exists($downloaded_file)) {
      $url = file_create_url($downloaded_file);

      $form[self::FIELD_JQUERY_URL]['#description'] = $this->t('You can specify the URL where Jquery will be obtained from. If empty, drupal core one will be used. Latest downloaded version: <a href=":link">here</a>', [
        ':link' => $url,
      ]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $url = $form_state->getValue(self::FIELD_JQUERY_URL);
    if (!empty($url) && !UrlHelper::isValid($url)) {
      $form_state->setErrorByName(self::FIELD_JQUERY_URL, $this->t('Please enter a valid url or leave it empty to disable jquery overwrite.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue(self::FIELD_JQUERY_URL);
    $version = $form_state->getValue(self::FIELD_JQUERY_VERSION);

    // If fields are ok, we refresh jquery.
    if (!empty($url) && !empty($version)) {
      $directory = self::FOLDER;
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
      $destination = self::FOLDER . 'jquery.min.js';
      $downloaded_file = system_retrieve_file($form_state->getValue(self::FIELD_JQUERY_URL), $destination, FALSE, FileSystemInterface::EXISTS_REPLACE);
      if (empty($downloaded_file)) {
        $downloaded_file = '';
      }
    }
    else {
      // If fields are empty, we remove jquery config to prevent load.
      $downloaded_file = '';
    }

    $method_override = $form_state->getValue(self::FIELD_HTTP_METHOD_OVERRIDE);

    $this->config(self::CONFIG)
      ->set(self::FIELD_HTTP_METHOD_OVERRIDE, $method_override)
      ->set(self::FIELD_JQUERY_URL, $url)
      ->set(self::FIELD_JQUERY_VERSION, $version)
      ->set(self::FIELD_JQUERY_DOWNLOAD, $downloaded_file)
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    $config = parent::getEditableConfigNames();
    $config[] = self::CONFIG;
    return $config;
  }

}
