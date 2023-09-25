<?php

namespace Drupal\notification_system\model;

use Drupal\Core\Url;

/**
 * Represents a notification.
 */
class Notification implements NotificationInterface {

  /**
   * The id of the provider that generated the notification.
   *
   * @var string
   */
  protected string $provider;

  /**
   * An identifier for this notification.
   *
   * It is unique in the context of the notification provider.
   *
   * @var string
   */
  protected string $id;

  /**
   * An array of user ids who this notification is for.
   *
   * @var int[]
   */
  protected array $users;

  /**
   * A key which indicates the type of the notification.
   *
   * @var string
   */
  protected string $type;

  /**
   * The unix timestamp when the notification was created.
   *
   * @var int
   */
  protected int $timestamp;

  /**
   * The title of the notification.
   *
   * @var string
   */
  protected string $title;

  /**
   * Additional text of the notification.
   *
   * @var string|null
   */
  protected ?string $body;

  /**
   * A link that provides more information about the notification.
   *
   * @var \Drupal\Core\Url|null
   */
  protected ?Url $link;

  /**
   * Indicates if the notification can be deleted.
   *
   * @var bool
   */
  protected bool $sticky;

  /**
   * Indicates how important the notification is.
   *
   * @var int
   */
  protected int $priority;

  /**
   * Indicates if the notification is forced and should bypass user settings.
   *
   * @var bool
   */
  protected bool $forced;

  /**
   * {@inheritdoc}
   */
  public function __construct($provider, $id, $type, array $users, $timestamp, $title, $body = NULL, Url $link = NULL, $sticky = FALSE, $priority = self::PRIORITY_MEDIUM, $forced = FALSE) {
    $this->provider = $provider;
    $this->id = $id;
    $this->type = $type;
    $this->users = $users;
    $this->timestamp = $timestamp;
    $this->title = $title;
    $this->body = $body;
    $this->link = $link;
    $this->sticky = $sticky;
    $this->priority = $priority;
    $this->forced = $forced;
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider(): string {
    return $this->provider;
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsers(): array {
    return $this->users;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimestamp(): int {
    return $this->timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody(): ?string {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function getLink(): ?Url {
    return $this->link;
  }

  /**
   * {@inheritdoc}
   */
  public function isSticky(): bool {
    return $this->sticky;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority(): int {
    return $this->priority;
  }

  /**
   * {@inheritdoc}
   */
  public function isForced(): bool {
    return $this->forced;
  }

  /**
   * {@inheritdoc}
   */
  public function setProvider($provider) {
    $this->provider = $provider;
  }

  /**
   * {@inheritdoc}
   */
  public function setId(string $id) {
    $this->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function setType(string $type) {
    $this->type = $type;
  }

  /**
   * {@inheritdoc}
   */
  public function setUsers(array $users) {
    $this->users = $users;
  }

  /**
   * {@inheritdoc}
   */
  public function addUser(int $userId) {
    if (!in_array($userId, $this->users)) {
      $this->users[] = $userId;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeUser(int $userId) {
    if (($key = array_search($userId, $this->users)) !== FALSE) {
      unset($this->users[$key]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setTimestamp(int $timestamp) {
    $this->timestamp = $timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle(string $title) {
    $this->title = $title;
  }

  /**
   * {@inheritdoc}
   */
  public function setBody(string $body) {
    $this->body = $body;
  }

  /**
   * {@inheritdoc}
   */
  public function setLink(Url $link) {
    $this->link = $link;
  }

  /**
   * {@inheritdoc}
   */
  public function setSticky(bool $sticky) {
    $this->sticky = $sticky;
  }

  /**
   * {@inheritdoc}
   */
  public function setPriority(int $priority) {
    $this->priority = $priority;
  }

  /**
   * {@inheritdoc}
   */
  public function setForced(bool $forced) {
    $this->forced = $forced;
  }

}
