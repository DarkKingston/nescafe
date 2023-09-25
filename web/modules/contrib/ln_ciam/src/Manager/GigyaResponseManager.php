<?php

namespace Drupal\ln_ciam\Manager;

/**
 * Giyga Response manager.
 */
class GigyaResponseManager { 

  /**
   * Calling default construct.
   */
  public function __construct() {
  }

  /**
   * Create response on API call.
   */
  public function createResponse($status, $message = NULL, $fieldName = NULL) {
    $response = "";
    switch ($status) {
      case "FAIL":
      case "FORBIDDEN":
        if ($fieldName) {
          $validationErrors[] = ['fieldName' => $fieldName, 'message' => $message];
        }
        else {
          $validationErrors[] = ['message' => $message];
        }
        $response = json_encode(['status' => $status, 'data' => ['validationErrors' => $validationErrors]]);
        break;

      case "OK":
        $response = json_encode(['status' => $status]);
        break;
    }
    return $response;
  }

}
