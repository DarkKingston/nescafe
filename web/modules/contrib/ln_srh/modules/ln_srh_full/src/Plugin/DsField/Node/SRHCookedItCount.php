<?php

namespace Drupal\ln_srh_full\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\flag\FlagCountManagerInterface;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ln_srh_full\SRHFullConstants;

/**
 * Plugin that renders cooked it count
 *
 * @DsField(
 *   id = "srh_cooked_it_count",
 *   title = @Translation("SRH Cooked It Count"),
 *   provider = "ln_srh_full",
 *   entity_type = "node",
 *   ui_limit = {"srh_recipe|*"},
 * )
 */
class SRHCookedItCount extends DsFieldBase {

  /**
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected FlagServiceInterface $flagService;

  /**
   * @var \Drupal\flag\FlagCountManagerInterface
   */
  protected FlagCountManagerInterface $flagCountManager;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, FlagServiceInterface $flagService, FlagCountManagerInterface $flagCountManager) {
    $this->flagService = $flagService;
    $this->flagCountManager = $flagCountManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('flag'),
      $container->get('flag.count')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $flag = $this->flagService->getFlagById(SRHFullConstants::SRH_LETS_COOK_IT_FLAG_ID);
    // Do nothing if "Let's cook it" flag doesn't exist.
    if (!$flag) {
      return [];
    }

    $counts = $this->flagCountManager->getEntityFlagCounts($this->entity());
    $count = $counts[SRHFullConstants::SRH_LETS_COOK_IT_FLAG_ID] ?? 0;

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('%count Cooked it', ['%count' => $count]),
      '#cache' => [
        'tags' => [
          'node:' . $this->entity()->id(),
          'flag:' . SRHFullConstants::SRH_LETS_COOK_IT_FLAG_ID . ':' . $this->entity()->id(),
        ],
      ],
    ];
  }

}
