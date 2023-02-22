<?php

namespace Drupal\ln_pdh;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Utility\Error;
use GuzzleHttp\Client;

/**
 * Class PdhConnector. Connects to the service and perform queries.
 *
 * @package Drupal\ln_pdh
 */
class PdhConnector implements PdhConnectorInterface {

  use LoggerChannelTrait;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The PDH config settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The http client service.
   *
   * @var \Guzzle\Http\Client
   */
  protected $httpClient;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * PdhConnector constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The http client service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ConfigFactory $config_factory, Client $http_client, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('ln_pdh.settings');
    $this->httpClient = $http_client;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function testConnection() {
    $endpoint = 'v1/GetService/getMyProducts/search';

    $request = $this->request($endpoint);

    if ($request === FALSE) {
      return FALSE;
    }

    return is_object($request) && $request->getStatusCode() == '200';
  }

  /**
   * {@inheritdoc}
   */
  public function getProducts(\DateTime $since_date = NULL, $gtin = '', $label_version = '') {
    $query = [];
    if ($since_date) {
      $query['updated_since_date'] = $since_date->format('Y-m-d\TH:i:s');
    }
    if (!empty($gtin)) {
      $query['gtin'] = $gtin;
    }
    if (!empty($label_version)) {
      $query['label_version'] = $label_version;
    }
    $brand_id = $this->config->get('auth.brand_id');
    if ($brand_id) {
      $query['brand_level_1'] = $brand_id;
    }

    $endpoint = 'v1/GetService/getMyProducts/search';

    $request = $this->request($endpoint, $query);

    if ($request === FALSE || $request->getStatusCode() != '200') {
      return FALSE;
    }

    $contents = new \SimpleXMLElement($request->getBody()->getContents());

    if (isset($contents->products)) {
      return $contents->xpath('//products//product');
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductInfo($gtin, $label_version, \DateTime $since_date = NULL) {
    $this->getLogger('pdh_connector')->debug(t('Importing product @gtin with label_version: @label', [
      '@gtin' => (string) $gtin,
      '@label' => (string) $label_version,
    ]));

    $query = [
      'gtin' => (string) $gtin,
      'label_version' => (string) $label_version,
    ];
    if ($since_date) {
      $query['updated_since_date'] = $since_date->format('Y-m-d\TH:i:s');
    }
    $endpoint = 'v1/GetService/getMyProductInfo/search';

    $request = $this->request($endpoint, $query);

    if ($request === FALSE || $request->getStatusCode() != '200') {
      return FALSE;
    }

    // Extract contents from XML.
    try {
      $contents = new \SimpleXMLElement($request->getBody()->getContents());
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($e);
      $this->getLogger('pdh_connector')->log(isset($variables['severity']) ? $variables['severity'] : 'error', '%type: @message in %function (line %line of %file). <br /> @backtrace_string', $variables);
      return FALSE;
    }

    if (isset($contents->Product)) {
      if (isset($contents->Product->GTIN)) {
        $this->getLogger('pdh_connector')->debug(t('Received gtin: @gtin', [
          '@gtin' => $contents->Product->GTIN,
        ]));
      }
      return $contents->Product;
    }
    elseif (isset($contents->Drupal)) {
      return $contents->Drupal;
    }

    return FALSE;
  }

  /**
   * Performs a basic request against PDH API.
   *
   * @param string $endpoint
   *   The API endpoint URL.
   * @param array $query
   *   The query parameters to pass.
   *
   * @return \Guzzle\Http\Message\RequestInterface|\Psr\Http\Message\ResponseInterface|false
   *   The result of the operation. FALSE if anything fails
   */
  protected function request($endpoint, array $query = []) {
    // Avoid multiples / characters, ensuring at least one.
    $url = $this->config->get('auth.endpoint_url');
    if (substr($url, -1) !== '/') {
      $url .= '/';
    }
    if ($endpoint[0] === '/') {
      $endpoint = substr($endpoint, 1);
    }
    $endpoint = $url . $endpoint;

    $cert_path = $this->config->get('auth.certificate_path');
    $cert_filename = basename($cert_path);
    $cert = DRUPAL_ROOT . '/' . $this->config->get('auth.certificate_path') . DIRECTORY_SEPARATOR . $cert_filename . '.crt';
    $key = DRUPAL_ROOT . '/' . $this->config->get('auth.certificate_path') . DIRECTORY_SEPARATOR . $cert_filename . '.key';
    if (!file_exists($cert) || !file_exists($key)) {
      $this->getLogger('pdh_connector')->error('Certificates can\'t be found on the system. Please check configuration');
      return FALSE;
    }

    $query += [
      'client' => $this->config->get('auth.client'),
    ];

    $country = $this->config->get('auth.country');
    if (!empty($country)) {
      $query['country'] = $country;
    }

    // Allow to change the request by 3rd party modules.
    $this->moduleHandler->alter('ln_pdh_request', $endpoint, $query);

    $passphrase = $this->config->get('auth.passphrase');
    $passphrase = empty($passphrase) ? '' : $passphrase;

    try {
      $request = $this->httpClient->get($endpoint, [
        'query' => $query,
        'headers' => ['Accept' => 'application/xml'],
        'cert' => [$cert, $passphrase],
        'ssl_key' => [$key],
      ]);
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($e);
      $this->getLogger('pdh_connector')->log(isset($variables['severity']) ? $variables['severity'] : 'error', '%type: @message in %function (line %line of %file). <br /> @backtrace_string', $variables);

      return FALSE;
    }

    return $request;
  }

}
