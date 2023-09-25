<?php

namespace Drupal\dsu_security_admin_module\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RedirectAuthSubscriber.
 */
class RedirectAuthSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal\Core\Path\CurrentPathStack definition.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathCurrent;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new RedirectAuthSubscriber object.
   */
  public function __construct(ConfigFactory $config_factory, CurrentPathStack $path_current, RequestStack $requestStack) {
    $this->configFactory = $config_factory;
    $this->pathCurrent = $path_current;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onUserAuthRequest'];

    return $events;
  }

  /**
   * This method is called whenever the kernel.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Event object.
   *
   * @return bool
   *   return boolean
   */
  public function onUserAuthRequest(GetResponseEvent $event) {
    $config = $this->configFactory->get('dsu_security_admin_module.authredirect');
    $authBlocked = (bool) $config->get('block_auth');
    $allowedHosts = [];
    if($allowed_domainds_setting = $config->get('allowed_domains')){
      $allowedHosts = explode(',', $allowed_domainds_setting);
    }
    $currentHostURL = $this->requestStack->getCurrentRequest()->getHttpHost();
    $uri = $this->pathCurrent->getPath();

    $blockedPaths = [
      '/user/login',
      '/user/register',
      '/user/logout',
      '/user/password',
      '/admin',
    ];

    $word_arr = ['factory', 'pantheonsite', 'acquia-sites', 'platformsh.site'];
    // Check if current URL has the word.
    // It will be accepted by default.
    $matches = [];
    foreach ($word_arr as $wkey => $wval) {
      $defaultPatternAccepted = '/'.$wval.'/i';
      preg_match_all($defaultPatternAccepted, $currentHostURL, $matches, PREG_SET_ORDER, 0);
      // Once we detect that we are allowed we don't keep checking.
      if ($matches) {
        break;
      }
    }

    $blockedPath = FALSE;
    // Check if requested path is blocked.
    if (in_array($uri, $blockedPaths)) {
      $blockedPath = TRUE;
    }

    // We have exceptions in the Exceptions field.
    // @Todo: Check if the given domains are valid
    $allowedHost = strstr(implode(',', $allowedHosts), $currentHostURL);
    if ($blockedPath !== FALSE) {
      // Add drupal logger on field configuration.
      if ($this->configFactory->get('dsu_security_admin_module.authredirect')
        ->get('logging_attempt')) {
        \Drupal::logger('dsu_security_admin_module')
          ->notice('Attempt to access %uri. Auth blocked: %auth_blocked, Matches: %matches', [
            '%uri' => $currentHostURL . $uri,
            '%auth_blocked' => $authBlocked,
            '%matches' => '<pre><code>' . print_r($matches, TRUE) . '</code></pre>',
          ]);
      }
      if ($allowedHost === FALSE && $authBlocked && empty($matches)) {
        $response = new RedirectResponse('/');
        \Drupal::service('page_cache_kill_switch')->trigger();
        return $response->send();
      }
    }
    return TRUE;
  }
}
