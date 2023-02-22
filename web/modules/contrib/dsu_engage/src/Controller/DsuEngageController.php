<?php

namespace Drupal\dsu_engage\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\dsu_engage\DsuEngage;
use Firebase\JWT\JWT;
use Symfony\Component\Yaml\Yaml;


/**
 * Defines a generic controller for Engage.
 *
 * @package Drupal\dsu_engage\Controller
 */
class DsuEngageController extends ControllerBase {

  /**
   * Get Engage API token function
   * Create a request to the API - Needs API parameters from DSU Engage Module
   * administration page
   *
   * return NULL or the Token ID
   */
  public static function dsuEngageGetToken() {

    $config = \Drupal::configFactory();
    // Client certificate is available then get token from JWT.
    if ($config->get('dsu_engage.settings')
        ->get(DsuEngage::API_CLIENT_CERTIFICATE) && !empty($config->get('dsu_engage.settings')
        ->get(DsuEngage::API_CLIENT_CERTIFICATE))) {
      $options = DsuEngageController::dsuEngageGetTokenFromJWT();
    }
    else {
      $options = [
        'form_params' => [
          'grant_type' => 'password',
          'client_id' => $config->get('dsu_engage.settings')
            ->get('dsu_engage_api_client_id'),
          'client_secret' => $config->get('dsu_engage.settings')
            ->get('dsu_engage_api_client_secret'),
          'username' => $config->get('dsu_engage.settings')
            ->get('dsu_engage_api_user_username'),
          'password' => $config->get('dsu_engage.settings')
            ->get('dsu_engage_api_user_password'),
        ],
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
      ];
    }

    // Get token ID from engage API.
    $response = DsuEngageController::dsuEngageHttpRequest(
      $config->get('dsu_engage.settings')->get('dsu_engage_api_token_url'),
      $options
    );

    $responseOk = $response->getStatusCode() === 200;
    return $responseOk ? Json::decode($response->getBody()) : NULL;
  }

  /**
   * Get Engage API token function
   * Create a request to the API - Needs API parameters from DSU Engage Module
   * administration page
   *
   * return array
   */
  public static function dsuEngageGetTokenFromJWT() {
    $config = \Drupal::configFactory();
    // JWT valid for 60 seconds from the issued time.
    $issuedAt = time();
    $expirationTime = $issuedAt + 600;
    $claim_set = [
      "iss" => $config->get('dsu_engage.settings')
        ->get('dsu_engage_api_client_id'),
      "sub" => $config->get('dsu_engage.settings')
        ->get('dsu_engage_api_user_username'),
      "aud" => $config->get('dsu_engage.settings')
        ->get('dsu_engage_api_audience_url'),
      "exp" => $expirationTime,
    ];
    // Get certificate file path.
    $privateKeyFile = "file://" . $config->get('dsu_engage.settings')
        ->get(DsuEngage::API_CLIENT_CERTIFICATE);

    // Get a private key.
    $key = openssl_pkey_get_private($privateKeyFile);

    // Identity provider has a private key used to generate the signature,
    $alg = "RS256";
    $jwt = JWT::encode($claim_set, $key, $alg);

    // Prepare formatted array for API.
    $options = [
      'form_params' => [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
      ],
      'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
    ];
    return $options;
  }

  /**
   * Perform the http request
   *
   * @param $endpoint
   * @param $options
   *
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public static function dsuEngageHttpRequest($endpoint, $options) {
    $httpClient = new \GuzzleHttp\Client();
    $response = $httpClient->request('POST', $endpoint, $options);
    return $response;
  }

  /**
   * Return an array with the available countries keyed by acronym
   *
   * @return array|mixed
   */
  public static function getCountries() {
    $countries = drupal_get_path('module', 'dsu_engage') . '/datasets/countries.yml';
    $countriesList = file_exists($countries) ? Yaml::parseFile($countries) : [];
    return $countriesList;
  }

  /**
   *
   * Returns the country name based on the acronym
   *
   * @param $acronym
   *
   * @return string
   */
  public static function getCountryByAcronym($acronym) {
    $countries = self::getCountries();
    if ($countries) {
      $country = isset($countries[$acronym]) ? $countries[$acronym] : "";
    }

    return $country ?: "";
  }

  /**
   * Return an array of countries keyed by Market
   * the format is
   *
   * @return array|mixed
   */
  public static function getMarkets() {
    $countries = drupal_get_path('module', 'dsu_engage') . '/datasets/markets.yml';
    $countriesList = file_exists($countries) ? Yaml::parseFile($countries) : [];
    return $countriesList;
  }

  /**
   * Returna a list of countries for the given market
   *
   * @param $market
   *
   * @return array
   */
  public static function getMarketCountries($market) {
    $markets = self::getMarkets();
    if ($markets) {
      $countries = isset($markets[$market]) ? $markets[$market] : [];
    }
    return $countries ?: [];
  }

  /**
   * Return a list of states based on the API MARKET value
   *
   * @param string $country two letter country code
   *
   * @return array|mixed
   */
  public static function getStatesByCountry($country = "") {
    $customerCountry = $country ? strtolower($country) : strtolower(DsuEngageController::getCountryCodeByIp());
    $states = drupal_get_path('module', 'dsu_engage') . '/datasets/' . $customerCountry . '.yml';
    $statesList = file_exists($states) ? Yaml::parseFile($states) : [];
    return $statesList;
  }

  /**
   *
   * Returns the country name based on the acronym
   *
   * @param $country
   *
   * @param $acronym
   *
   * @return string
   */
  public static function getStateByAcronym($country, $acronym) {
    $states = self::getStatesByCountry($country);
    if ($states) {
      $state = isset($states[$acronym]) ? $states[$acronym] : "";
    }

    return $state ? $state : "";
  }

  /**
   * Return a list of states based on the API MARKET value
   *
   * @param string $country two letter country code
   *
   * @return array|mixed
   */
  public static function getPhonePrefixes() {
    $prefixes = drupal_get_path('module', 'dsu_engage') . '/datasets/phone_prefixes.yml';
    $prefixesList = file_exists($prefixes) ? Yaml::parseFile($prefixes) : [];
    return $prefixesList;
  }

  /**
   * Return a list of countries and prefix - array(Country (+xxx) => +xxx)
   *
   * @return array|mixed
   */
  public static function getPhonePrefixesValues() {
    $prefixes = drupal_get_path('module', 'dsu_engage') . '/datasets/phone_values.yml';
    $prefixesList = file_exists($prefixes) ? Yaml::parseFile($prefixes) : [];
    return $prefixesList;
  }

  /**
   * Return the phone prefix for a Country Code
   *
   * @param array $countrycode (two letter country code)
   *
   * @return array|mixed
   */
  public static function getCountryPrefix($countrycode) {
    $prefixes = drupal_get_path('module', 'dsu_engage') . '/datasets/phone_prefixes.yml';
    $prefixesList = file_exists($prefixes) ? Yaml::parseFile($prefixes) : [];

    return $countrycode ? [0 => $prefixesList[$countrycode[0]]] : [];
  }

  /**
   * Returns the country code based on the IP of the user
   *
   * @return string
   */
  public static function getCountryCodeByIp() {
    $client = DsuEngageController::restrictByIpGetClientOrgIp('HTTP_CLIENT_IP');
    $forward = DsuEngageController::restrictByIpGetClientOrgIp('HTTP_X_FORWARDED_FOR');
    $remote = DsuEngageController::restrictByIpGetClientOrgIp('REMOTE_ADDR');
    $country = "";
    if (filter_var($client, FILTER_VALIDATE_IP)) {
      $ip = $client;
    }
    elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
      $ip = $forward;
    }
    else {
      $ip = $remote;
    }
    if ($ip == '127.0.0.1') {
      $ip = DsuEngageController::getIpOfInternal();
    }
    $file_path = "http://www.geoplugin.net/json.gp?ip=" . $ip;
    $buffer = $ip_data = '';
    if (file_exists($file_path)) {
      $file_pointer = fopen($file_path, 'rb');
      if ($file_pointer) {
        $buffer = fread($file_pointer, filesize($file_path));
        fclose($file_pointer);
      }
      $ip_data = json_decode($buffer);
    }
    if ($ip_data && $ip_data->geoplugin_countryName !== NULL) {
      $country = $ip_data->geoplugin_countryCode;
    }
    return $country;
  }

  private static function getIpOfInternal() {
    // Pull contents from ip4.me
    $file = file_get_contents('http://ip4.me/');

    // Trim IP based on HTML formatting
    $pos = strpos($file, '+3') + 3;
    $ip = substr($file, $pos, strlen($file));

    // Trim IP based on HTML formatting
    $pos = strpos($ip, '</');
    $ip = substr($ip, 0, $pos);

    // Output the IP address of your box
    return $ip;
  }

  public static function restrictByIpGetClientOrgIp($key) {
    return isset($_SERVER[$key]) ? $_SERVER[$key] : "";
  }
}
