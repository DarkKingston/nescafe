<?php

namespace Drupal\ln_campaign\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\ln_campaign\Entity\LnCampaign;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Sets the current ln_campaign as a context on ln_campaign routes.
 */
class LnCampaignRoute implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new LnCampaignRouteContext.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $result = [];
    $context_definition = EntityContextDefinition::create('ln_campaign')->setRequired(FALSE);
    $value = NULL;
    if (($route_object = $this->routeMatch->getRouteObject())) {
      $route_contexts = $route_object->getOption('parameters');
      // Check for a ln_campaign revision parameter first.
      if (isset($route_contexts['ln_campaign_revision']) && $revision = $this->routeMatch->getParameter('ln_campaign_revision')) {
        $value = $revision;
      }
      elseif (isset($route_contexts['ln_campaign']) && $ln_campaign = $this->routeMatch->getParameter('ln_campaign')) {
        $value = $ln_campaign;
      }
      elseif (isset($route_contexts['ln_campaign_preview']) && $ln_campaign = $this->routeMatch->getParameter('ln_campaign_preview')) {
        $value = $ln_campaign;
      }
      elseif ($this->routeMatch->getRouteName() == 'ln_campaign.add') {
        $ln_campaign_type = $this->routeMatch->getParameter('ln_campaign_type');
        $value = LnCampaign::create(['type' => $ln_campaign_type->id()]);
      }
    }

    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['route']);

    $context = new Context($context_definition, $value);
    $context->addCacheableDependency($cacheability);
    $result['ln_campaign'] = $context;

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $context = EntityContext::fromEntityTypeId('ln_campaign', $this->t('Ln Campaign from URL'));
    return ['ln_campaign' => $context];
  }

}
