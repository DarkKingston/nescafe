<?php

namespace Drupal\mobile_number_login_otp;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The GateWay Class.
 *
 * @package Drupal\mobile_number_login_otp
 */
class GateWay {

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * GateWay constructor.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config) {
    $this->httpClient = $http_client;
    $this->config = $config;
  }

  /**
   * Function sendSms.
   */
  public function sendSms(array $mobile = [], $templateId = '', array $variables = []) {
    $mainConfig = $this->config->get('mobilenumberlogin.otp.settings');
    if (empty($templateId)) {
      return new JsonResponse(['message' => 'SMS Template ID not present.']);
    }
    foreach ($mainConfig->get('templatesWrapped') as $tempId => $tempValue) {
      if ($tempId == $templateId) {
        $count = substr_count($tempValue['body'], '{#var');
        for ($i = 1; $i <= $count; $i++) {
          $searchArr[] = '{#var' . $i . '#}';
        }
        $messageWithVariables = str_replace($searchArr, $variables, $tempValue['body']);
        $type = $tempValue['type'];
      }
    }
    if (empty($messageWithVariables)) {
      return new JsonResponse(['message' => 'SMS Template ID is not configured properly.']);
    }
    $body = [
      'sender' => $mainConfig->get('sender_id'),
      'to' => implode(',', $mobile),
      'type' => !empty($type) ? $type : 'TXN',
      'source' => 'API',
      'body' => $messageWithVariables,
      'template_id' => $templateId,
    ];
    $response = $this->httpClient->post("https://api.in.kaleyra.io/v1/" . $mainConfig->get('sid') . "/messages", [
      'body' => Json::encode($body),
      'http_errors' => FALSE,
      'headers' => [
        'Content-Type' => 'application/json',
        'api-key' => $mainConfig->get('api_key'),
      ],
    ]);
    $content = Json::decode($response->getBody()->getContents());
    return new JsonResponse($content);
  }

}
