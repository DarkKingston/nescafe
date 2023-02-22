<?php

namespace Drupal\google_optimize_js;

/**
 * A service that manages the conditional inclusion of related snippets.
 */
interface InclusionInterface {

  /**
   * Should the optimize snippet be included on the current page?
   *
   * @return bool
   *   TRUE if the optimize snippet should be included, otherwise FALSE.
   */
  public function includeOptimizeSnippet();

  /**
   * Should the anti-flicker snippet be included on the current page?
   *
   * @return bool
   *   TRUE if the anti-flicker snippet should be included, otherwise FALSE.
   */
  public function includeAntiFlickerSnippet();

}
