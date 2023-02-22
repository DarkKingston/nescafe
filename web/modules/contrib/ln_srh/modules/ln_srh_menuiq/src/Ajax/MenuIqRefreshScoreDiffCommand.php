<?php

namespace Drupal\ln_srh_menuiq\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class MenuIqRefreshScoreDiffCommand.
 */
class MenuIqRefreshScoreDiffCommand implements CommandInterface {

  /**
   * A score diff.
   *
   * @var string
   */
  protected $scoreDiff;

  /**
   * A sidedish identifier.
   *
   * @var string
   */
  protected $sidedishId;

  /**
   * A json mymenuiq balcnce settings.
   *
   * @var string
   */
  protected $balance;

  /**
   * @param $balance
   * @param $sidedishId
   * @param $scoreDiff
   */
  public function __construct($balance,$sidedishId,$scoreDiff) {
    $this->sidedishId = $sidedishId;
    $this->scoreDiff = $scoreDiff;
    $this->balance = $balance;
  }

  public function render() {
    return [
      'command' => 'MenuIqRefreshScoreDiffCommand',
      'scoreDiff' => $this->scoreDiff,
      'balance' => $this->balance,
      'sidedishId' => $this->sidedishId,
    ];
  }
}
