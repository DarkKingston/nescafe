<?php

namespace Drupal\ln_c_video_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the oembed field formatter.
 *
 * @FieldFormatter(
 *   id = "lightnest_oembed_video_display",
 *   label = @Translation("Lightnest Oembed Video Display"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   },
 * )
 */
class LightnestOembedContent extends LightnestMediaModal implements ContainerFactoryPluginInterface {

  /**
   * The field formatter plugin instance for lazyload.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $lazyloadFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $plugin->getOembedLazyLoadInstance($container->get('plugin.manager.field.formatter')
      ->createInstance('lightnest_media_oembed_lazyload', $configuration));

    return $plugin;
  }

  /**
   * It returns lazyload fieldforammter instance.
   *
   * @param \Drupal\ln_c_video_embed\Plugin\Field\FieldFormatter\LightnestMediaLazyLoad $lazyload_formatter
   *   Formatter instance.
   *
   * @return $this
   */
  public function getOembedLazyLoadInstance(LightnestMediaLazyLoad $lazyload_formatter) {
    $this->lazyloadFormatter = $lazyload_formatter;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $value = $item->getParent()
        ->getEntity()
        ->get('field_show_in_lightbox')
        ->first();

      if ($value !== NULL && !$value->isEmpty()) {
        $lightbox_check = $value->getValue()['value'];
        if ($lightbox_check == 1) {
          $element[$delta] = parent::viewElements($items, $langcode);
        }
        else {
          $element[$delta] = $this->lazyloadFormatter->viewElements($items, $langcode);
        }
      }
    }
    return $element;
  }

}
