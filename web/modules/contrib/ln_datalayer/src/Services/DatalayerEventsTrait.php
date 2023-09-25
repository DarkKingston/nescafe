<?php

namespace Drupal\ln_datalayer\Services;

/**
 * Provides a trait for the datalayer events service.
 */
trait DatalayerEventsTrait {

  /**
   * The datalayer events service.
   *
   * @var \Drupal\ln_datalayer\Services\DatalayerEventsInterface
   */
  protected $datalayerEvents;

  /**
   * Sets the datalayer events service.
   *
   * @param \Drupal\ln_datalayer\Services\DatalayerEventsInterface
   *   The datalayer events service.
   */
  public function setDetalayerEvent(DatalayerEventsInterface $datalayer_events) {
    $this->datalayerEvents = $datalayer_events;
  }

  /**
   * Gets the datalayer events service.
   *
   * @return \Drupal\ln_datalayer\Services\DatalayerEventsInterface
   *   The datalayer events service.
   */
  public function datalayerEvents() {
    if (!isset($this->datalayerEvents)) {
      $this->datalayerEvents = \Drupal::service('ln_datalayer.events');
    }
    return $this->datalayerEvents;
  }

}
