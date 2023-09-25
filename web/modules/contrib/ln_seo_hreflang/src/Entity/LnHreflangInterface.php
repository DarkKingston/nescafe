<?php

namespace Drupal\ln_seo_hreflang\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a hreflang entity type.
 */
interface LnHreflangInterface extends ContentEntityInterface {
  /**
   * Returns the path.
   *
   * @return string
   *   The path.
   */
  public function getPath();

  /**
   * Returns the url.
   *
   * @return \Drupal\Core\Url
   *   The path.
   */
  public function getUrl();

  /**
   * Returns the link.
   *
   * @return \Drupal\Core\Link
   *   The path.
   */
  public function getLink();

  /**
   * Sets the path.
   *
   * @param string $path
   *   The path.
   *
   * @return $this
   *   The called user entity.
   */
  public function setPath($path);
}
