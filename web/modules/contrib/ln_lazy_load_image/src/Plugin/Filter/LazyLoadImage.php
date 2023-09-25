<?php

namespace Drupal\ln_lazy_load_image\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to enable lazy loading on inline images.
 *
 * @Filter(
 *   id = "lazy_load_image",
 *   title = @Translation("Lazy Load Images"),
 *   description = @Translation("Enables lazy loading for inline images."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 100
 * )
 */
class LazyLoadImage extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    /*if (!$this->account->isAnonymous()) {
      return $result;
    }*/

    if (strpos($text, '<img') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//img') as $node) {
        /** @var \DOMElement $node */

        $current_uri = \Drupal::request()->getRequestUri();
        $url_array = explode('?', $current_uri);

        $src = $node->getAttribute('src');
        if(!empty($src) && !is_null($src) && (!preg_match('/(^\/admin\/|\/(node|media)\/[a-zA-Z0-9]+\/edit|\/(node|media)\/add\/|^\/entity-embed\/)/', $url_array[0]))) {
          $node->setAttribute('data-src', $src);
          $class = $node->getAttribute('class');
          $node->setAttribute('class', $class . ' lazyload');
          $node->removeAttribute('src');
        }
      }

      $result->setProcessedText(Html::serialize($dom));
      $result->addAttachments([
        'library' => [
          'ln_lazy_load_image/lazy_load_image',
        ],
      ]);
    }

    return $result;
  }

}
