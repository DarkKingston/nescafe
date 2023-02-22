<?php

namespace Drupal\ln_contenthub\Plugin\EntityBrowser\Widget;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ln_contenthub\ContentHubInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\entity_browser\WidgetValidationManager;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\Token;

/**
 * Integrates with ContentHub library.
 *
 * @EntityBrowserWidget(
 *   id = "ln_contenthub_document_widget",
 *   label = @Translation("Content Hub Document Widget"),
 *   description = @Translation("Integrates with Content Hub service")
 * )
 */
class ContentHubDocumentWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin implementation definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * The Content Hub service.
   *
   * @var \Drupal\ln_contenthub\ContentHubInterface
   */
  protected $contentHubService;

  /**
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The Per page record.
   */
  protected $per_page_record = 20;

  /**
   * The page index.
   */
  protected $page_index = 0;

  /**
   * Constructs a new View object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin
   *   instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\entity_browser\WidgetValidationManager $validation_manager
   *   The Widget Validation Manager service.
   * @param \Drupal\ln_contenthub\ContentHubInterface $content_hub_service
   *   Media ContentHubImages services service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger channel factory.
   * @param \Drupal\Core\Utility\Token $token
   *   Token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager, WidgetValidationManager $validation_manager, ContentHubInterface $content_hub_service, FileSystemInterface $file_system, LoggerChannelFactoryInterface $logger_channel_factory, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_type_manager, $validation_manager);
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->contentHubService = $content_hub_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->logger = $logger_channel_factory;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_browser.widget_validation'),
      $container->get('ln_contenthub.ln_contenthub_services'),
      $container->get('file_system'),
      $container->get('logger.factory'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'media_type' => '',
        'server_uri' => NULL,
        'username' => NULL,
        'password' => NULL,
        'api_key' => NULL,
        'upload_location' => 'public://contenthub-media/[date:custom:Y]-[date:custom:m]',
        'multiple' => TRUE,
        'submit_text' => $this->t('Place'),
        'extensions' => 'doc docx xls xlsx pdf ppt',
      ] + parent::defaultConfiguration();
  }

  /**
   * Search default values.
   *
   * @return array
   *   Array of defaults.
   */
  public function getSearchDefaults() {
    return [
      'ln_contenthub_search_keyword' => '',
	  'ln_contenthub_extra_param' => [],
      'ln_contenthub_pageindex' => $this->page_index,
      'ln_contenthub_pagesize' => $this->per_page_record,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);
    $form['#tree'] = TRUE;

    // Call buildSearch() to make a call without params.
    $this->buildSearch($form, $form_state);

    return $form;
  }

  /**
   * Build search form part of the browser form.
   *
   * @param array $form
   *   The widget form form.
   */
  protected function buildSearch(&$form, FormStateInterface $form_state) {
    $search_params = $this->getCurrentParams($form_state);
	$config = \Drupal::config('ln_contenthub.settings');

    $form['search'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Search'),
    ];

    // Keyword search.
    $form['search']['search_keyword'] = [
      '#type' => 'search',
      '#title' => $this->t('Keyword'),
      '#description' => $this->t('Search on file name or asset id.'),
      '#default_value' => $search_params['ln_contenthub_search_keyword'],
    ];
	
	$form['search']['sub_brand'] = [
      '#type' => 'search',
      '#title' => $this->t('Sub brand'),
      '#default_value' => isset($search_params['ln_contenthub_extra_param']['ln_contenthub_sub_brand']) ?
        $search_params['ln_contenthub_extra_param']['ln_contenthub_sub_brand'] : '',
      '#access' => ($config->get('ln_contenthub_sub_brand') == 1 ) ? TRUE : FALSE,
    ];

    $form['search']['product_category'] = [
      '#type' => 'search',
      '#title' => $this->t('Product category'),
      '#default_value' => isset($search_params['ln_contenthub_extra_param']['ln_contenthub_product_category'])
        ? $search_params['ln_contenthub_extra_param']['ln_contenthub_product_category'] : '',
      '#access' => ($config->get('ln_contenthub_product_category') == 1 ) ? TRUE : FALSE,
    ];
	
    $search_results = $this->contentHubService->search($search_params['ln_contenthub_search_keyword'], ($search_params['ln_contenthub_pagesize'] + 1), $search_params['ln_contenthub_pageindex'], 'Document', $search_params['ln_contenthub_extra_param']);

    if ($search_results) {
      $form['pageindex'] = [
        '#type' => 'hidden',
        '#default_value' => $search_params['ln_contenthub_pageindex'],
      ];
      $form['results'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['ln_contenthub-search-results']],
        '#attached' => [
          'library' => ['ln_contenthub/integration'],
        ],
      ];

      if (isset($search_results) && count($search_results) > 0) {
        $record_count = count($search_results);
        // Get 1 record extra for laod more functionality and remove from display.
        if ($record_count > $search_params['ln_contenthub_pagesize']) {
          array_pop($search_results);
        }
        
        foreach ($search_results as $key => $result) {
          if (isset($result->previews)) {
            $src = $result->previews->thumbnail . '&apikey=' . $config->get('ln_contenthub_api_key');
            $title = $result->name;

            $form['results'][$key] = [
              '#type' => 'container',
              '#attributes' => ['class' => ['ln_contenthub-grid-item']],
              'checkbox' => [
                '#type' => 'checkbox',
                '#return_value' => json_encode($result),
              ],
              'preview' => [
                '#theme' => 'ln_contenthub',
                '#src' => $src,
                '#title' => isset($title) ? $title : $this->t('No title'),
              ],
            ];
          }
        }
      }
      if ($record_count > $search_params['ln_contenthub_pagesize']) {
        $form['content_load_more'] = [
          '#type' => 'submit',
          '#value' => $this->t('Load More'),
          '#submit' => [[$this, 'contentHubPagination']],
          '#attributes' => ['class' => ['contenthub-load-more']],
        ];
      }
    }
    else {
      $form['results'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['ln_contenthub-search-no-results']],
        '#attached' => [
          'library' => ['ln_contenthub/integration'],
        ],
      ];
    }

    $form['search']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#submit' => [[$this, 'searchSubmit']],
      '#prefix' => '<div class="search_submit">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * Search form submit callback.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function searchSubmit(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues()['widget']['search'];
    $form_state->set('ln_contenthub', ['ln_contenthub_search_keyword' => $values['search_keyword']]);
	 $form_state->set('ln_contenthub_extra_param',
      [
        'ln_contenthub_sub_brand' => $values['sub_brand'],
        'ln_contenthub_product_category' => $values['product_category'],
      ]
    );
    $form_state->setRebuild();
  }

  /**
   * Utility function that gets ContentHub search params and entity browser
   * params.
   *
   * @return array
   *   Array of search parameters.
   */
  protected function getCurrentParams(FormStateInterface $form_state) {
    $current = $form_state->get('ln_contenthub') ?: [];
    $extra_param['ln_contenthub_extra_param'] = $form_state->get('ln_contenthub_extra_param') ?: [];
    $user_input = array_merge($extra_param, $current);
    return array_merge($this->getSearchDefaults(), $user_input);
  }

  /**
   * Returns the media type that this widget creates.
   *
   * @return \Drupal\media\MediaTypeInterface
   *   Media type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getType() {
    // Load param is the machine name of media type.
    return $this->entityTypeManager
      ->getStorage('media_type')
      ->load('content_hub_document');
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    // Depend on the media type this widget creates.
    $media_type = $this->getType();
    $dependencies[$media_type->getConfigDependencyKey()][] = $media_type->getConfigDependencyName();
    $dependencies['module'][] = 'media';

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();
    $trigger = $form_state->getTriggeringElement()['#id'];
    $selected = [];
    // We don't want to validate when we are using Search or Clear buttons.
    if ($trigger != 'edit-widget-search-submit' && $trigger != 'edit-widget-search-clear') {
      if (isset($user_input['widget']['results'])) {
        foreach ($user_input['widget']['results'] as $result) {
          if (!empty($result['checkbox'])) {
            $selected[] = $result['checkbox'];
          }
        }
      }

      // If there weren't any errors set, run the normal validators.
      if (empty($form_state->getErrors())) {
        $form_state->set('ln_contenthub_selected', $selected);
        parent::validate($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    $media_type = $this->getType();
    $destination = $this->getUploadLocation();
	
    $documents = [];
    // Prepare destination.
    if (!file_exists($destination)) {
      $this->fileSystem->mkdir($destination, NULL, TRUE);
    }

    if ($selected_documents = $form_state->get('ln_contenthub_selected', [])) {
      foreach ($selected_documents as $selected_document) {
        $document = json_decode($selected_document);

        $intellectual_property_rights = $this->contentHubService->checkIntellectualPropertyRights($document->id);
        $ipr = !is_null($intellectual_property_rights['ipr']) ? $intellectual_property_rights['ipr'] : 'IPR data not informed.';
        $ipr_expiration_date = !is_null($intellectual_property_rights['ipr_expiration_date']) ? $intellectual_property_rights['ipr_expiration_date'] : '';
        try {
          $config = \Drupal::config('ln_contenthub.settings');
          // Download image from api key.
          $download_url = $document->previews->downloadUrl . '&apikey=' . $config->get('ln_contenthub_api_key');
          $image_name = str_replace(' ', '_', $document->name);
          $image_uri = file_create_url("public://") . $image_name;
          $local = $this->saveFileDataInFile($image_uri, $destination, $download_url);
          // Download image using Guzzle HTTP client.
          $file = File::create([
            'filename' => $document->name,
            'uri' => $local,
            'uid' => '1',
            // This sets the file as permanent.
            'status' => TRUE,
          ]);
          $file->setPermanent();
          $file->save();

          $entity = $this->entityTypeManager->getStorage('media')->create([
		  
            'bundle' => $media_type->id(),
            $media_type->getSource()
              ->getConfiguration()['source_field'] => $file,
            'uid' => '1',
            'status' => TRUE,
            'type' => $media_type->getSource()->getPluginId(),
            'name' => $document->name,
            'field_media_ln_contenthub_docume' => [
              'target_id' => $file->id(),
            ],
            'field_media_ln_contenthub_id' => $document->id,
            'field_media_ln_contenthub_downl' => $document->previews->downloadUrl,
            'field_media_ln_conthub_thumbnail' => $document->previews->thumbnail,
            'field_media_ln_contenthub_viewex' => $document->previews->viewex,
            'field_media_ln_contenthub_mime_t' => $document->mimeType,
            'field_media_ln_contenthub_ipr' => $ipr,
            'field_media_ln_contenthub_ipr_ex' => $ipr_expiration_date,
            'field_media_ln_contenthub_bytes' => $document->bytes,
            'field_media_ln_content_last_mod' => $document->lastModified,
            'field_media_ln_contenthub_name' => $document->name,
            'field_media_ln_contenthub_path' => $document->path,
          ]);

          $documents[] = $entity;

        } catch (\Exception $e) {
          $this->logger->get('ln_contenthub')
            ->error('Unable to generate entity due to: %e', ['%e' => $e->getMessage()]);
        }

      }
	  
    }
	
    // Pass the prepared entities to submit.
    $form_state->set('ln_contenthub_prepared', $documents);
    return $documents;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {

    $prepared_entities = $form_state->get('ln_contenthub_prepared', []);

    foreach ($prepared_entities as $key => $prepared_entity) {
      $prepared_entities[$key]->save();
    }

    $this->selectEntities($prepared_entities, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['media_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Media type'),
      '#required' => TRUE,
      '#disabled' => TRUE,
      '#description' => $this->t('The type of media entity to create from the uploaded file(s).'),
    ];

    $media_type = $this->getType();
    if ($media_type) {
      $form['media_type']['#default_value'] = $media_type->id();
      $form['media_type']['#options'][$media_type->id()] = 'Content Hub';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues()['table'][$this->uuid()]['form'];
    $this->configuration['submit_text'] = $values['submit_text'];
    $this->configuration['media_type'] = $values['media_type'];
  }

  /**
   * Gets upload location.
   *
   * @return string
   *   Destination folder URI.
   */
  protected function getUploadLocation() {
    return $this->token->replace($this->configuration['upload_location']);
  }

  /**
   * Save file in system and return file path.
   *
   * @param string $file_name
   *    File name.
   * @param string $destination
   *    Upload destination path.
   * @param string $file_url
   *    Download file URL.
   *
   * @return string
   *    Upload file URI.
   */
  protected function saveFileDataInFile($file_name, $destination, $file_url) {
    $content = (string) \Drupal::httpClient()
      ->get($file_url)
      ->getBody();
    $image_uri = file_create_url("public://") . $file_name;
    $parsed_url = parse_url($image_uri);
    if (is_dir(realpath($destination))) {
		print 'inside';exit;
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
      $this->logger->get('ln_contenthub')
        ->error('Unable to download file from content hub due to: %e', ['%e' => $exception->getMessage()]);
      return FALSE;
    }
    return $local;
  }

  /**
   * Pagination record for contenthub.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function contentHubPagination(array $form, FormStateInterface $form_state) {
    $page_index = $form_state->get('pageindex');
    $record = count($form_state->getValues()['widget']['results']);
    $values = $form_state->getValues()['widget']['search'];
    $form_state->set('ln_contenthub', [
        'ln_contenthub_search_keyword' => $values['search_keyword'],
        'ln_contenthub_pageindex' => ($page_index + 1),
        'ln_contenthub_pagesize' => ($record + $this->per_page_record),
      ]
    );
	$form_state->set('ln_contenthub_extra_param',
      [
        'ln_contenthub_sub_brand' => $values['sub_brand'],
        'ln_contenthub_product_category' => $values['product_category'],
      ]
    );
    $form_state->setRebuild();
  }
}
