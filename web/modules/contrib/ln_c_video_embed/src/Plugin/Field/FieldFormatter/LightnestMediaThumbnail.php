<?php

namespace Drupal\ln_c_video_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\File\FileSystemInterface;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "lightnest_media_oembed_thumbnail",
 *   label = @Translation("Oembed Thumbnail"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   }
 * )
 */
class LightnestMediaThumbnail extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The oEmbed resource fetcher.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The media settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The iFrame URL helper service.
   *
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iFrameUrlHelper;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs an OEmbedFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, IFrameUrlHelper $iframe_url_helper, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, FileSystemInterface $file_system) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->logger = $logger_factory->get('media');
    $this->config = $config_factory->get('media.settings');
    $this->iFrameUrlHelper = $iframe_url_helper;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->mediaSource = $config_factory->get('plugin.manager.media.source');
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $plugin_id,
        $plugin_definition,
        $configuration['field_definition'],
        $configuration['settings'],
        $configuration['label'],
        $configuration['view_mode'],
        $configuration['third_party_settings'],
        $container->get('media.oembed.resource_fetcher'),
        $container->get('media.oembed.url_resolver'),
        $container->get('logger.factory'),
        $container->get('config.factory'),
        $container->get('media.oembed.iframe_url_helper'),
        $container->get('entity_type.manager'), $container->get('entity_field.manager'),
        $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $url = '';
    /** @var \Drupal\media\Plugin\media\Source\OEmbedInterface $source */
    $source = $items->getEntity()->getSource();

    foreach ($items as $delta => $item) {
      $main_property = $item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
      $value = $item->{$main_property};

      if (empty($value)) {
        continue;
      }

      try {
        $resource_url = $this->urlResolver->getResourceUrl($value);
        $resource = $this->resourceFetcher->fetchResource($resource_url);
      }
      catch (ResourceException $exception) {
        $this->logger->error("Could not retrieve the remote URL (@url).", ['@url' => $value]);
        continue;
      }

      if (!empty($this->getSetting('thumbnail_field'))) {
        $thumbnail_field = $item->getParent()
          ->getEntity()
          ->get($this->getSetting('thumbnail_field'));
        if (!$thumbnail_field->isEmpty()) {
          if ($thumbnail_field->getFieldDefinition()->getType() == 'image') {
            $value = $thumbnail_field->first();
            if ($value !== NULL && !$value->isEmpty()) {
              $title = $value->getValue()['title'];
              $alt = $value->getValue()['alt'];
              $file = $this->entityTypeManager->getStorage('file')
                ->load($value->getValue()['target_id']);
              /** @var \Drupal\file\Entity\File $thumbnail_uri */
              $thumbnail_uri = $file->getFileUri();
            }
          }
          else {
            $thumbnail_image = $thumbnail_field->entity->image->entity;
            $value = $thumbnail_field->entity->get('image')
              ->first();
            if ($value !== NULL && !$value->isEmpty()) {
              $title = $value->getValue()['title'];
              $alt = $value->getValue()['alt'];
              $thumbnail_uri = $thumbnail_image->uri->value;
            }
          }
        }
      }

      if (!isset($thumbnail_uri)) {
        $thumbnail_uri = '';

        // If there is no remote thumbnail,
        // there's nothing for us to fetch here.
        $remote_thumbnail_url = $resource->getThumbnailUrl();
        if (!$remote_thumbnail_url) {
          return NULL;
        }

        // Ensure that we can write to the local directory where thumbnails are
        // stored.
        $configuration = $source->getConfiguration();
        $directory = $configuration['thumbnails_directory'];

        // The local thumbnail doesn't exist yet, so try to download it. First,
        // ensure that the destination directory is writable, and if it's not,
        // log an error and bail out.
        if (!$this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
          $this->logger->warning('Could not prepare thumbnail destination directory @dir for oEmbed media.', [
            '@dir' => $directory,
          ]);
          return NULL;
        }

        // The local filename of the thumbnail is always,
        // a hash of its remote URL.
        // If a file with that name already exists in the thumbnails directory,
        // regardless of its extension, return its URI.
        $remote_thumbnail_url = $remote_thumbnail_url->toString();
        $hash = Crypt::hashBase64($remote_thumbnail_url);
        $files = $this->fileSystem->scanDirectory($directory, "/^$hash\..*/");
        if (count($files) > 0) {
          $thumbnail_uri = reset($files)->uri;
        }

        $title = '';
        $alt = '';
      }

      if ($this->getSetting('image_type') === 'responsive') {
        $element[$delta] = $this->renderThumbnail($this->getSetting('responsive_image_style'), $url, $thumbnail_uri, $title, $alt, TRUE);
      }
      else {
        $element[$delta] = $this->renderThumbnail($this->getSetting('image_style'), $url, $thumbnail_uri, $title, $alt, FALSE);
      }

    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_type'             => 'thumbnail',
      'image_style'            => '',
      'responsive_image_style' => '',
      'thumbnail_field'        => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['image_type'] = [
      '#title'         => $this->t('Image Type'),
      '#type'          => 'select',
      '#default_value' => $this->getSetting('image_type'),
      '#options'       => [
        'thumbnail'  => $this->t('Thumbnail image style'),
        'responsive' => $this->t('Responsive image style'),
      ],
    ];

    $element['image_style'] = [
      '#title'         => $this->t('Image Style'),
      '#type'          => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#required'      => FALSE,
      '#options'       => image_style_options(),
      '#states'        => [
        'visible' => [
          'select[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][image_type]"]' => [
            'value' => 'thumbnail',
          ],
        ],
      ],
    ];

    $options = [];
    $entity_type_id = $this->fieldDefinition->getTargetEntityTypeId();
    $bundle = $this->fieldDefinition->getTargetBundle();
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    if (!empty($fields)) {
      foreach ($fields as $field) {
        if ($field instanceof FieldConfig && ($field->getType() === 'entity_reference' || $field->getType() === 'image')) {
          $options[$field->getName()] = $field->getLabel();
        }
      }
    }

    $element['thumbnail_field'] = [
      '#title'         => $this->t('Image thumbnail field'),
      '#type'          => 'select',
      '#description'   => $this->t('Optionally select an image field in this entity which will be used as thumbnail.'),
      '#default_value' => $this->getSetting('thumbnail_field'),
      '#options'       => $options,
      '#empty_option'  => $this->t('- None -'),
    ];

    $responsive_image_options = [];
    $responsive_image_styles = $this->entityTypeManager->getStorage('responsive_image_style')
      ->loadMultiple();
    if ($responsive_image_styles && !empty($responsive_image_styles)) {
      foreach ($responsive_image_styles as $machine_name => $responsive_image_style) {
        if ($responsive_image_style->hasImageStyleMappings()) {
          $responsive_image_options[$machine_name] = $responsive_image_style->label();
        }
      }
    }
    $element['responsive_image_style'] = [
      '#title'         => $this->t('Responsive Image Style'),
      '#type'          => 'select',
      '#default_value' => $this->getSetting('responsive_image_style'),
      '#required'      => FALSE,
      '#options'       => $responsive_image_options,
      '#states'        => [
        'visible' => [
          'select[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][image_type]"]' => [
            'value' => 'responsive',
          ],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $thumbnail_field = '';

    $image_type = $this->getSetting('image_type');
    if ($image_type != 'responsive') {
      $style = $this->getSetting('image_style') ? $this->t('Thumbnail:') . ' ' . $this->getSetting('image_style') : $this->t('no image style');
    }
    else {
      $style = $this->getSetting('responsive_image_style') ? $this->t('Responsive image:') . ' ' . $this->getSetting('responsive_image_style') : $this->t('no responsive image style');
    }
    if (!empty($this->getSetting('thumbnail_field'))) {
      $thumbnail_field = $this->t('Thumbnail field: @thumbnail_field', [
        '@thumbnail_field' => $this->getSetting('thumbnail_field'),
      ]);
    }
    $summary[] = $this->t('Video thumbnail (@style). @thumbnail_field', [
      '@style'           => $style,
      '@thumbnail_field' => $thumbnail_field,
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $style_id = $this->getSetting('image_style');
    if ($style_id && $style = ImageStyle::load($style_id)) {
      $dependencies[$style->getConfigDependencyKey()][] = $style->getConfigDependencyName();
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = parent::onDependencyRemoval($dependencies);
    $style_id = $this->getSetting('image_style');
    if ($style_id && $style = ImageStyle::load($style_id)) {
      if (!empty($dependencies[$style->getConfigDependencyKey()][$style->getConfigDependencyName()])) {
        $replacement_id = $this->entityTypeManager->getStorage('responsive_image_style')
          ->getReplacementId($style_id);
        // If a valid replacement has been provided in the storage, replace the
        // image style with the replacement and signal that the formatter plugin
        // settings were updated.
        if ($replacement_id && ImageStyle::load($replacement_id)) {
          $this->setSetting('image_style', $replacement_id);
          $changed = TRUE;
        }
      }
    }
    return $changed;
  }

  /**
   * Render a thumbnail.
   *
   * @param string $image_style
   *   The quality of the thumbnail to render.
   * @param string $url
   *   Link for image.
   * @param string $thumbnail_uri
   *   The thumbnail uri.
   * @param string $title
   *   Title for image.
   * @param string $alt
   *   Alternate text for image.
   * @param bool $responsive
   *   Whether we are using a responsive image style.
   *
   * @return array
   *   A renderable array of a thumbnail.
   */
  public function renderThumbnail($image_style, $url, $thumbnail_uri, $title, $alt, $responsive = FALSE) {
    $output = [
      '#theme' => 'image',
      '#uri'   => $thumbnail_uri,
      '#title' => $title,
      '#alt'   => $alt,
    ];

    if (!empty($image_style)) {
      if ($responsive) {
        $output['#theme'] = 'responsive_image';
        $output['#responsive_image_style_id'] = $image_style;
      }
      else {
        $output['#theme'] = 'image_style';
        $output['#style_name'] = $image_style;
      }
    }

    return $output;
  }

}
