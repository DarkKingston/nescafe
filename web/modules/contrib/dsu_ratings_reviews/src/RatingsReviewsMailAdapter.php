<?php

namespace Drupal\dsu_ratings_reviews;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\dsu_ratings_reviews\Form\RatingsReviewsSettingsForm;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RatingsReviewsMailAdapter.
 */
class RatingsReviewsMailAdapter implements ContainerInjectionInterface {

  use StringTranslationTrait;

  const COMMENT_TYPE = 'dsu_ratings_reviews_comment_type';

  const MAIL_KEY = 'comment_created';

  const ROLE_MODERATOR = 'ln_moderator';

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Provides messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Token Service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * RatingsReviewsAdaptations constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Entity field manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\Core\Utility\Token $token
   *   Token Service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser, Connection $connection, EntityFieldManagerInterface $entityFieldManager, ConfigFactoryInterface $configFactory, MessengerInterface $messenger, Token $token) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->connection = $connection;
    $this->entityFieldManager = $entityFieldManager;
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('config.factory'),
      $container->get('messenger'),
      $container->get('token')
    );
  }

  /**
   * Implements hook_entity_insert().
   *
   * Sends an email to certain users when comments are to be moderated.
   */
  public function commentInsertSendMail(EntityInterface $entity) {
    // Filter out other entities or comments.
    if ($entity->getEntityTypeId() !== 'comment'
      || ($entity->getEntityTypeId() === 'comment' && $entity->bundle() !== self::COMMENT_TYPE)) {
      return;
    }
    // Replies do not need moderation.
    /** @var \Drupal\comment\CommentInterface $entity */
    if ($entity->isPublished() || !empty($entity->getParentComment())) {
      return;
    }

    $mail_config = $this->configFactory->get('dsu_ratings_reviews.settings');
    $subject = $mail_config->get(RatingsReviewsSettingsForm::CONFIG_SUBJECT);
    $body = $mail_config->get(RatingsReviewsSettingsForm::CONFIG_BODY);
    if (empty($subject)) {
      return;
    }
    $subject = $this->token->replace($subject, ['comment' => $entity]);
    $body = $this->token->replace($body, ['comment' => $entity]);

    // TODO: Load users and prepare mails. Function?.
    $mails = [];
    $ids = Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', self::ROLE_MODERATOR)
      ->execute();
    foreach ($ids as $id) {
      $mails[] = User::load($id)->getEmail();
    }

    $to = implode(',', $mails);
    $params = [
      'body'    => $body,
      'subject' => $subject,
      'headers' => [
        'Bcc' => $to,
      ],
    ];

    // TODO: Check langcode of user or use generic of site?

    /** @var \Drupal\Core\Mail\MailManagerInterface $mailManager */
    $mailManager = Drupal::service('plugin.manager.mail');
    $result = $mailManager->mail('dsu_ratings_reviews', self::MAIL_KEY, $to,
      $this->currentUser->getPreferredLangcode(), $params, NULL, TRUE);

    if ($result['result'] !== TRUE) {
      $this->messenger->addMessage($this->t('Moderator notification could not be sent after comment was created.'));
    }

  }

  /**
   * Implements hook_mail().
   *
   * Sends an email to specific users to moderate comments when created.
   */
  public function sendMail($key, &$message, $params) {
    switch ($key) {
      case self::MAIL_KEY:
        $site_config = $this->configFactory->get('system.site');
        $message['from'] = $site_config->get('mail');
        if (empty($message['to'])) {
          $message['to'] = $params['to'];
        }
        $message['subject'] = $params['subject'];
        $message['body'][] = $params['body'];
        break;
    }
  }

}
