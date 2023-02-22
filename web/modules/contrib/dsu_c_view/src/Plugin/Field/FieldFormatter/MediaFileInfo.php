<?php

namespace Drupal\dsu_c_view\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "media_file_info",
 *   label = @Translation("Media File Info"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MediaFileInfo extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
             'custom_label' => '',
             'link_file'    => TRUE,
           ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays Media file information.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    // Default link options.
    $default_options = [
      'attributes' => [
        'target' => '_blank',
        'class'  => 'media-file-info',
      ],
    ];

    foreach ($items as $delta => $item) {

      $entity = $item->entity;
      $custom_label = $this->getSetting('custom_label') ?: $entity->getName();

      // Skip processing if entity type is not media.
      if ($entity->getEntityTypeId() != 'media') {
        $element[$delta] = ['#markup' => $this->t('not supported entity type.')];
        continue;
      }
      else {
        switch ($entity->bundle()) {
          case 'audio_file':
            $file_entity = $entity->get('field_media_audio_file')->entity;
            break;

          case 'document':
            $file_entity = $entity->get('field_document')->entity;
            break;

          case 'video':
            $markup = $custom_label;

            // Get the parent entity.
            $node = $item->getParent()->getEntity();

            // Add the custom label with video duration.
            if ($node->hasField('field_read_time')) {
              $readTime = $node->get('field_read_time')->getValue();
              if (!empty($readTime[0]['value'])) {
                $markup .= '<span class="media">' . $readTime[0]['value'] . '</span>';
              }
            }
            $url = $entity->get('field_media_video_embed_field')->value;
            $url = Url::fromUri($url, $default_options);
            break;

          case 'video_file':
            $file_entity = $entity->get('field_media_video_file')->entity;
            break;
        }
      }

      // Show filesize if available.
      if (!empty($file_entity)) {
        $markup = $custom_label . ' <span class="media">' . $this->prettyFileSize($file_entity->getSize(), 0) . '</span> <span class="ext"></span>';
      }

      // Link the label to source File/Url.
      if ($this->getSetting('link_file')) {
        if (!empty($file_entity)) {
          $url = Url::fromUri(file_create_url($file_entity->getFileUri()));
        }
        $markup = '<a href="' . $url->toString() . '" target="_blank" class="media-file-info">' . $markup . '</a>';
      }

      $element[$delta] = ['#markup' => $markup];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function prettyFileSize($bytes, $decimals = 2) {
    $size = ['B', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['custom_label'] = [
      '#title'         => $this->t('Custom Label'),
      '#description'   => $this->t('If empty, then default name field value will be used as label.'),
      '#type'          => 'textfield',
      '#size'          => 60,
      '#default_value' => $this->getSetting('custom_label'),
    ];
    $element['link_file'] = [
      '#title'         => $this->t('Link custom label to the file'),
      '#description'   => $this->t('Check this to link the label to source File/Url.'),
      '#type'          => 'checkbox',
      '#default_value' => $this->getSetting('link_file'),
    ];

    return $element;
  }

}
