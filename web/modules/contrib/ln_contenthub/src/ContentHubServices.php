<?php

namespace Drupal\ln_contenthub;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Unicode;

/**
 * Class ContentHubServices.
 */
class ContentHubServices implements ContentHubInterface {

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Content Hub server uri from config.
   *
   * @var string
   */
  protected $server_uri;

  /**
   * Content Hub API key from config.
   *
   * @var string
   */
  protected $api_key;

  /**
   * Content Hub entity filter from config.
   *
   * @var string
   */
  protected $brand_corporate;

  /**
   * Content Hub entity filter from config.
   *
   * @var string
   */
  protected $brand_range;

  /**
   * Content Hub entity filter from config.
   *
   * @var string
   */
  protected $applicable_region;

  /**
   * Content Hub entity filter from config.
   *
   * @var string
   */
  protected $creator_region;
  
  /**
   * File upload location.
   *
   * @var string
   */
  protected $upload_location;

  /**
   * Constructs a new ContentHubService object.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $config = $this->configFactory->get('ln_contenthub.settings');
    $this->server_uri = $config->get('ln_contenthub_server_uri');
    $this->api_key = $config->get('ln_contenthub_api_key');
    $this->brand_corporate = $config->get('ln_contenthub_brand_corporate');
    $this->brand_range = $config->get('ln_contenthub_brand_range');
    $this->applicable_region = $config->get('ln_contenthub_applicable_region');
    $this->creator_region = $config->get('ln_contenthub_creator_region');
	$this->upload_location = 'public://contenthub-media/[date:custom:Y]-[date:custom:m]';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function search($keyword, $per_page_record = 100, $page_index = 0, $file_type = 'Graphics', $extra_param = array()) {
    $results = [];
    // Query example to check while we are developing.
    if(!empty($keyword)) {

      $query = 'mediabeacon/wf/restapi/v2/search?verbose=true&search={"conjunction":"and","criteria":[{"fieldId":"http://ns.mediabeacon.com/rights/ipdoc/1.0/DocumentFlag","condition":"ne","value":"TRUE"},{"fieldId":"http://ns.nestle.com/descriptive/en/1.0/ AssetStatus","condition":"eq","value":"Published"},{"conjunction":"or","criteria":[{"fieldId":"file_name","condition":"cont","value":"' . $keyword . '"},{"fieldId":"record_id","condition":"eq","value":"' . $keyword . '"}]},{"fieldId":"http://mediabeacon.com/ns/default/1.0/ format","condition":"member","value":"' . $file_type . '"}';

      // Add extra query filter.
      if (!empty($extra_param))  {
        // Add sub brand filter.
        if (isset($extra_param['ln_contenthub_sub_brand']) && !empty($extra_param['ln_contenthub_sub_brand'])) {
          $query .= ',{"fieldId": "http://ns.nestle.com/packaging/en/1.0/ SubBrand", "condition": "member", "value": "' . $extra_param['ln_contenthub_sub_brand'] . '"}';
        }
        // Add product category filter.
        if (isset($extra_param['ln_contenthub_product_category']) && !empty($extra_param['ln_contenthub_product_category'])) {
          $query .= ',{"fieldId": "http://ns.nestle.com/packaging/en/1.0/ ProductCategory", "condition": "member", "value": "' . $extra_param['ln_contenthub_product_category'] . '"}';
        }
      }
      
      $query .= $this->addGlobalQueryFilter();

      $query .= ']}';

	    // Limit file size to 10MB.
	    $query .= ($file_type == 'Video') ? '&bytes=<10485760' : '';
		  
      if(!empty($per_page_record) && (!empty($page_index) || $page_index == 0)) {
        $query .= '&pageSize='.$per_page_record;
      }
      try {
        $results = $this->requestImages($this->server_uri, $query);
      } catch (\Exception $e) {
        \Drupal::logger('ln_contenthub')->error($e->getMessage());
      }
    }

    return $results;
  }

  /**
   * Constructs and issues a request to Content Hub service.
   *
   * @param string $baseUri
   *   The base server uri that includes the trailing slash.
   * @param string $query
   *   (optional) Query to pass along the request.
   * @param string $method
   *   (optional) HTTP Request Method: GET, POST,...
   *
   * @return object
   *   The response returned from the http client.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *   If error code is returned.
   */
  public function requestImages($baseUri, $query = '', $method = 'GET') {
    $uri = $baseUri . $query;
    $response = $this->httpClient->request($method, $uri,[
      'headers' => ['apikey' => $this->api_key],
    ]);
    $json = $response->getBody()->getContents();
    $response_object = json_decode($json);

    if (isset($response_object->errorcode)) {
      throw new \Exception($response_object->message);
    }

    return $response_object;
  }

  /**
   * {@inheritdoc}
   */
  public function checkIntellectualPropertyRights($id) {

    $response = $this->httpClient->request('GET', $this->server_uri . 'mediabeacon/wf/restapi/1/getXmp?apikey='.$this->api_key.'&src=' . $id);
    $content = json_decode($response->getBody());
    foreach ($content as $item) {
      $content = $item;
    }

    $xmp_data_start = strpos($content, '<x:xmpmeta');
    $xmp_data_end = strpos($content, 'x:xmpmeta>');
    if ($xmp_data_start === FALSE || $xmp_data_end === FALSE) {
      return [];
    }
    $xmp_length = $xmp_data_end - $xmp_data_start;
    $xmp_data = substr($content, $xmp_data_start, $xmp_length + 10);
    $xmp = simplexml_load_string($xmp_data);
    if ($xmp === FALSE) {
      return [];
    }

    $field_data = [];
    $this->xmp_recursion($xmp, $field_data, 'XMP');
    $ip_rights = [];
    $ip_rights['ipr'] = array_key_exists('XMP:xmpmeta:rdf:description:rightsclassification', $field_data) ? $field_data['XMP:xmpmeta:rdf:description:rightsclassification'] : NULL;
    $ip_rights['ipr_expiration_date'] = array_key_exists('XMP:xmpmeta:rdf:description:expirationdate', $field_data) ? $field_data['XMP:xmpmeta:rdf:description:expirationdate'] : NULL;

    return $ip_rights;
  }

  /**
   * Iterates over xmp object to extract data.
   *
   * @param object $obj
   * @param array $fields
   * @param string name
   *
   * @return array
   */
  public function xmp_recursion($obj, array &$fields, $name) {
    $namespace = $obj->getDocNamespaces(TRUE);
    $namespace[NULL] = NULL;

    $children = [];
    $attributes = [];

    $text = trim((string) $obj);
    if (strlen($text) === 0) {
      $text = NULL;
    }

    $name = $name . ':' . strtolower((string) $obj->getName());

    if (is_object($obj)) {
      foreach ($namespace as $ns => $ns_url) {
        $obj_attributes = $obj->attributes($ns, TRUE);
        foreach ($obj_attributes as $attribute_name => $attribute_value) {
          $attr_name = strtolower(trim((string) $attribute_name));
          $att_val = trim((string) $attribute_value);
          if (!empty($ns)) {
            $attr_name = $ns . ':' . $attr_name;
          }
          $attributes[$attr_name] = $att_val;
        }
        $obj_children = $obj->children($ns, TRUE);
        foreach ($obj_children as $child_name => $child) {
          $child_name = strtolower((string) $child_name);
          if (!empty($ns)) {
            $child_name = $ns . ':' . $child_name;
          }
          $children[$child_name][] = $this->xmp_recursion($child, $fields, $name);
        }
      }
    }
    if (!is_null($text)) {
      $fields[$name] = $text;
    }

    return [
      'name' => $name,
      'text' => html_entity_decode($text),
      'attributes' => $attributes,
      'children' => $children,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getServerUri() {
    return $this->server_uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getApiKey() {
    return $this->api_key;
  }

  /**
   * {@inheritdoc}
   */
  public function addGlobalQueryFilter() {
    $query_filter = '';
    // Add Brand Range filter.
    if (!empty($this->brand_range)) {
      $query_filter .= ',{"fieldId": "http://ns.nestle.com/descriptive/en/1.0/ BrandRange", "condition": "member", "value": "' . $this->brand_range . '"}';
    }
    // Add Brand Corporate filter.
    if (!empty($this->brand_corporate)) {
      $query_filter .= ',{"fieldId": "http://ns.nestle.com/descriptive/en/1.0/ BrandCorporate", "condition": "cont", "value": "' . $this->brand_corporate . '"}';
    }
    // Add Applicable Region filter.
    if (!empty($this->applicable_region)) {
      $query_filter .= ',{"fieldId": "http://ns.nestle.com/descriptive/en/1.0/ ApplicableRegion", "condition": "member", "value": "' . $this->applicable_region . '"}';
    }
    // Add Creator Region flter.
    if (!empty($this->creator_region)) {
      $query_filter .= ',{"fieldId": "http://ns.nestle.com/administrative/en/1.0/ CreatorRegion", "condition": "member", "value": "' . $this->creator_region . '"}';
    }

    return $query_filter;
  }

  /**
   * {@inheritdoc}
   */
  public function query(array $param) {
    $results = [];
    $global_filter = $this->addGlobalQueryFilter();
    $str_length = Unicode::strlen($global_filter);
    // Global filters require for automatic update.
    if($str_length <= 0) {
      \Drupal::logger('ln_contenthub')
        ->warning('Common filters must have at least one value for automatic update cron. Check the Lightnest Content Hub configuration form.');
      return $results;
    }

    $query = 'mediabeacon/wf/restapi/v2/search?verbose=true&search={"conjunction":"and","criteria":[{"fieldId":"http://ns.mediabeacon.com/rights/ipdoc/1.0/DocumentFlag","condition":"ne","value":"TRUE"},{"fieldId":"http://ns.nestle.com/descriptive/en/1.0/ AssetStatus","condition":"eq","value":"Published"}';

    $query .= $global_filter;

    $query .= ']}';

    if(!empty($param)) {
      if (isset($param['lastModified'])) {
        $query .= '&lastModified=>'.$param['lastModified'];
      }
      if (isset($param['pageSize'])) {
        $query .= '&pageSize='.$param['pageSize'];
      }
    }

    try {
      $results = $this->requestImages($this->server_uri, $query);
    } catch (\Exception $e) {
      \Drupal::logger('ln_contenthub')->error($e->getMessage());
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function saveFileData($file_name, $file_url) {
    $destination = $this->getUploadLocation();
    $content = (string) \Drupal::httpClient()
      ->get($file_url)
      ->getBody();
    $image_uri = file_create_url("public://") . $file_name;
    $parsed_url = parse_url($image_uri);
    if (is_dir(realpath($destination))) {
      // Prevent URIs with triple slashes when glueing parts together.
      $path = str_replace('///', '//', "{$destination}/") . basename($parsed_url['path']);
    }
    else {
      $path = $destination . '/'. basename($parsed_url['path']);
    }
    try {
      $local = \Drupal::service('file_system')
        ->saveData($content, $path, FileSystemInterface::EXISTS_REPLACE);
    } catch (RequestException $exception) {
      \Drupal::logger('ln_contenthub')
        ->error('Unable to download file from content hub due to: %e', ['%e' => $exception->getMessage()]);
      return FALSE;
    }
    return $local;
  }

  /**
   * {@inheritdoc}
   */
  public function fileCreate($file_name, $file_path) {
    $file = File::create([
      'filename' => $file_name,
      'uri' => $file_path,
      'uid' => '1',
      'status' => TRUE,
    ]);
    $file->setPermanent();
    try {
      $file->save();
      return $file->id();
    }
    catch (Exception $e) {
      \Drupal::logger('ln_contenthub')->error('Cannot save file: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateMediaEntity($entity_id, $entity) {
    $download_url = $entity->previews->downloadUrl . '&apikey=' . $this->getApiKey();
    $image_name = str_replace(' ', '_', $entity->name);
    $local_path = $this->saveFileData($image_name, $download_url);
    $fid =  $this->fileCreate($entity->name, $local_path);
    $this->updateContentHubMedia($fid, $entity_id, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getUploadLocation() {
    $token_service = \Drupal::token();
    return $token_service->replace($this->upload_location);
  }

  /**
   * {@inheritdoc}
   */
  public function updateContentHubMedia($fid, $entity_id, $entity) {
    $media = Media::load($entity_id);
    $intellectual_property_rights = $this->checkIntellectualPropertyRights($entity->id);
    $ipr = !is_null($intellectual_property_rights['ipr']) ? $intellectual_property_rights['ipr'] : 'IPR data not informed.';
    $ipr_expiration_date = !is_null($intellectual_property_rights['ipr_expiration_date']) ? $intellectual_property_rights['ipr_expiration_date'] : '';
    if($media->bundle() == 'content_hub') {
      $media->set('field_media_ln_contenthub_image', ['target_id' => $fid, 'alt' => $entity->name]);
      $media->set('field_media_ln_contenthub_height', $entity->height);
      $media->set('field_media_ln_contenthub_width',	$entity->width);
    }else if ($media->bundle() == 'content_hub_document') {
      $media->set('field_media_ln_contenthub_docume', ['target_id' => $fid]);
    }
    else if($media->bundle() == 'content_hub_video') {
      $media->set('field_media_ln_contenthub_video', ['target_id' => $fid]);
    }
    else {
      return;
    }

    $media->set('thumbnail', ['target_id' => $fid]);
    $media->set('name',$entity->name);
    $media->set('field_media_ln_contenthub_bytes', $entity->bytes);
    $media->set('field_media_ln_contenthub_id', $entity->id);
    $media->set('field_media_ln_contenthub_downl', $entity->previews->downloadUrl);
    $media->set('field_media_ln_content_last_mod', $entity->lastModified);
    $media->set('field_media_ln_contenthub_mime_t', $entity->mimeType);
    $media->set('field_media_ln_contenthub_name', $entity->name);
    $media->set('field_media_ln_contenthub_path', $entity->path);
    $media->set('field_media_ln_conthub_thumbnail', $entity->previews->thumbnail);
    $media->set('field_media_ln_contenthub_viewex', $entity->previews->viewex);
    $media->set('field_ln_content_last_sync_time', time());
    $media->set('field_media_ln_contenthub_ipr', $ipr);
    $media->set('field_media_ln_contenthub_ipr_ex', $ipr_expiration_date);

    try {
      $media->save();
    }
    catch (Exception $e) {
      \Drupal::logger('ln_contenthub')->error('Cannot update media: ' . $e->getMessage());
    }
  }
}
