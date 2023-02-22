<?php

namespace Drupal\ln_contenthub;

/**
 * Defines an interface for Content Hub Images plugins.
 */
interface ContentHubInterface {

  /**
   * Search assets in Content Hub.
   *
   * @see ln_contenthub related docs
   *
   * @param string $keyword
   *   The search string.
   *
   * @param intiger $per_page_record
   *   The per page record on load more.
   *
   * @param integer $page_index
   *   The page index on load more.
   *
   * @param string $file_type
   *   The search file type.
   *
   *  @param array $extra_param
   *   Extra filter query parameter.
   *
   * @return array
   *   Array of search result hits.
   */
  public function search($keyword, $per_page_record, $page_index, $file_type, $extra_param);

  /**
   * Check Intellectual Property Rights in Content Hub.
   *
   * @see ln_contenthub related docs
   *
   * @param string $id
   *   The media id.
   */
  public function checkIntellectualPropertyRights($id);

  /**
   * Returns server uri.
   *
   * @return string
   */
  public function getServerUri();

  /**
   * Returns API key.
   *
   * @return string
   */
  public function getApiKey();

  /**
   * Add global query filter in Content Hub.
   *
   * @return string
   *   String for global filter query.
   */
  public function addGlobalQueryFilter();

  /**
   * Get assets in Content Hub.
   *
   * @see ln_contenthub related docs
   *
   *  @param array $param
   *   query filter parameter.
   *
   * @return array
   *   Array of search result hits.
   */
  public function query(array $param);

  /**
   * Save file content.
   *
   *  @param string $file_name
   *   File name.
   *
   *  @param string $file_url
   *   Remote file URL.
   *
   * @return string
   *   Local file path.
   */
  public function saveFileData($file_name, $file_url);

  /**
   * Save file in DB.
   *
   *  @param string $file_name
   *   File name.
   *
   *  @param string $file_url
   *   Local file URL.
   *
   * @return string
   *   Local file path.
   */
  public function fileCreate($file_name, $file_path);

  /**
   * Update media entity.
   *
   *  @param integer $entity_id
   *   Local media id.
   *
   *  @param array $entity
   *   Contenthub entity.
   */
  public function updateMediaEntity($entity_id, $entity);

}
