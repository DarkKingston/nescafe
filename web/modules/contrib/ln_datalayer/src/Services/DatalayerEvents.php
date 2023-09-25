<?php

namespace Drupal\ln_datalayer\Services;

use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * The DatalayerEvents service.
 */
class DatalayerEvents implements DatalayerEventsInterface {

  /**
   * The datalayer events bag.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionBagInterface
   */
  protected $datalayerEventsBag;

  /**
   * The kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Messenger constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionBagInterface $datalayer_events_bag
   *   The datalayer events bag.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   The kill switch.
   */
  public function __construct(SessionBagInterface $datalayer_events_bag, KillSwitch $killSwitch) {
    $this->datalayerEventsBag = $datalayer_events_bag;
    $this->killSwitch = $killSwitch;
  }

  /**
   * {@inheritdoc}
   */
  public function addEvent($key, $event) {
    $this->datalayerEventsBag->add($key, $event);

    // Mark this page as being uncacheable.
    $this->killSwitch->trigger();

    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function all() {
    return $this->datalayerEventsBag->clear();
  }
}
