<?php

namespace Drupal\ln_sample_content\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

/**
 * Provides a controller which a node with data imported from yml files
 */
class SampleContentMaker extends ControllerBase {

  /**
   * Creates a node with sample data imported from modules that are implementing hook_ln_sample_content
   *
   * @throws Drupal\Core\Entity\EntityStorageException
   * Thrown when the node is not saved.
   */
  public function createNodeSampleContent() {
    $config = Drupal::service('config.factory')->getEditable('ln_sample_content.settings');
    $data = $this->getSampleContent();
    $node_title = 'Sample Components Content';
    // Get nid saved in a config variable
    $nid = $config->get('ln_sample_content.sample_content_nid');
    $node = $nid ? Node::load($nid) : NULL;

    if(is_null($node)) {
      $node = Node::create(['type' => 'dsu_component_page']);
      $node->enforceIsNew();
      $node->set('title', $node_title);
      $node->save();
      // The first time we create sample node content, the nid is saved in a config variable
      $config->set('ln_sample_content.sample_content_nid', $node->id());
      $config->save();

    }
    if($data && $node) {
      $paragraphs = [];
      $data_keys = array_keys($data);
      $last_component_name = end($data_keys);
      // For each component inside $data, we create a paragraph to set in the created node
      foreach ($data as $component_name => $component_value) {
        // Add c_text paragraph type before every component to be able to add a component description
        if(is_array($component_value) && array_key_exists('component_description', $component_value)) {
          $paragraphs[] = $this->createParagraph([
            'type' => 'c_text',
            'field_c_text' => [
                'value' => $component_value['component_description'].'<br><br>',
                'format' => 'rich_text'
            ],
          ]);
        }
        // Add the component
        $paragraphs[] = $this->createParagraph($component_value);
        // Add c_spacer paragraph type after every component to add a separation between components
        // Avoid put separator after last component
        if($component_name != $last_component_name) {
          $paragraphs[] = $this->createParagraph([
            'type' => 'c_spacer',
            'field_divider_height' => '20',
            'field_margin_bottom' => '80',
            'field_margin_top' => '100',
            'field_type_of_divider' => 'line_full_width',
          ]);
        }
      }
      if(!empty($paragraphs)) {
        $node->set('field_components', $paragraphs);
        try {
          $node->save();
        } catch (\Exception $e) {
          Drupal::logger('Sample Content')->notice('Cannot add sample data to node:' . $e->getMessage());
        }
      }
    }

  }

  /**
   * Parses YAML into a PHP value.
   *
   * @param $module
   * The name of the module that contains the Yaml file.
   * @param $file_path
   * The path of Yaml file.
   *
   * @return mixed
   * The YAML converted to a PHP value.
   */
  public function getYmlContent($module, $file_path) {
    $yaml = new SymfonyYaml();
    $module_path = drupal_get_path('module', $module);
    $content = file_get_contents(DRUPAL_ROOT . '/' . $module_path . $file_path);

    return $yaml->parse($content);
  }

  /**
   * Get a list of modules implementing a hook
   *
   * @param $hook_name
   * The name of hook to get implementations.
   *
   * @return array
   * Array with modules implementing the hook passed in params.
   */
  public function getHookImplementationsList($hook_name) {
    $modules_list = $this->moduleHandler()->getImplementations($hook_name);

    return  $modules_list;
  }

  /**
   * Get and return sample content.
   *
   * This function invokes all the modules that are implementing hook_ln_sample_content to retrieve the files
   * that contain the sample content.
   *
   * @return array
   * Array with sample content.
   */
  public function getSampleContent() {
    // List of modules implementing hook_ln_sample_content
    $sample_data=[];
    $modules_to_extract_content = $this->getHookImplementationsList('ln_sample_content');
    foreach ($modules_to_extract_content as $module) {
      // Invoke hook_ln_sample_content to get the paths of the content files
      if($file_path = $this->moduleHandler()->invoke($module, 'ln_sample_content')){
        // Get the path were the media files have been added to add it to returned data by this function
        $images_source = drupal_get_path('module', $module) . '/content/media/';
        // Every module invoked can have more than one content files, we need to parse data for each one
        foreach ($file_path as $key_path => $path) {
          $imported_data = $this->getYmlContent($module, $path);
          $imported_data['media_path'] = $images_source;
          $sample_data[$module.'_'.$key_path] = $imported_data;
        }
      }
    }
    return $sample_data;
  }

  /**
   * This function creates a media image entity from the sample image and returns the media entity id.
   *
   * @param $path
   * The media files path.
   * @param $data
   * Array with data about of type and fields to create a new paragraph.
   *
   * @return array
   * Array with file and media id's.
   */
  public function getSampleImage($path, $data) {
    $return_image = [
      'media_id' => NULL,
      'file_id' => NULL,
    ];
    // Create a file in the 'public:// ' stream.
    if(!empty($data['image_name']) && file_exists($path . $data['image_name'])){
      $file_content = file_get_contents($path . $data['image_name']);
      $file_data = file_save_data($file_content, "public://" . $data['image_name'], FileSystemInterface::EXISTS_REPLACE);
      $file = File::create([
        'filename' => $file_data->get('filename')->getValue()[0]['value'],
        'uri' => $file_data->get('uri')->getValue()[0]['value'],
        'uid' => \Drupal::currentUser()->id(),
        // This sets the file as permanent.
        'status' => TRUE,
      ]);
      $file->setPermanent();
      $file->save();
      $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('media','image');
      $field_name = (isset($fields['image']))
        ? 'image'
        : ((isset($fields['field_media_image'])) ? 'field_media_image' : 'field_media_image');
      // Create media entity with saved file.
      $image_media = Media::create([
        'bundle' => 'image',
        'uid' => \Drupal::currentUser()->id(),
        'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
        'name' => $data['image_title'],
        $field_name => [
          [
            'target_id' => $file->id(),
            'alt' => $data['image_title'],
            'title' => $data['image_title'],
          ],
        ],
      ]);
      $image_media->save();
      $return_image['media_id'] = $image_media->id();
      $return_image['file_id'] = $file->id();
    }

    return $return_image;
  }

  /**
   * This function creates a media document entity from the sample document and returns the media entity id.
   *
   * @param $path
   * The media files path.
   * @param $data
   * Array with data about of type and fields to create a new paragraph.
   *
   * @return array
   * Array with file and media id's.
   */
  public function getSampleDocument($path, $data) {
    // Create a file in the 'public:// ' stream.
    $file_content = file_get_contents($path . $data['document_name']);
    $file_data = file_save_data($file_content, "public://" . $data['document_name'], FileSystemInterface::EXISTS_REPLACE);
    $file = File::create([
      'filename' => $file_data->get('filename')->getValue()[0]['value'],
      'uri' => $file_data->get('uri')->getValue()[0]['value'],
      'uid' => \Drupal::currentUser()->id(),
      // This sets the file as permanent.
      'status' => TRUE,
    ]);
    $file->setPermanent();
    $file->save();
    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('media','document');
    $field_name = (isset($fields['field_document']))
      ? 'field_document'
      : ((isset($fields['field_media_document'])) ? 'field_media_document' : 'field_media_document');
    // Create media entity with saved file.
    $document_media = Media::create([
      'bundle' => 'document',
      'uid' => \Drupal::currentUser()->id(),
      'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      'name' => $data['document_title'],
      $field_name => $file->id(),
      'field_media_in_library' => TRUE,
    ]);
    $document_media->save();

    return ['media_id' => $document_media->id(), 'file_id' => $file->id()];
  }

  /**
   * Find taxonomy term by name
   *
   * @param null $name
   *  Term name
   *
   * @return int
   *  Term id or 0 if none.
   */
  public function getTidByName($name) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    return !empty($term) ? $term->id() : 0;
  }

  /**
   * Creates a taxonomy term
   *
   * @param $vid
   * The vocabulary id
   * @param $term_name
   * The term name to be created
   * @param $custom_fields
   * The term custom_fields details
   * @param $media_path
   * The term media path
   */
  public function createTaxomonyTerm($vid, $term_name, $custom_fields = array(), $media_path = '') {
    $term_data = [];
    $term_data['vid'] = $vid;
    $term_data['name'] = $term_name;
    if(!empty($custom_fields)) {
      foreach($custom_fields as $custom_field_name => $custom_field_value) {
        if(is_array($custom_field_value) && array_key_exists('image_name', $custom_field_value)) {
          $image_target_id = $this->getSampleImage($media_path, $custom_field_value);
          $term_data[$custom_field_name]['target_id'] = $image_target_id['media_id'];
        } else {
          $term_data[$custom_field_name]['value'] = $custom_field_value;
        }
      }
    }
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->create($term_data);
    $term->save();

    return $term->id();
  }

  /**
   * This function creates a node of content type passed in params
   *
   * @param $content_type
   * The content type machine_name
   *
   * @param $data
   * The data to be mapped in a new node.
   *
   * @param $media_folder
   * The folder that contains media files
   */
  public function createNodeFromYamlFile($content_type, $data, $media_folder) {
    switch ($content_type){
      case 'teaser' :
        $image = $this->getSampleImage($media_folder, $data['field_image']);
        $node = Node::create(['type' => $content_type]);
        $node->enforceIsNew();
        $node->set('title', $data['title']);
        $node->set('body', $data['body']);
        $node->set('field_buton_position', $data['field_buton_position']);
        $node->set('field_dsu_tags', $data['field_dsu_tags']);
        $node->set('field_teaser_link', $data['field_teaser_link']);
        $node->set('field_teaser_subtitle', $data['field_teaser_subtitle']);
        $node->set('field_teaser_video_url', $data['field_teaser_video_url']);
        $node->field_image = [
          'target_id' => $image['media_id'],
        ];

        $node->save();
    }
  }
  /**
   * Get Yaml content files path from a module
   *
   * @param $content_type
   * The module name
   *
   * @param $path
   * The path where files are located
   *
   * @return array
   * Returns an array with the path of the Yaml files
   */
  public function getYamlContentFilesPaths($module, $path) {
    $source = drupal_get_path('module', $module) . $path;
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $paths = $file_system->scanDirectory($source, '/demo.*\.(yml)$/');

    return $paths;
  }


  /**
   * Create paragraph with data passed in params.
   *
   * The received data is usually formatted to create paragraphs directly, but sometimes we need to create both,
   * nested paragraphs and nested paragraphs mixed with other field types. After isolating these cases,
   * we must create child fields to attach to the main paragraph.
   *
   * @param $data
   * Array with data about of type and fields to create a new paragraph.
   *
   * @return array
   * Array with structure to include the created paragraph into a node.
   *
   * @throws Drupal\Core\Entity\EntityStorageException
   * Thrown when the paragraph is not saved.
   */
  public function createParagraph($data) {
    $nested_paragraph_data = NULL;
    // Loop through the data to isolate not formatted fields
    foreach ($data as $ikey => $item) {
      // Case to nested paragraph
      if(is_array($item) && array_key_exists('paragraph', $item)) {
        $nested_paragraph_data['type'] = $data['type'];
        foreach ($item as $paragraph_data) {
          $nested_paragraph = [];
          foreach ($paragraph_data as $paragraph_value) {
            // For paragraphs it's necessary also to add the media_path
            // at this level so we can call "getSampleImage()" if it's necessary
            $paragraph_value['media_path'] = $data['media_path'];
            $nested_paragraph[] = $this->createParagraph($paragraph_value);
          }
          $nested_paragraph_data[$ikey] = $nested_paragraph;
        }
      }
      // Case to fields that needs to be related with an entity
      if(is_array($item) && array_key_exists('entity_relation', $item)) {
        $entity_type = $item['entity_relation'];
        $data[$ikey] = [];
        foreach ($entity_type as $key => $value) {
          $nids = \Drupal::entityQuery('node')->condition('type',$value)->execute();
          $data[$ikey][] = $nids[array_rand($nids)];
        }
      }
      // Case to fields that allow some links
      if(is_array($item) && array_key_exists('links', $item)) {
        $data[$ikey] = [];
        foreach ($item as $links) {
          foreach ($links as $link) {
            $data[$ikey][] = $link;
          }
        }
      }
      // Case to fields that needs to save term id
      if(is_array($item) && array_key_exists('tax_term_name', $item)) {
        $term_data = $item['tax_term_name'];
        $media_path = (array_key_exists('media_path', $data)) ? $data['media_path'] : '';
        $custom_fields = (array_key_exists('custom_field', $term_data)) ? $term_data['custom_field'] : [];
        $term_id = $this->getTidByName($term_data['name']) != 0 ? $this->getTidByName($term_data['name']) : $this->createTaxomonyTerm($term_data['vid'], $term_data['name'], $custom_fields, $media_path);
        $data[$ikey] = $term_id;
      }
      // Case to image fields
      if(is_array($item) && array_key_exists('image_name', $item)) {
        $image_target_id = $this->getSampleImage($data['media_path'], $item);
        // Save media_id in image type fields
        $item['target_id'] = $image_target_id['media_id'];
		// To video alternatie thumbnail field , save fid, not media_id.
		if($ikey == 'field_alternative_thumbnail'){
          $item['target_id'] = $image_target_id['file_id'];
		  $item['alt'] = $item['image_title'];
		  $item['title'] = $item['image_title'];
        }
      }
      // Case to document fields
      if(is_array($item) && array_key_exists('document_name', $item)) {
        $media_document_id = $this->getSampleDocument($data['media_path'], $item);
        $item['target_id'] = $media_document_id['media_id'];
       // $data['field_c_document_upload']['target_id'] = $media_document_id['file_id'];
        //$data['field_c_document_upload']['display'] = 1;
      }
      // Case to fields that needs to save target_id value
      if(is_array($item) && array_key_exists('target_id', $item)) {
        // Field in nested paragraph
        if(!is_null($nested_paragraph_data)){
          $nested_paragraph_data[$ikey] = $item;
        }
        // Field in parent paragraph
        else{
          $data[$ikey] = $item;
        }
      }
    }
    // If there are new processed fields, override $data is necessary to apply the changes
    $data = !is_null($nested_paragraph_data) ? array_merge($data, $nested_paragraph_data) : $data;

    try {
      $paragraph = Paragraph::create($data);
      $paragraph->isNew();
      $paragraph->save();
    } catch (\Exception $e) {
      Drupal::logger('Sample Content')->notice('Cannot create paragraph.' . $e->getMessage());
    }

    $paragraph_data = [
      'target_id'          => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];

    return $paragraph_data;
  }

}
