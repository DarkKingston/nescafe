<?php

namespace Drupal\ln_campaign\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Campaign type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "ln_campaign_type",
 *   label = @Translation("Campaign type"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\ln_campaign\Form\LnCampaignTypeForm",
 *       "edit" = "Drupal\ln_campaign\Form\LnCampaignTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\ln_campaign\LnCampaignTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer campaign types",
 *   bundle_of = "ln_campaign",
 *   config_prefix = "ln_campaign_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/ln_campaign_types/add",
 *     "edit-form" = "/admin/structure/ln_campaign_types/manage/{ln_campaign_type}",
 *     "delete-form" = "/admin/structure/ln_campaign_types/manage/{ln_campaign_type}/delete",
 *     "collection" = "/admin/structure/ln_campaign_types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class LnCampaignType extends ConfigEntityBundleBase {

  /**
   * The machine name of this campaign type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the campaign type.
   *
   * @var string
   */
  protected $label;

}
