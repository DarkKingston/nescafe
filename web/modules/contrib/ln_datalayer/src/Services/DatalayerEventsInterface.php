<?php

namespace Drupal\ln_datalayer\Services;

/**
 * Stores runtime datalayer events sent to individual users on the page.
 */
interface DatalayerEventsInterface {
  /**
   * Adds a new event to the queue.
   *
   * @param string $key
   * @param array $event
   *
   * @return $this
   */
  public function addEvent($key, $event);

  /**
   * Gets all events.
   *
   * @return array
   */
  public function all();
}
