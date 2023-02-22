<?php

namespace Drupal\ln_srh_menuiq\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class MenuIqAddSidedishCommand.
 */
class MenuIqAddSidedishCommand implements CommandInterface {

  /**
   * A sidedish identifier.
   *
   * @var string
   */
  protected $sidedishId;

  /**
   * A sidedish category identifier.
   *
   * @var string
   */
  protected $sidedishCategory;

  /**
   * A json with sidedishes recalculated score diff.
   *
   * @var string
   */
  protected $sidedishesScoreDiff;

  /**
   * @param $sidedishId
   * @param $sidedishCategory
   * @param $sidedishesScoreDiff
   */
  public function __construct($sidedishId,$sidedishCategory,$sidedishesScoreDiff) {
    $this->sidedishId = $sidedishId;
    $this->sidedishCategory = $sidedishCategory;
    $this->sidedishesScoreDiff = $sidedishesScoreDiff;
  }

  public function render() {
    return [
      'command' => 'MenuIqAddSidedishCommand',
      'sidedishId' => $this->sidedishId,
      'sidedishCategory' => $this->sidedishCategory,
      'sidedishesScoreDiff' => $this->sidedishesScoreDiff,
    ];
  }
}
