<?php

namespace Drupal\notification_system_dispatch_webpush;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use JWage\APNS\Certificate;
use JWage\APNS\Client;
use JWage\APNS\Sender;
use JWage\APNS\SocketClient;

/**
 * A service to manage user tokens, device tokens and sending of Safari Push.
 */
class AppleWebPushClient {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The dispatcher Configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $loggerChannel;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Constructs an AppleWebPushClient object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory, FileSystemInterface $file_system, Connection $database) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->database = $database;

    $this->loggerChannel = $logger_factory->get('notification_system_dispatch_webpush');
    $this->config = $this->configFactory->get('notification_system_dispatch_webpush.settings');
  }

  /**
   * Checks if Safari Web Push is enabled.
   *
   * @return bool
   *   The state.
   */
  public function isEnabled(): bool {
    return $this->config->get('apple_enabled') == TRUE;
  }

  /**
   * Load the websitePushId from the dispatcher config.
   *
   * @return string
   *   The website push id.
   *
   * @throws \Exception
   *   When id is not set in configuration.
   */
  public function getWebsitePushId(): string {
    $websitePushId = $this->config->get('apple_website_push_id');

    if (empty($websitePushId) || strlen($websitePushId) == 0) {
      $this->loggerChannel->error('Safari Website Push ID is missing in dispatcher configuration');
      throw new \Exception('Safari Website Push ID is missing in dispatcher configuration');
    }

    return (string) $websitePushId;
  }

  /**
   * Load the Safari Web Push Certificate path from the dispatcher config.
   *
   * Validates if the file exists.
   *
   * @return string
   *   The path to the certificate.
   *
   * @throws \Exception
   */
  public function getCertificatePath(): string {
    $certificatePath = $this->config->get('apple_cert_path');

    if (empty($certificatePath) || strlen($certificatePath) == 0) {
      $this->loggerChannel->error('Safari Web Push Certificate is missing in dispatcher configuration');
      throw new \Exception('Safari Web Push Certificate is missing in dispatcher configuration');
    }

    if (StreamWrapperManager::getScheme($certificatePath)) {
      $path = $this->fileSystem->realpath($certificatePath);
    }
    else {
      $path = \Drupal::root() . DIRECTORY_SEPARATOR . $certificatePath;
    }

    if (!file_exists($path)) {
      $this->loggerChannel->error('Safari Web Push Certificate file is not existing: ' . $path);
      throw new \Exception('Safari Web Push Certificate file is not existing: ' . $path);
    }

    return $path;
  }

  /**
   * Load the Safari Web Push Certificate password from the dispatcher config.
   *
   * @return string
   *   The password of the certificate file.
   *
   * @throws \Exception
   */
  public function getCertificatePassword(): string {
    $certificatePassword = $this->config->get('apple_cert_password');

    if (empty($certificatePassword) || strlen($certificatePassword) == 0) {
      $this->loggerChannel->error('Safari Website Push Certificate password is missing in dispatcher configuration');
      throw new \Exception('Safari Website Push ID Certificate password is missing in dispatcher configuration');
    }

    return (string) $certificatePassword;
  }

  /**
   * Load a user by a user_token.
   *
   * @param string $userToken
   *   The user token.
   *
   * @return int
   *   The id of the user with this token.
   *
   * @throws \Exception
   *   When the user was not found.
   */
  public function getUserByToken(string $userToken): int {
    $result = $this->database->select('notification_system_dispatch_webpush_apple_user_tokens', 'ut')
      ->fields('ut', ['uid', 'token'])
      ->condition('token', $userToken)
      ->execute();

    $rows = $result->fetchAllAssoc('uid');

    if (count($rows) == 0) {
      $this->loggerChannel->error('User with user_token ' . $userToken . ' not found');
      throw new \Exception('User with user_token ' . $userToken . ' not found');
    }

    return array_values($rows)[0]->uid;
  }

  /**
   * Get the token of a user or generates a new one if not existing.
   *
   * @param int $userId
   *   The id of the user.
   *
   * @return string
   *   The token.
   *
   * @throws \Exception
   */
  public function getUserToken(int $userId): string {
    $result = $this->database->select('notification_system_dispatch_webpush_apple_user_tokens', 'ut')
      ->fields('ut', ['uid', 'token'])
      ->condition('uid', $userId)
      ->execute();

    $rows = $result->fetchAllAssoc('uid');

    if (count($rows) == 0) {
      $token = $this->generateUserToken($userId);
    }
    else {
      $token = array_values($rows)[0]->token;
    }

    return $token;
  }

  /**
   * Generate a random token for a user and save it in its user profile.
   *
   * @param int $userId
   *   The id of the user.
   *
   * @return string
   *   The user_token.
   *
   * @throws \Exception
   */
  protected function generateUserToken(int $userId): string {
    /** @var \Drupal\Component\Uuid\UuidInterface $uuid */
    $uuid = \Drupal::service('uuid');

    $token = $uuid->generate();

    $this->database->insert('notification_system_dispatch_webpush_apple_user_tokens')
      ->fields(['uid', 'token'], [$userId, $token])
      ->execute();

    return $token;
  }

  /**
   * Send notification to all AppleRegistrations of a user.
   *
   * @param int $uid
   *   The user id.
   * @param string $title
   *   The notification title.
   * @param string $body
   *   The notification body.
   * @param string|null $link
   *   The link of the notification.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function sendToUser(int $uid, string $title, string $body, string $link = NULL) {
    // Read certificate from p12 file.
    $certParts = [];
    openssl_pkcs12_read(file_get_contents($this->getCertificatePath()), $certParts, $this->getCertificatePassword());

    if (empty($certParts['cert']) || empty($certParts['pkey'])) {
      throw new \Exception('Certificate could not be read.');
    }

    $cert = $certParts['cert'] . "\n" . $certParts['pkey'];

    $certificate = new Certificate($cert);

    // Create APNS sender.
    $socketClient = new SocketClient($certificate, 'gateway.push.apple.com', 2195);
    $client = new Client($socketClient);
    $sender = new Sender($client);

    // Get registrations of a user.
    $registrations = $this->getRegistrationsByUser($uid);

    // Send out the notifications.
    foreach ($registrations as $registration) {
      $result = $sender->send($registration->getDeviceToken(), $title, $body, $link);

      if (!$result) {
        $this->loggerChannel->error('Failed to send Safari Web Push notification to user ' . $uid . ', device ' . $registration->getDeviceToken());
      }
    }
  }

  /**
   * Get all AppleRegistrations for a user.
   *
   * @param int $uid
   *   The user id.
   *
   * @return \Drupal\notification_system_dispatch_webpush\Entity\AppleRegistrationInterface[]
   *   A list of registrations.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getRegistrationsByUser(int $uid): array {
    $storage = $this->entityTypeManager->getStorage('apple_registration');
    $result = $storage->getQuery()
      ->condition('uid', $uid)
      ->execute();

    /** @var \Drupal\notification_system_dispatch_webpush\Entity\AppleRegistration[] $registrations */
    $registrations = $storage->loadMultiple($result);

    return $registrations;
  }

}
