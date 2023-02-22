<?php

namespace Drupal\ln_datalayer\EventSubscriber;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Render\HtmlResponse;
use Drupal\ln_datalayer\Services\DatalayerEventsInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Response subscriber to handle Datalayer events.
 */
class DatalayerEventResponseSubscriber implements EventSubscriberInterface {

  /**
   * The datalayer events service.
   *
   * @var \Drupal\ln_datalayer\Services\DatalayerEventsInterface
   */
  protected $datalayerEvents;

  /**
   * Constructs a DatalayerEventResponseSubscriber object.
   *
   * @param \Drupal\ln_datalayer\Services\DatalayerEventsInterface $datalayer_events
   *   The datalayer events service.
   */
  public function __construct(DatalayerEventsInterface $datalayer_events) {
    $this->datalayerEvents = $datalayer_events;
  }

  /**
   * Processes attachments for HtmlResponse responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }

    $response = $event->getResponse();
    if (!($response instanceof HtmlResponse) && !($response instanceof AjaxResponse)) {
      return;
    }
    $attachments = $response->getAttachments();
    $attachments['drupalSettings']['ln_datalayer']['events'] = $this->datalayerEvents->all();
    $response->setAttachments($attachments);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond', 100];
    return $events;
  }

}
