<?php

namespace Drupal\ln_srh_menuiq\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\ln_srh_menuiq\Ajax\MenuIqAddSidedishCommand;
use Drupal\ln_srh_menuiq\Ajax\MenuIqRemoveSidedishCommand;
use Drupal\ln_srh_menuiq\Services\SRHMyMenuIQHelper;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class MyMenuIQController extends ControllerBase{

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var SRHMyMenuIQHelper
   */
  protected $menuIqHelper;

  /**
   * @param ConfigFactoryInterface $configFactory
   * @param SRHMyMenuIQHelper $menuIQHelper
   */
  public function __construct(ConfigFactoryInterface $configFactory, SRHMyMenuIQHelper $menuIQHelper) {
    $this->configFactory = $configFactory;
    $this->menuIqHelper = $menuIQHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('ln_srh_menuiq.helper')
    );
  }

  /**
   * @param Request $request
   * @param $operation
   * @return AjaxResponse
   */
  public function operationSidedish(Request $request, $operation){
    $response = new AjaxResponse();
    $data = $request->get('data',[]);
    $sidedish_id = $data['sidedish']['id'] ?? FALSE;
    $sidedish_type = $data['sidedish']['type'] ?? FALSE;
    $sidedish_category = $data['sidedish']['category'] ?? FALSE;
    $recipe_id = $data['menuIq']['id'] ?? FALSE;
    $selectedSidedishes = Json::decode($data['menuIq']['sidedishes'] ?? []);
    if ($recipe_id && $sidedish_id && $sidedish_type && $sidedish_category) {
      if ($recipe = Node::load($recipe_id)) {
        switch ($operation) {
          case 'add':
            $selectedSidedishes[$sidedish_category][$sidedish_type][] = $sidedish_id;
            break;
          case 'remove':
            $key = array_search($sidedish_id, array_column($selectedSidedishes[$sidedish_category][$sidedish_type], NULL)) ?? FALSE;
            if ($key !== FALSE) {
              unset($selectedSidedishes[$sidedish_category][$sidedish_type][$key]);
            }
            break;
        }
        $sidedishesScoreDiff = $this->calculateScoreDiff($recipe, $selectedSidedishes, $operation);
        $sidedishesScoreDiff = Json::encode($sidedishesScoreDiff);
      }
      switch ($operation) {
        case 'add':
          $response->addCommand(new MenuIqAddSidedishCommand($sidedish_id, $sidedish_category, $sidedishesScoreDiff));
          break;
        case 'remove':
          $response->addCommand(new MenuIqRemoveSidedishCommand($sidedish_id, $sidedish_category, $sidedishesScoreDiff));
          break;
      }
    }

    return $response;
  }

  /**
   * @param NodeInterface $recipe
   * @param $sidedishes
   * @param $operation
   * @return array
   */
  private function calculateScoreDiff(NodeInterface $recipe, $sidedishes, $operation = 'add'){
    $combination = [];
    $combinationStack = [];
    $sidedishesScoreDiff = [];
    foreach ($sidedishes as $sidedishesCategory) {
      foreach ($sidedishesCategory as $sidedishes_ids) {
        $sidedishesParagraphs = Paragraph::loadMultiple($sidedishes_ids);
        foreach ($sidedishesParagraphs as $sideDish) {
          $combination[] = $sideDish;
        }
      }
    }
    $combinationScore = $this->menuIqHelper->calculateScore($recipe, $combination);
    if ($recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_SIDEDISHES_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_SIDEDISHES_FIELD)->isEmpty()) {
      $sidedishesParagraphs = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_SIDEDISHES_FIELD)->referencedEntities();
      /** @var ParagraphInterface $sideDish */
      foreach ($sidedishesParagraphs as $sideDish) {
        foreach ($sidedishes as $sidedishesCategory) {
          foreach ($sidedishesCategory as $sidedishes_ids) {
            $posSidedish = array_search($sideDish->id(), $sidedishes_ids) ?? FALSE;
            if ($posSidedish !== FALSE) {
              break 2;
            }
          }
        }
        if ($posSidedish === FALSE) {
          $combinationWithSidedish = $combination;
          $combinationWithSidedish[] = $sideDish;
          $scoreWithSidedish = $this->menuIqHelper->calculateScore($recipe, $combinationWithSidedish);
          $scoreDiff = $scoreWithSidedish - $combinationScore;
          $sidedishesScoreDiff[$sideDish->id()] = $scoreDiff;
        } elseif ($operation == 'remove') {
          $scoreWithoutSidedish = $this->menuIqHelper->calculateScore($recipe, $combinationStack);
          $combinationStack[] = $sideDish;
          $scoreWithSidedish = $this->menuIqHelper->calculateScore($recipe, $combinationStack);
          $scoreDiff = $scoreWithSidedish - $scoreWithoutSidedish;
          $sidedishesScoreDiff[$sideDish->id()] = $scoreDiff;
        }
      }
    }

    return $sidedishesScoreDiff;
  }

}
