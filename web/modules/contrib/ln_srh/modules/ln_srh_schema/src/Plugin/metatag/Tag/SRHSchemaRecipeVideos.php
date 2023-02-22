<?php

namespace Drupal\ln_srh_schema\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaNameBase;

/**
 * Provides a plugin for the 'name' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "srh_schema_recipe_videos",
 *   label = @Translation("Recipe Videos"),
 *   description = @Translation("REQUIRED BY GOOGLE. The video Url of the recipe."),
 *   name = "video",
 *   group = "schema_recipe",
 *   weight = 2,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = TRUE
 * )
 */
class SRHSchemaRecipeVideos extends SchemaNameBase {

}
