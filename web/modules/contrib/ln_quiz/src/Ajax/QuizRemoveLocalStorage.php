<?php

namespace Drupal\ln_quiz\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class QuizRemoveLocalStorage
 *
 * @package Drupal\ln_quiz\Ajax
 */
class QuizRemoveLocalStorage implements CommandInterface{

  /**
   * @var string
   */
  protected $localStorageKey;

  /**
   * QuizSetLocalStorage constructor.
   *
   * @param $localStorageKey
   */
  public function __construct($localStorageKey) {
    $this->localStorageKey = $localStorageKey;
  }

  public function render() {
    return [
      'command' => 'QuizRemoveLocalStorage',
      'localStorageKey' => $this->localStorageKey
    ];
  }

}
