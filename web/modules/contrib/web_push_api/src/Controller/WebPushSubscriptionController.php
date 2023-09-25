<?php

namespace Drupal\web_push_api\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\Error;
use Drupal\web_push_api\Entity\WebPushSubscriptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * The controller for creating, updating and deleting Push API subscriptions.
 */
class WebPushSubscriptionController extends ControllerBase {

  /**
   * The list of headers the inbound request must have present.
   */
  public const HEADERS = [
    'Content-Type' => 'application/json',
  ];

  /**
   * A storage of the "web_push_subscription" entities.
   *
   * @var \Drupal\web_push_api\Entity\WebPushSubscriptionStorage
   */
  protected $storage;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    AccountInterface $current_user,
    TranslationInterface $string_translation,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->currentUser = $current_user;
    $this->loggerFactory = $logger_factory;
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $this->loggerFactory->get('web_push_api.controller');
    $this->storage = $this->entityTypeManager->getStorage(WebPushSubscriptionInterface::ENTITY_TYPE);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_user'),
      $container->get('string_translation'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function subscription(Request $request): JsonResponse {
    $errors = $this->validateRequest($request);

    if ($errors->valid()) {
      return static::response(...$errors);
    }

    $body = $errors->getReturn();

    return static::response(...\call_user_func(
      [$this, $request->getMethod() === 'DELETE' ? 'delete' : 'manage'],
      $this->storage->loadByEndpoint($body['endpoint']),
      $body
    ));
  }

  /**
   * Returns the JSON response.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup ...$errors
   *   The list of errors occurred during the request process.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public static function response(TranslatableMarkup ...$errors): JsonResponse {
    return new JsonResponse([
      'errors' => $errors,
    ]);
  }

  /**
   * Returns the request content and yields validation errors.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request to validate.
   *
   * @return \Generator
   *   The list of errors (empty if none).
   */
  protected function validateRequest(Request $request): \Generator {
    foreach (static::HEADERS as $header => $value) {
      if ($request->headers->get($header) !== $value) {
        yield $this->t('The "@header" header must be "@value".', [
          '@header' => $header,
          '@value' => $value,
        ]);
      }
    }

    $content = $request->getContent();
    $body = empty($content) ? [] : Json::decode($content);

    if (empty($body['endpoint'])) {
      yield $this->t('The "endpoint" must not be empty.');
    }

    return $body;
  }

  /**
   * Creates/updates the subscription.
   *
   * @param \Drupal\web_push_api\Entity\WebPushSubscriptionInterface|null $subscription
   *   The subscription.
   * @param array $body
   *   The subscription's data.
   *
   * @return \Generator
   *   The list of errors (empty if none).
   */
  protected function manage(?WebPushSubscriptionInterface $subscription, array $body): \Generator {
    $subscription = $subscription ?? $this->storage->create();
    $body['uid'] = $this->currentUser->id();

    foreach ($body as $key => $value) {
      $subscription->set($key, $value);
    }

    foreach ($subscription->validate() as $violation) {
      \assert($violation instanceof ConstraintViolationInterface);
      yield $this->t('@property=@message', [
        '@message' => $violation->getMessage(),
        '@property' => $violation->getPropertyPath(),
      ]);
    }

    if (!isset($violation)) {
      try {
        $this->storage->save($subscription);
      }
      catch (\Exception $e) {
        $this->loggerChannel->error(Error::renderExceptionSafe($e));
        yield $this->t('Unable to save the subscription.');
      }
    }
  }

  /**
   * Removes the subscription.
   *
   * @param \Drupal\web_push_api\Entity\WebPushSubscriptionInterface|null $subscription
   *   The subscription.
   *
   * @return \Generator
   *   The list of errors (empty if none).
   */
  protected function delete(?WebPushSubscriptionInterface $subscription): \Generator {
    if ($subscription !== NULL) {
      try {
        $this->storage->delete([$subscription]);
      }
      catch (\Exception $e) {
        $this->loggerChannel->error(Error::renderExceptionSafe($e));
        yield $this->t('Unable to delete the subscription.');
      }
    }
  }

}
