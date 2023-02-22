<?php

namespace Drupal\ln_quiz\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Component\Serialization\Json;

/**
 * Class QuizSetLocalStorage
 *
 * @package Drupal\ln_quiz\Ajax
 */
class QuizSetLocalStorage implements CommandInterface{

  /**
   * @var string
   */
  protected $localStorageKey;

  /**
   * @var array
   */
  protected $localStorageValue;

  /**
   * QuizSetLocalStorage constructor.
   *
   * @param $localStorageKey
   * @param $localStorageValue
   */
  public function __construct($localStorageKey, $localStorageValue) {
    $this->localStorageKey = $localStorageKey;
    $this->localStorageValue = $localStorageValue;
  }

  public function render() {
    return [
      'command' => 'QuizSetLocalStorage',
      'localStorageKey' => $this->localStorageKey,
      'localStorageValue' => Json::encode($this->localStorageValue)
    ];
  }

}
