<?php

namespace Drupal\ln_ciam\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\gigya\Helper\GigyaHelper;
use Drupal\gigya\CmsStarterKit\sdk\GSObject;

use Drupal\ln_ciam\Manager\GigyaResponseManager;
use Drupal\ln_ciam\Manager\GigyaValidatorManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for extentions routes.
 */
class GigyaAPIController extends ControllerBase {

  /**
   * Callback for `gigya-api/postextension.json` API method.
   */
  public function post_extension(Request $request) {
    // This condition checks the `Content-type` and makes sure to
    // decode JSON string from the request body into array.
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
      $data = ln_ciam_json_decode($request->getContent());
      $request->request->replace(is_array($data) ? $data : []);
    }

    $gigyaHelper = new GigyaHelper();
    $gigyaConfig = \Drupal::config('gigya.settings');
    $responseManager = new GigyaResponseManager();

    $buffer = $json = '';
    $file_pointer = fopen("php://input", 'rb');
    if ($file_pointer) {
      $buffer = fread($file_pointer, filesize("php://input"));
      fclose($file_pointer);
    }
    $json = ln_ciam_json_decode($buffer);
    $jws = $json["jws"];

    $response = '';
    $apiKey = $gigyaConfig->get('gigya.gigya_api_key');

    if (!empty($jws)) {
      $validator = new GigyaValidatorManager($apiKey);
      $isValid = $validator->check($jws);
      if ($isValid) {
        $parts = explode('.', $jws);
        $payload = ln_ciam_json_decode(base64_decode($parts[1]));
      }
      else {
        $response = $responseManager->createResponse("FAIL", "Invalid JWS");
      }
    }
    else {
      $response = $responseManager->createResponse("FAIL", "Failed JWS public key validation");
    }

    if ($payload && $payload["apiKey"] == $apiKey && !$response) {

      $applicationInternalId = $payload["data"]["params"]["data"]["externalApplication"][0]["internalIdentifier"];
      $query = "select count(*) from emailAccounts where data.externalApplication.internalIdentifier = '" . $applicationInternalId . "'";
      $fieldName = "data.externalApplication.internalIdentifier";

      // hook_alter for query alter from outside of module.
      \Drupal::moduleHandler()->alter('ln_ciam_query', $query, $payload, $applicationInternalId, $fieldName);

      $accessParameters['api_key'] = $apiKey;
      $accessParameters['app_secret'] = $gigyaHelper->decrypt($gigyaConfig->get('gigya.gigya_application_secret_key'));
      $accessParameters['data_center'] = $gigyaConfig->get('gigya.gigya_data_center');
      $accessParameters['app_key'] = $gigyaConfig->get('gigya.gigya_application_key');
      $params = new GSObject();
      $params->put("query", $query);
      $responseGigya = $gigyaHelper->sendApiCall("accounts.search", $params, $accessParameters);

      if ($responseGigya->getData()->getInt("totalCount") > 0) {
        $response = $responseManager->createResponse("FAIL", "The code " . $applicationInternalId . " already exists in the system.", $fieldName);
      }
      elseif ($responseGigya->getData()->getInt("totalCount") == 0) {
        $response = $responseManager->createResponse('OK');
      }

    }
    elseif ($payload && $payload["apiKey"] != $apiKey) {
      $response = $responseManager->createResponse('OK');
    }
    else {
      $response = $responseManager->createResponse("FAIL", "Invalid JWS");
    }
    return new Response($response);
  }

}
