<?php

namespace Drupal\easy_breadcrumb;

use Drupal\Core\Controller\TitleResolver as ControllerTitleResolver;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Class TitleResolver.
 */
class TitleResolver extends ControllerTitleResolver {

  /**
   * The field storage config storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Breadcrumb config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new EntityDisplayRebuilder.
   *
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface $argument_resolver
   *   The argument resolver.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(ControllerResolverInterface $controller_resolver, TranslationInterface $string_translation, ArgumentResolverInterface $argument_resolver, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, LanguageManagerInterface $languageManager) {
    parent::__construct($controller_resolver, $string_translation, $argument_resolver);
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config_factory->get(EasyBreadcrumbConstants::MODULE_SETTINGS);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request, Route $route) {
    $url = Url::fromUri("internal:" . $request->getRequestUri());
    $route_parts = explode(".", $url->getRouteName());
    $entity = NULL;
    $params = $url->getRouteParameters();
    if ($route_parts[0] === 'entity' && $route_parts[2] === 'canonical') {
      $entity_type = $route_parts[1];
      if (isset($params[$entity_type])) {
        $entity = $this->entityTypeManager->getStorage($entity_type)->load($params[$entity_type]);
      }
    }
    if ($entity !== NULL) {
      $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
      if ($entity instanceof TranslatableInterface && $entity->hasTranslation($current_langcode)) {
        $entity = $entity->getTranslation($current_langcode);
      }
      $alternative_title_field = $this->config->get(EasyBreadcrumbConstants::ALTERNATIVE_TITLE_FIELD);
      if ($entity instanceof FieldableEntityInterface && $entity->hasField($alternative_title_field) && !$entity->get($alternative_title_field)
        ->isEmpty()) {
        return Xss::filter($entity->get($alternative_title_field)->value);
      }
    }
    return parent::getTitle($request, $route);
  }

}
