<?php

namespace Drupal\dsu_c_externalvideo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Colorbox;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\LazyLoad;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "lightnest_video_embed_field_colorbox",
 *   label = @Translation("Lightnest Colorbox Modal"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class LightnestVideoEmbedFormatter extends Colorbox implements ContainerFactoryPluginInterface {

  protected $lazyloadFormatter;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return \Drupal\Core\Field\FormatterBase|\Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\dsu_c_externalvideo\Plugin\Field\FieldFormatter\LightnestVideoEmbedFormatter|\Drupal\video_embed_field\Plugin\Field\FieldFormatter\Colorbox|static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $plugin->getLazyLoadInstance($container->get('plugin.manager.field.formatter')
      ->createInstance('video_embed_field_lazyload', $configuration));

    return $plugin;
  }

  /**
   * @param \Drupal\video_embed_field\Plugin\Field\FieldFormatter\LazyLoad $lazyload_formatter
   *
   * @return $this
   */
  public function getLazyLoadInstance(LazyLoad $lazyload_formatter) {
    $this->lazyloadFormatter = $lazyload_formatter;
    return $this;
  }

  /**
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param string $langcode
   *
   * @return array
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
          $itemThumb = [$element[$delta][0]['children']];
          // Add a play button.
          $itemThumb[] = [
            '#type'       => 'html_tag',
            '#tag'        => 'button',
            '#attributes' => [
              'class'    => ['video-embed-field-lazy-play'],
              'tabindex' => [0],
            ],
          ];
          $element[$delta][0]['children'] = $itemThumb;
        }
        else {
          $element[$delta] = $this->lazyloadFormatter->viewElements($items, $langcode);
        }
      }
    }
    return $element;
  }

}
