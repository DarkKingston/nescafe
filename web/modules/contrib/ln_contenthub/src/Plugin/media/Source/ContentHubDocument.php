<?php

namespace Drupal\ln_contenthub\Plugin\media\Source;

use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaTypeInterface;
use Drupal\media\MediaSourceBase;

/**
 * External document entity media source.
 *
 * @see \Drupal\core\file\FileInterface
 *
 * @MediaSource(
 *   id = "content_hub_document",
 *   label = @Translation("Content Hub Document"),
 *   description = @Translation("Use remote document from Content Hub."),
 *   allowed_field_types = {"file"}
 * )
 */
class ContentHubDocument extends MediaSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
   return [
      'id' => $this->t('Media ID'),
      'name' => $this->t('Media name'),
      'path' => $this->t('Media path'),
      'bytes' => $this->t('Media bytes'),
      'mimetype' => $this->t('Media mime type'),
      'thumbnail' => $this->t('Media thumbnail url'),
      'viewex' => $this->t('Media viewex url'),
      'downloadUrl' => $this->t('Media download url'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $media->get($this->configuration['source_field'])->entity;
    // If the source field is not required, it may be empty.
    if (!$file) {
      return parent::getMetadata($media, $attribute_name);
    }
    switch ($attribute_name) {
     case 'id':
        return $media->get($this->configuration['source_field'])->id ?: NULL;

      case 'name':
        return $media->get($this->configuration['source_field'])->name ?: NULL;

      case 'path':
        return $media->get($this->configuration['source_field'])->path ?: NULL;

      case 'bytes':
        return $media->get($this->configuration['source_field'])->bytes ?: NULL;

      case 'mimetype':
        return $media->get($this->configuration['source_field'])->mimetype ?: NULL;

      case 'thumbnail':
        return $media->get($this->configuration['source_field'])->thumbnail ?: NULL;

      case 'viewex':
        return $media->get($this->configuration['source_field'])->viewex ?: NULL;

      case 'downloadUrl':
        return $media->get($this->configuration['source_field'])->downloadUrl ?: NULL;

      case 'filesize':
        return $file->getSize();

      default:
        return parent::getMetadata($media, $attribute_name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type) {
    return parent::createSourceField($type)->set('settings', ['file_extensions' => 'txt doc docx pdf']);
  }

}
