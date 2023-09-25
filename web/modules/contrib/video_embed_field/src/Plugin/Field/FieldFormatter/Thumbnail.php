<?php

namespace Drupal\video_embed_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "video_embed_field_thumbnail",
 *   label = @Translation("Thumbnail"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class Thumbnail extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The embed provider plugin manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

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
   * Class constant for linking to content.
   */
  const LINK_CONTENT = 'content';

  /**
   * Class constant for linking to the provider URL.
   */
  const LINK_PROVIDER = 'provider';

  /**
   * Constructs a new instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
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
   *   Third party settings.
   * @param \Drupal\video_embed_field\ProviderManagerInterface $provider_manager
   *   The video embed provider manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, $settings, $label, $view_mode, $third_party_settings, ProviderManagerInterface $provider_manager, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->providerManager = $provider_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
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
      $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get('video_embed_field.provider_manager'), $container->get('entity_type.manager'), $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $provider = $this->providerManager->loadProviderFromInput($item->value);

      if (!$provider) {
        $element[$delta] = ['#theme' => 'video_embed_field_missing_provider'];
      }
      else {
        $url = FALSE;
        if ($this->getSetting('link_image_to') == static::LINK_CONTENT) {
          $url = $items->getEntity()->toUrl();
        }
        elseif ($this->getSetting('link_image_to') == static::LINK_PROVIDER) {
          $url = Url::fromUri($item->value);
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
          $provider->downloadThumbnail();
          $thumbnail_uri = $provider->getLocalThumbnailUri();
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
      'link_image_to'          => '',
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
    $element['link_image_to'] = [
      '#title' => $this->t('Link image to'),
      '#type' => 'select',
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $this->getSetting('link_image_to'),
      '#options' => [
        static::LINK_CONTENT => $this->t('Content'),
        static::LINK_PROVIDER => $this->t('Provider URL'),
      ],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $linked = '';
    $thumbnail_field = '';

    if (!empty($this->getSetting('link_image_to'))) {
      $linked = $this->getSetting('link_image_to') == static::LINK_CONTENT ? $this->t(', linked to content') : $this->t(', linked to provider');
    }
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
    $summary[] = $this->t('Video thumbnail (@style@linked). @thumbnail_field', [
      '@style'           => $style,
      '@linked'          => $linked,
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
   * @param string $link_url
   *   Where the thumbnail should be linked to.
   * @param string $thumbnail_uri
   *   The thumbnail uri.
   * @param $title
   * @param $alt
   * @param bool $responsive
   *   Whether we are using a responsive image style.
   *
   * @return array
   *   A renderable array of a thumbnail.
   */
  public function renderThumbnail($image_style, $link_url, $thumbnail_uri, $title, $alt, $responsive = FALSE) {
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

    if ($link_url) {
      $output = [
        '#type'  => 'link',
        '#title' => $output,
        '#url'   => $link_url,
      ];
    }
    return $output;
  }
}
