<?php

namespace Drupal\ln_srh;


interface SRHConstants
{
  const SRH_RECIPE_BUNDLE = 'srh_recipe';
  const SRH_COMPLEMENT_BUNDLE = 'srh_complement';
  const SRH_MEDIA_IMAGE_BUNDLE = 'image';
  const SRH_MEDIA_IMAGE_FIELD = 'field_media_image';
  const SRH_MEDIA_REMOTE_VIDEO_BUNDLE = 'remote_video';
  const SRH_MEDIA_REMOTE_VIDEO_FIELD = 'field_media_oembed_video';
  const SRH_MEDIA_THUMBNAIL_URL_FIELD = 'field_media_thumbnail_url';
  const SRH_RECIPE_EXTERNAL_FIELD = 'field_srh_id';
  const SRH_RECIPE_TAGGING_FIELD = 'field_srh_tagging';
  const SRH_RECIPE_TIMES_FIELD = 'field_srh_times';
  const SRH_RECIPE_BRAND_FIELD = 'field_srh_brand';
  const SRH_RECIPE_CHEF_FIELD = 'field_srh_chef';
  const SRH_RECIPE_SERVING_FIELD = 'field_srh_serving';
  const SRH_RECIPE_DIFFICULTY_FIELD = 'field_srh_difficulty';
  const SRH_API_VERSION = 'v5';
  const SRH_STATUS_PUBLISHED = 1;
  const SRH_STATUS_DELETED = 5;
  const SRH_MODULES = ['ln_srh_basic','ln_srh_standard','ln_srh_extended','ln_srh_full'];
}
