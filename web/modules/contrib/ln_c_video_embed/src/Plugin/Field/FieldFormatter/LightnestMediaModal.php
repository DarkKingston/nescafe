<?php

namespace Drupal\ln_c_video_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Plugin implementation of the field formatter.
 *
 * @FieldFormatter(
 *   id = "lightnest_media_oembed_modal",
 *   label = @Translation("Oembed Video Modal"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   }
 * )
 */
class LightnestMediaModal extends FormatterBase implements ContainerFactoryPluginInterface {

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
   * The field formatter plugin instance for thumbnails.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $thumbnailFormatter;

  /**
   * The field formatterp plguin instance for videos.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $videoFormatter;

  /**
   * Allow us to attach colorbox settings to our element.
   *
   * @var \Drupal\colorbox\ElementAttachmentInterface
   */
  protected $colorboxAttachment;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Field\FormatterInterface $thumbnail_formatter
   *   The field formatter for thumbnails.
   * @param \Drupal\Core\Field\FormatterInterface $video_formatter
   *   The field formatter for videos.
   * @param \Drupal\colorbox\ElementAttachmentInterface|null $colorbox_attachment
   *   The colorbox attachment if colorbox is enabled.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, IFrameUrlHelper $iframe_url_helper, RendererInterface $renderer, FormatterInterface $thumbnail_formatter, FormatterInterface $video_formatter, $colorbox_attachment) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->logger = $logger_factory->get('media');
    $this->config = $config_factory->get('media.settings');
    $this->iFrameUrlHelper = $iframe_url_helper;
    $this->thumbnailFormatter = $thumbnail_formatter;
    $this->videoFormatter = $video_formatter;
    $this->renderer = $renderer;
    $this->colorboxAttachment = $colorbox_attachment;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter_manager = $container->get('plugin.manager.field.formatter');
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
        $container->get('renderer'),
        $formatter_manager->createInstance('lightnest_media_oembed_thumbnail', $configuration),
        $formatter_manager->createInstance('lightnest_media_oembed_video', $configuration),
        $container->get('colorbox.attachment')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $thumbnails = $this->thumbnailFormatter->viewElements($items, $langcode);
    $videos = $this->videoFormatter->viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      // Support responsive videos within the colorbox modal.
      if ($this->getSetting('responsive')) {
        $videos[$delta]['#attributes']['class'][] = 'video-oembed-responsive-modal';
        $videos[$delta]['#attributes']['style'] = sprintf('width:%dpx;', $this->getSetting('modal_max_width'));
      }

      $itemThumb = [$thumbnails[$delta]];
      // Add a play button.
      $itemThumb[] = [
        '#type' => 'html_tag',
        '#tag' => 'button',
        '#attributes' => [
          'class' => ['video-oembed-launch-modal-play'],
        ],
      ];

      $thumbVideoPlayWrapper = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['video-oembed-media-modal-play-wrapper'],
        ],
        'children' => $itemThumb,
      ];

      $element[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'data-video-oembed-media-modal' => (string) $this->renderer->render($videos[$delta]),
          'class' => ['video-oembed-launch-modal'],
        ],
        '#attached' => [
          'library' => [
            'ln_c_video_embed/colorbox',
            'ln_c_video_embed/responsive-video',
          ],
        ],
        // Ensure the cache context from the video formatter which was rendered
        // early still exists in the renderable array for this formatter.
        '#cache' => [
          'contexts' => ['user.permissions'],
        ],
        'children' => $thumbVideoPlayWrapper,
      ];
    }
    $this->colorboxAttachment->attach($element);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return LightnestMediaThumbnail::defaultSettings() + LightnestMediaVideo::defaultSettings() + [
      'modal_max_width' => '854',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element += $this->thumbnailFormatter->settingsForm([], $form_state);
    $element += $this->videoFormatter->settingsForm([], $form_state);
    $element['modal_max_width'] = [
      '#title' => $this->t('Maximum Width'),
      '#type' => 'number',
      '#description' => $this->t('The maximum size of the video opened in the Colorbox window in pixels. For smaller screen sizes, the video will scale.'),
      '#required' => TRUE,
      '#field_suffix' => 'px',
      '#size' => 20,
      '#states' => ['visible' => [[':input[name*="responsive"]' => ['checked' => TRUE]]]],
      '#default_value' => $this->getSetting('modal_max_width'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Thumbnail that launches a modal window.');
    $summary[] = implode(',', $this->videoFormatter->settingsSummary());
    $summary[] = implode(',', $this->thumbnailFormatter->settingsSummary());
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + $this->thumbnailFormatter->calculateDependencies() + $this->videoFormatter->calculateDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $parent = parent::onDependencyRemoval($dependencies);
    $thumbnail = $this->thumbnailFormatter->onDependencyRemoval($dependencies);
    $video = $this->videoFormatter->onDependencyRemoval($dependencies);
    $this->setSetting('image_style', $this->thumbnailFormatter->getSetting('image_style'));
    return $parent || $thumbnail || $video;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return \Drupal::moduleHandler()->moduleExists('colorbox');
  }

}
