<?php

namespace Drupal\ln_campaign;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a campaign entity type.
 */
interface LnCampaignInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the campaign title.
   *
   * @return string
   *   Title of the campaign.
   */
  public function getTitle();

  /**
   * Sets the campaign title.
   *
   * @param string $title
   *   The campaign title.
   *
   * @return \Drupal\ln_campaign\LnCampaignInterface
   *   The called campaign entity.
   */
  public function setTitle($title);

  /**
   * Gets the campaign creation timestamp.
   *
   * @return int
   *   Creation timestamp of the campaign.
   */
  public function getCreatedTime();

  /**
   * Sets the campaign creation timestamp.
   *
   * @param int $timestamp
   *   The campaign creation timestamp.
   *
   * @return \Drupal\ln_campaign\LnCampaignInterface
   *   The called campaign entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the campaign status.
   *
   * @return bool
   *   TRUE if the campaign is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the campaign status.
   *
   * @param bool $status
   *   TRUE to enable this campaign, FALSE to disable.
   *
   * @return \Drupal\ln_campaign\LnCampaignInterface
   *   The called campaign entity.
   */
  public function setStatus($status);

}
