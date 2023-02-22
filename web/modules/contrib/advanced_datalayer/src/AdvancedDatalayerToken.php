<?php

namespace Drupal\advanced_datalayer;

use Drupal\Core\Utility\Token;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\token\TokenEntityMapperInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Token handling service. Uses core token service or contributed Token.
 */
class AdvancedDatalayerToken {

  use StringTranslationTrait;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Token entity type mapper service.
   *
   * @var \Drupal\token\TokenEntityMapperInterface
   */
  protected $tokenEntityMapper;

  /**
   * Constructs a new AdvancedDatalayerToken object.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   Token service.
   * @param \Drupal\token\TokenEntityMapperInterface $token_entity_mapper
   *   The token entity type mapper service.
   */
  public function __construct(Token $token, TokenEntityMapperInterface $token_entity_mapper) {
    $this->token = $token;
    $this->tokenEntityMapper = $token_entity_mapper;
  }

  /**
   * Wrapper for the Token module's string parsing.
   *
   * @param string $string
   *   The string to parse.
   * @param array $data
   *   Arguments for token->replace().
   * @param array $options
   *   Any additional options necessary.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *
   * @return mixed|string
   *   The processed string.
   */
  public function replace($string, array $data = [], array $options = [], BubbleableMetadata $bubbleable_metadata = NULL) {

    $replaced = '';
    if (!empty($string)) {
      // Set default requirements for advanced_datalayer
      // unless options specify otherwise.
      $options += [
        'clear' => TRUE,
      ];

      $replaced = $this->token->replace($string, $data, $options, $bubbleable_metadata);

      // Ensure that there are no double-slash sequences due to empty token
      // values.
      $replaced = preg_replace('/(?<!:)(?<!)\/+\//', '/', $replaced);
    }

    return $replaced;
  }

  /**
   * Gatekeeper function to direct to either the core or contributed Token.
   *
   * @param array $token_types
   *   The token types to filter the tokens list by. Defaults to an empty array.
   *
   * @return array
   *   If token module is installed, a popup browser plus a help text. If not
   *   only the help text.
   */
  public function tokenBrowser(array $token_types = []) {
    $form = [];

    // Normalize token types.
    if (!empty($token_types)) {
      $token_types = array_map(function ($value) {
        return $this->tokenEntityMapper->getTokenTypeForEntityType($value, TRUE);
      }, $token_types);
    }

    $form['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => $token_types,
      '#global_types' => TRUE,
      '#show_nested' => FALSE,
    ];

    return $form;
  }

}
