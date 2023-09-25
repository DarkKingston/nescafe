<?php

namespace Drupal\external_hreflang\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Url;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\external_hreflang\Event\ExternalHreflangGetCurrentUrlEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Subscriber for ExternalHreflangGetCurrentUrlEvent.
 *
 * @package Drupal\external_hreflang\EventSubscriber
 */
class ExternalHreflangGetCurrentUrlEventSubscriber implements EventSubscriberInterface {

  use LoggerChannelTrait;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  private $pathMatcher;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * ExternalHreflangGetCurrentUrlEventSubscriber constructor.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(PathMatcherInterface $path_matcher,
                              RequestStack $request_stack) {
    $this->pathMatcher = $path_matcher;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ExternalHreflangGetCurrentUrlEvent::EVENT_NAME][] = [
      'onGetCurrentUrlEvent',
      10,
    ];
    return $events;
  }

  /**
   * Default implementation of getting current page url.
   *
   * @param \Drupal\external_hreflang\Event\ExternalHreflangGetCurrentUrlEvent $event
   *   Event object.
   */
  public function onGetCurrentUrlEvent(ExternalHreflangGetCurrentUrlEvent $event) {
    try {
      if ($this->pathMatcher->isFrontPage()) {
        $url = Url::fromRoute('<front>');
      }
      else {
        $url = Url::createFromRequest($this->requestStack->getCurrentRequest());
      }
    }
    catch (\Exception $e) {
      $this->getLogger('ExternalHreflangGetCurrentUrlEventSubscriber')->error('Error occurred while getting the url. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      // Store the empty url.
      $url = Url::fromRoute('<none>');
    }

    $event->setCurrentUrl($url);
  }

}
