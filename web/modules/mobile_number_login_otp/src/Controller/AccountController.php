<?php

namespace Drupal\mobile_number_login_otp\Controller;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\mobile_number_login_otp\AccountAuth;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserFloodControlInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * Provides controllers for login, login status and logout via HTTP requests.
 */
class AccountController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * String sent in responses, to describe the user as being logged in.
   *
   * @var string
   */
  const LOGGED_IN = 1;

  /**
   * String sent in responses, to describe the user as being logged out.
   *
   * @var string
   */
  const LOGGED_OUT = 0;

  /**
   * The user flood control service.
   *
   * @var \Drupal\user\UserFloodControl
   */
  protected $userFloodControl;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * The user authentication.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The available serialization formats.
   *
   * @var array
   */
  protected $serializerFormats = [];

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new UserAuthenticationController object.
   *
   * @param \Drupal\user\UserFloodControlInterface $user_flood_control
   *   The user flood control service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token
   *   The CSRF token generator.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The serializer.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\mobile_number_login_otp\AccountAuth $account_auth
   *   Mobile number login otp gateway service.
   */
  public function __construct(UserFloodControlInterface $user_flood_control, UserStorageInterface $user_storage, CsrfTokenGenerator $csrf_token, UserAuthInterface $user_auth, RouteProviderInterface $route_provider, Serializer $serializer, array $serializer_formats, LoggerInterface $logger, AccountAuth $account_auth) {
    if (!$user_flood_control instanceof UserFloodControlInterface) {
      // Ignoring phpcs errors as make it compatible with older versions.
      // @codingStandardsIgnoreStart
      @trigger_error('Passing the flood service to ' . __METHOD__ . ' is deprecated in drupal:9.1.0 and is replaced by user.flood_control in drupal:10.0.0. See https://www.drupal.org/node/3067148', E_USER_DEPRECATED);
      $user_flood_control = \Drupal::service('user.flood_control');
      // @codingStandardsIgnoreEnd
    }
    $this->userFloodControl = $user_flood_control;
    $this->userStorage = $user_storage;
    $this->csrfToken = $csrf_token;
    $this->userAuth = $user_auth;
    $this->serializer = $serializer;
    $this->serializerFormats = $serializer_formats;
    $this->routeProvider = $route_provider;
    $this->logger = $logger;
    $this->accountAuth = $account_auth;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    if ($container->hasParameter('serializer.formats') && $container->has('serializer')) {
      $serializer = $container->get('serializer');
      $formats = $container->getParameter('serializer.formats');
    }
    else {
      $formats = ['json'];
      $encoders = [new JsonEncoder()];
      $serializer = new Serializer([], $encoders);
    }

    return new static(
      $container->get('user.flood_control'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('csrf_token'),
      $container->get('user.auth'),
      $container->get('router.route_provider'),
      $serializer,
      $formats,
      $container->get('logger.factory')->get('user'),
      $container->get('mobile_number_login_otp.account.auth')
    );
  }

  /**
   * Logs in a user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response which contains the ID and CSRF token.
   */
  public function login(Request $request) {
    $format = $this->getRequestFormat($request);

    $content = $request->getContent();
    $credentials = $this->serializer->decode($content, $format);
    if (!isset($credentials['mobilenumber']) && !isset($credentials['otp'])) {
      throw new BadRequestHttpException('Missing credentials.');
    }

    if (!isset($credentials['mobilenumber'])) {
      throw new BadRequestHttpException('Missing credentials.mobile number.');
    }
    if (!isset($credentials['otp'])) {
      throw new BadRequestHttpException('Missing credentials.otp.');
    }

    $this->floodControl($request, $credentials['mobilenumber'], $credentials['extension']);

    if ($this->userIsBlocked($credentials['mobilenumber'])) {
      throw new BadRequestHttpException('The user has not been activated or is blocked.');
    }

    if ($uid = $this->accountAuth->authenticate($credentials['extension'], $credentials['mobilenumber'], $credentials['otp'])) {
      $this->userFloodControl->clear('user.http_login', $this->getLoginFloodIdentifier($request, $credentials['mobilenumber'], $credentials['extension']));

      $user = $this->userStorage->load($uid);
      $this->userLoginFinalize($user);

      $response_data = [];
      if ($user->get('uid')->access('view', $user)) {
        $response_data['current_user']['uid'] = $user->id();
      }
      if ($user->get('roles')->access('view', $user)) {
        $response_data['current_user']['roles'] = $user->getRoles();
      }
      if ($user->get('name')->access('view', $user)) {
        $response_data['current_user']['name'] = $user->getAccountName();
      }
      $response_data['csrf_token'] = $this->csrfToken->get('rest');

      $logout_route = $this->routeProvider->getRouteByName('user.logout.http');

      $logout_path = ltrim($logout_route->getPath(), '/');
      $response_data['logout_token'] = $this->csrfToken->get($logout_path);

      $encoded_response_data = $this->serializer->encode($response_data, $format);
      return new Response($encoded_response_data);
    }

    $flood_config = $this->config('user.flood');
    if ($identifier = $this->getLoginFloodIdentifier($request, $credentials['mobilenumber'], $credentials['extension'])) {
      $this->userFloodControl->register('user.http_login', $flood_config->get('user_window'), $identifier);
    }
    // Always register an IP-based failed login event.
    $this->userFloodControl->register('user.failed_login_ip', $flood_config->get('ip_window'));
    throw new BadRequestHttpException('Sorry, unrecognized username or password.');
  }

  /**
   * Verifies if the user is blocked.
   *
   * @param string $name
   *   The username.
   *
   * @return bool
   *   TRUE if the user is blocked, otherwise FALSE.
   */
  protected function userIsBlocked($name) {
    return user_is_blocked($name);
  }

  /**
   * Finalizes the user login.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   */
  protected function userLoginFinalize(UserInterface $user) {
    user_login_finalize($user);
  }

  /**
   * Gets the format of the current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return string
   *   The format of the request.
   */
  protected function getRequestFormat(Request $request) {
    $format = $request->getRequestFormat();
    if (!in_array($format, $this->serializerFormats)) {
      throw new BadRequestHttpException("Unrecognized format: $format.");
    }
    return $format;
  }

  /**
   * Enforces flood control for the current login request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param int $mobile
   *   The mobile for login credentials.
   * @param string $extension
   *   The extension for login credentials.
   */
  protected function floodControl(Request $request, $mobile, $extension) {
    $flood_config = $this->config('user.flood');
    if (!$this->userFloodControl->isAllowed('user.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
      throw new AccessDeniedHttpException('Access is blocked because of IP based flood prevention.', NULL, Response::HTTP_TOO_MANY_REQUESTS);
    }

    if ($identifier = $this->getLoginFloodIdentifier($request, $mobile, $extension)) {
      // Don't allow login if the limit for this user has been reached.
      // Default is to allow 5 failed attempts every 6 hours.
      if (!$this->userFloodControl->isAllowed('user.http_login', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
        if ($flood_config->get('uid_only')) {
          $error_message = sprintf('There have been more than %s failed login attempts for this account. It is temporarily blocked. Try again later or request a new password.', $flood_config->get('user_limit'));
        }
        else {
          $error_message = 'Too many failed login attempts from your IP address. This IP address is temporarily blocked.';
        }
        throw new AccessDeniedHttpException($error_message, NULL, Response::HTTP_TOO_MANY_REQUESTS);
      }
    }
  }

  /**
   * Gets the login identifier for user login flood control.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param int $mobile
   *   The mobile for login credentials.
   * @param string $extension
   *   The extension for login credentials.
   *
   * @return string
   *   The login identifier or if the user does not exist an empty string.
   */
  protected function getLoginFloodIdentifier(Request $request, $mobile, $extension) {
    $flood_config = $this->config('user.flood');
    $accounts = $this->userStorage->loadByProperties([
      'field_login_mobile_number' => $extension . '-' . $mobile,
      'status' => 1,
    ]);
    if ($account = reset($accounts)) {
      if ($flood_config->get('uid_only')) {
        // Register flood events based on the uid only, so they apply for any
        // IP address. This is the most secure option.
        $identifier = $account->id();
      }
      else {
        // The default identifier is a combination of uid and IP address. This
        // is less secure but more resistant to denial-of-service attacks that
        // could lock out all users with public user names.
        $identifier = $account->id() . '-' . $request->getClientIp();
      }
      return $identifier;
    }
    return '';
  }

}
