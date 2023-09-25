<?php

namespace Drupal\noreferrer\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to apply the noreferrer attribute.
 *
 * @Filter(
 *   id = "noreferrer",
 *   title = @Translation("Add rel=&quot;noopener&quot; and/or rel=&quot;noreferrer&quot; to links"),
 *   description = @Translation("Note, this filter includes the <em>Correct faulty and chopped off HTML</em> filter; there is no need to enable both filters."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = 10
 * )
 */
class NoReferrerFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs No Referrer filter.
   *
   * @param mixed[] $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   * @param mixed[] $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $html_dom = Html::load($text);
    $links = $html_dom->getElementsByTagName('a');
    $config = $this->configFactory->get('noreferrer.settings');
    $noopener = $config->get('noopener');
    $noreferrer = $config->get('noreferrer');
    foreach ($links as $link) {
      $types = [];
      if ($noopener && $link->getAttribute('target') !== '') {
        $types[] = 'noopener';
      }
      if ($noreferrer && ($href = $link->getAttribute('href')) && UrlHelper::isExternal($href) && !noreferrer_is_allowed($href)) {
        $types[] = 'noreferrer';
      }
      if ($types) {
        // Merge existing rel values.
        if ($rel = $link->getAttribute('rel')) {
          $types = array_merge($types, explode(' ', $rel));
          // Clear empty strings and ensure all values are unique.
          $types = array_unique(array_filter($types));
        }
        $link->setAttribute('rel', implode(' ', $types));
      }
    }
    $result->setProcessedText(Html::serialize($html_dom));
    return $result;
  }

}
