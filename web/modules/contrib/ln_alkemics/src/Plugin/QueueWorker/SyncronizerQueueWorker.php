<?php

namespace Drupal\ln_alkemics\Plugin\QueueWorker;

use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\ln_alkemics\Controller\Importer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes tasks for example module.
 *
 * @QueueWorker(
 *   id = "syncronizer_alkemics_queue",
 *   title = @Translation("Syncronizer Alkemics Product"),
 *   cron = {"time" = 240}
 * )
 */
class SyncronizerQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Configuration state Drupal Site.
   *
   * @var array
   */

  protected $configFactory;

  /**
   * Configuration state Drupal Site.
   *
   * @var array
   */
  protected $serialization;

  /**
   * DSU Alkemics Products Importer service.
   *
   * @var array
   */
  protected $importer;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $configFactory, Json $serialization, Importer $importer) {
    $this->configFactory = $configFactory;
    $this->serialization = $serialization;
    $this->importer = $importer;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('serialization.json'),
      $container->get('ln_alkemics.importer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    Drupal::logger('syncronizer_alkemics_queue')->notice('Queue Item working ');

    // Get all alkemics products id.
    $groups = $this->importer->getAllId();
    Drupal::logger('syncronizer_alkemics_queue')
      ->notice('Developed <pre><code>' . print_r($groups, TRUE) . '</code></pre');

    // Get groups of result for syncing.
    foreach ($groups as $values) {
      $results = $this->importer->syncroProducts($values['product']);
    }

    // Get save history of indexing in config variables and convert json_decode.
    Importer::toggleSolrSearchIndexingServer(TRUE);
  }

}
