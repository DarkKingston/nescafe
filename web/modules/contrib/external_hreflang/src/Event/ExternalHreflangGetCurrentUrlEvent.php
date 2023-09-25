<?php

namespace Drupal\external_hreflang\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Drupal\Core\Url;

/**
 * Class to get Current Url.
 *
 * @package Drupal\external_hreflang\Event
 */
class ExternalHreflangGetCurrentUrlEvent extends Event {

  const EVENT_NAME = 'external_hreflang_get_current_url';

  /**
   * Url object.
   *
   * @var \Drupal\Core\Url
   */
  private $url;

  /**
   * Get the current url object.
   *
   * @return \Drupal\Core\Url
   *   URL object.
   */
  public function getCurrentUrl() {
    return $this->url;
  }

  /**
   * Set the current url object.
   *
   * @param \Drupal\Core\Url $url
   *   URL object.
   */
  public function setCurrentUrl(Url $url) {
    $url->setAbsolute(FALSE);
    $this->url = $url;
  }

}
