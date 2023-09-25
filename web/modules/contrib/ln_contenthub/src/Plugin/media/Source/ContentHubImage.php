<?php

namespace Drupal\ln_contenthub\Plugin\media\Source;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\ln_contenthub\ContentHubInterface;
use GuzzleHttp\Client;
use Drupal\media\MediaTypeInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;

/**
 * External image entity media source.
 *
 * @see \Drupal\core\Image\ImageInterface
 *
 * @MediaSource(
 *   id = "content_hub_image",
 *   label = @Translation("Content Hub Image"),
 *   description = @Translation("Use remote images from Content Hub."),
 *   allowed_field_types = {"image"},
 * )
 */
class ContentHubImage extends MediaSourceBase {

  /**
   * The ln_contenthub fetcher service.
   *
   * @var \Drupal\ln_contenthub\ContentHubServices
   */
  protected $contentHubServices;

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type plugin manager service.
   * @param \Drupal\ln_contenthub\ContentHubInterface $ln_contenthub_services
   *   Content Hub service.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \GuzzleHttp\Client $httpClient
   *   Guzzle client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory, FieldTypePluginManagerInterface $field_type_manager, ContentHubInterface $ln_contenthub_services, ImageFactory $image_factory, FileSystemInterface $file_system, Client $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $field_type_manager, $config_factory);
    $this->contentHubServices = $ln_contenthub_services;
    $this->imageFactory = $image_factory;
    $this->fileSystem = $file_system;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('ln_contenthub.ln_contenthub_services'),
      $container->get('image.factory'),
      $container->get('file_system'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [
      'id' => $this->t('Media ID'),
      'name' => $this->t('Media name'),
      'path' => $this->t('Media path'),
      'height' => $this->t('Media height'),
      'width' => $this->t('Media width'),
      'bytes' => $this->t('Media bytes'),
      'lastModified' => $this->t('Media last modification'),
      'mimeType' => $this->t('Media mime type'),
      'thumbnail' => $this->t('Media thumbnail url'),
      'viewex' => $this->t('Media viewex url'),
      'downloadUrl' => $this->t('Media download url'),
      'ipr' => $this->t('Intellectual Property Rights'),
      'ipr_expiration_date' => $this->t('Intellectual Property Rights expiration date'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    // Get the file and image data.
    /** @var \Drupal\file\FileInterface $file */
    $file = $media->get($this->configuration['source_field'])->entity;

    // If the source field is not required, it may be empty.
    if (!$file) {
      return parent::getMetadata($media, $attribute_name);
    }

    $uri = $file->getFileUri();
    $image = $this->imageFactory->get($uri);

    switch ($attribute_name) {
      case 'id':
        return $media->get($this->configuration['source_field'])->id ?: NULL;

      case 'name':
        return $media->getName() ?: NULL;

      case 'path':
        return $media->get($this->configuration['source_field'])->path ?: NULL;

      case 'height':
        return $image->getHeight() ?: NULL;

      case 'width':
        return $image->getWidth() ?: NULL;

      case 'bytes':
        return $media->get($this->configuration['source_field'])->bytes ?: NULL;

      case 'lastModified':
        return $media->get($this->configuration['source_field'])->last_modified ?: NULL;

      case 'mimeType':
        return $media->get($this->configuration['source_field'])->mime_type ?: NULL;

      case 'thumbnail':
        return $media->get($this->configuration['source_field'])->thumbnail ?: NULL;

      case 'viewex':
        return $media->get($this->configuration['source_field'])->viewex ?: NULL;

      case 'thumbnail_uri':
        return $uri;

      case 'thumbnail_alt_value':
        return $media->getName() ?: NULL;

      case 'downloadUrl':
        return $media->get($this->configuration['source_field'])->host_image ?: NULL;

      case 'ipr':
        return $media->get($this->configuration['source_field'])->ipr ?: NULL;

      case 'ipr_expiration_date':
        return $media->get($this->configuration['source_field'])->ipr_expiration_date ?: NULL;

      case 'thumbnail_alt_value':
        return $media->getName() ?: NULL;
    }

    return parent::getMetadata($media, $attribute_name);
  }

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type) {
    /** @var \Drupal\field\FieldConfigInterface $field */
    $field = parent::createSourceField($type);

    // Reset the field to its default settings so that we don't inherit the
    // settings from the parent class' source field.
    $settings = $this->fieldTypeManager->getDefaultFieldSettings($field->getType());

    return $field->set('settings', $settings);
  }

}
