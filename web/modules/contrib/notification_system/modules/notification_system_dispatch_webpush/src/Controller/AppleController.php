<?php

namespace Drupal\notification_system_dispatch_webpush\Controller;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\notification_system_dispatch_webpush\ApplePush\MyPackageGenerator;
use Drupal\notification_system_dispatch_webpush\AppleWebPushClient;
use Drupal\notification_system_dispatch_webpush\Entity\AppleRegistration;
use JWage\APNS\Certificate;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Returns responses for Notification System Dispatch Web Push routes.
 */
class AppleController extends ControllerBase {

  /**
   * The AppleWebPushClient.
   *
   * @var \Drupal\notification_system_dispatch_webpush\AppleWebPushClient
   */
  protected AppleWebPushClient $appleWebPushClient;

  /**
   * AppleController constructor.
   *
   * @param \Drupal\notification_system_dispatch_webpush\AppleWebPushClient $apple_web_push_client
   *   The AppleWebPushClient.
   */
  public function __construct(AppleWebPushClient $apple_web_push_client) {
    $this->appleWebPushClient = $apple_web_push_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('notification_system_dispatch_webpush.apple'),
    );
  }

  /**
   * Checks if apple support is enabled.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    return AccessResultAllowed::allowedIf($this->appleWebPushClient->isEnabled());
  }

  /**
   * Return the apple user_token for the currently signed in user.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   A json string containing the property 'user_token'.
   *
   * @throws \Exception
   */
  public function userToken(): CacheableJsonResponse {
    $user = \Drupal::currentUser();

    $userToken = $this->appleWebPushClient->getUserToken($user->id());

    $response = new CacheableJsonResponse([
      'user_token' => $userToken,
    ]);

    $cache_metadata = new CacheableMetadata();
    $cache_metadata->addCacheableDependency($user);
    $cache_metadata->addCacheContexts(['user']);
    $response->addCacheableDependency($cache_metadata);

    return $response;
  }

  /**
   * Redirect a user to a url given via GET parameter.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The http request.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A redirect response.
   */
  public function redirectNotification(Request $request) {
    $url = $request->get('url');

    return new TrustedRedirectResponse($url);
  }

  /**
   * Generates a push package for the user given in the body of the request.
   *
   * @param string $version
   *   The api version.
   * @param string $websitePushId
   *   The website push id.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   The generated zip file.
   *
   * @throws \Exception
   */
  public function pushpackage(string $version, string $websitePushId): BinaryFileResponse {
    if ($websitePushId !== $this->appleWebPushClient->getWebsitePushId()) {
      throw new BadRequestHttpException('Invalid website push id.');
    }

    // Load certificate.
    $certificatePath = $this->appleWebPushClient->getCertificatePath();
    $certificatePassword = $this->appleWebPushClient->getCertificatePassword();

    $certificate = new Certificate(file_get_contents($certificatePath), $certificatePassword);

    // Validate if host is HTTPS.
    if (\Drupal::request()->getScheme() == 'http') {
      throw new BadRequestHttpException('The website is not using HTTPS. Safari Web push is only supported via HTTPS.');
    }

    // Get Vars.
    $host = \Drupal::request()->getHttpHost();
    $websiteName = \Drupal::config('system.site')->get('name');

    $language_none = \Drupal::languageManager()->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $webServiceUrl = Url::fromRoute('notification_system_dispatch_webpush.apple')
      ->setOption('language', $language_none)
      ->setAbsolute(TRUE)
      ->toString();

    $packageGenerator = new MyPackageGenerator($certificate, $host, $webServiceUrl, $websiteName, $websitePushId);

    // Validate the user_token in the body.
    $body = \json_decode(\Drupal::request()->getContent());

    if (!$body || empty($body->user_token)) {
      throw new BadRequestHttpException('"user_token" not found in body');
    }

    $userToken = $body->user_token;

    try {
      $this->appleWebPushClient->getUserByToken($userToken);
    }
    catch (\Exception $e) {
      throw new BadRequestHttpException('No user with the user token ' . $userToken . ' was found');
    }

    $package = $packageGenerator->createPushPackageForUser($userToken);

    $path = $package->getZipPath();

    return new BinaryFileResponse($path, 200);
  }

  /**
   * Store a device_token in the database.
   *
   * @param string $version
   *   The api version.
   * @param string $deviceToken
   *   The token of the device.
   * @param string $websitePushId
   *   The website push id.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A 200 OK if it was saved.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function registration(string $version, string $deviceToken, string $websitePushId): Response {
    if ($websitePushId !== $this->appleWebPushClient->getWebsitePushId()) {
      throw new BadRequestHttpException('Invalid website push id.');
    }

    $authorization = \Drupal::request()->headers->get('Authorization');

    $userToken = str_replace('ApplePushNotifications ', '', $authorization);

    try {
      $user = $this->appleWebPushClient->getUserByToken($userToken);
    }
    catch (\Exception $e) {
      throw new BadRequestHttpException('No user found with the user_token ' . $userToken);
    }

    $registration = AppleRegistration::create([
      'uid' => $user,
      'device_token' => $deviceToken,
    ]);

    $registration->save();

    return new Response('OK');
  }

  /**
   * Delete a device_token from the database.
   *
   * @param string $version
   *   The api version.
   * @param string $deviceToken
   *   The device token.
   * @param string $websitePushId
   *   The website push id.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   200 OK if it was deleted.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function delete(string $version, string $deviceToken, string $websitePushId): Response {
    if ($websitePushId !== $this->appleWebPushClient->getWebsitePushId()) {
      throw new BadRequestHttpException('Invalid website push id.');
    }

    $authorization = \Drupal::request()->headers->get('Authorization');

    $userToken = str_replace('ApplePushNotifications ', '', $authorization);

    try {
      $user = $this->appleWebPushClient->getUserByToken($userToken);
    }
    catch (\Exception $e) {
      throw new BadRequestHttpException('No user found with the user_token ' . $userToken);
    }

    $registrationStorage = $this->entityTypeManager()->getStorage('apple_registration');
    $registrations = $registrationStorage->loadByProperties([
      'device_token' => $deviceToken,
      'uid' => $user,
    ]);

    $registrationStorage->delete($registrations);

    return new Response('OK', 200);
  }

  /**
   * Log api callback.
   *
   * @param string $version
   *   The api version.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   200 OK if it was logged.
   */
  public function log(string $version): Response {
    $body = \json_decode(\Drupal::request()->getContent());

    $logs = "\n\n";
    $logs .= implode("\n\n", $body->logs);

    $this->getLogger('notification_system_dispatch_webpush')->warning('Errors logged from apple webservice: ' . $logs);

    return new Response('OK', 200);
  }

}
