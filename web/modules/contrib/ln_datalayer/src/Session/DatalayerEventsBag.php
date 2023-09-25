<?php

namespace Drupal\ln_datalayer\Session;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * DatalayerEventsBag session container.
 */
class DatalayerEventsBag implements DatalayerEventsBagInterface {

  /**
   * The bag name.
   */
  const BAG_NAME = 'ln_datalayer_events';

  /**
   * Key used when persisting the session.
   *
   * @var string
   */
  protected $storageKey;

  /**
   * Storage for data to save.
   *
   * @var array
   */
  protected $events = [];

  /**
   * Constructs a new DatalayerEvents object.
   *
   * @param string $storage_key
   *   The key used to store datalayer events attributes.
   */
  public function __construct($storage_key = '_ln_datalayer_events') {
    $this->storageKey = $storage_key;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return self::BAG_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array &$attributes) {
    $this->events = &$attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageKey() {
    return $this->storageKey;
  }

  /**
   * {@inheritdoc}
   */
  public function add($key, $event) {
    if(!isset($event[$key])){
      $this->events[$key] = $event;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $return = $this->events;
    $this->events = [];

    return $return;
  }

}
