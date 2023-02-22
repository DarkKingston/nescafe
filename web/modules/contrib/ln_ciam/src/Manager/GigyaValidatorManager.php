<?php

namespace Drupal\ln_ciam\Manager;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

/**
 * Giyga validate manager.
 */
class GigyaValidatorManager {

  /**
   * Calling default construct.
   */
  public function __construct($apikey) {
    $this->apikey = $apikey;
  }

  /**
   * Check data is valid or not.
   */
  public function check($jws) {
    $isValid = FALSE;
    $publicKeyUrl = 'https://accounts.eu1.gigya.com/accounts.getJWTPublicKey?apiKey=' . $this->apikey;
    $buffer = '';
    $file_pointer = fopen($publicKeyUrl, 'rb');
    if ($file_pointer) {
      $buffer = fread($file_pointer, filesize($publicKeyUrl));
      fclose($file_pointer);
    }
    $publicKey = ln_ciam_json_decode($buffer);
    $n = $publicKey->n;
    $e = $publicKey->e;
    $kid = $publicKey->kid;
    $parts = explode('.', $jws);
    $keySignature = $parts[2];
    $tokenData = $parts[0] . "." . $parts[1];
    $header = ln_ciam_json_decode(base64_decode($parts[0]));
    if (isset($header['kid'])) {
      if ($header['kid'] == $kid) {
        $cont = TRUE;
      }
    }
    if ($cont) {
      $keySignature = str_replace(['-', '_'], ['+', '/'], $keySignature);
      $n = str_replace(['-', '_'], ['+', '/'], $n);
      $keySignature = base64_decode($keySignature);
      $n = base64_decode($n);
      $e = base64_decode($e);
      $rsa = new RSA();
      $rsa->loadKey([
        "n" => new BigInteger($n, 256),
        "e" => new BigInteger($e, 256),
      ]);
      $rsa->setHash('sha256');
      $rsa->setSignatureMode($rsa::SIGNATURE_PKCS1);
      $rsa->verify($tokenData, $keySignature) ? $isValid = TRUE : $isValid = FALSE;
    }
    return $isValid;
  }

}
