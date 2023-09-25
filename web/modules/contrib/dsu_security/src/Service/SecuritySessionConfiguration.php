<?php

namespace Drupal\dsu_security\Service;

use Drupal\Core\Session\SessionConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sets session cookie lifetime dynamically.
 */
class SecuritySessionConfiguration extends SessionConfiguration {

  /**
   * {@inheritdoc}
   */
  public function getOptions(Request $request) {
    $options = parent::getOptions($request);

    // Set the cookie lifetime dynamically depending on the request.
    // Default value is 0 seconds.
    $dsu_security_config = \Drupal::config('dsu_security.settings');
    $dsu_cookie_lifetime_value = $dsu_security_config->get('override_cookies_lifetime');
    $options['cookie_lifetime'] = (!empty($dsu_cookie_lifetime_value) || $dsu_cookie_lifetime_value == '0') ? $dsu_cookie_lifetime_value : 0;

    return $options;
  }

}
