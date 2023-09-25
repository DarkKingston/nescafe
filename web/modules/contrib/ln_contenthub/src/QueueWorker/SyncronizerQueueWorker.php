<?php

namespace Drupal\ln_contenthub\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Config\ConfigFactory;
use Drupal\ln_contenthub\ContentHubInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes tasks for example module.
 *
 * @QueueWorker(
 *   id = "syncronizer_contenthub_media_queue",
 *   title = @Translation("Syncronizer Contenthub Media Product"),
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
   * The Content Hub service.
   *
   * @var \Drupal\ln_contenthub\ContentHubInterface
   */
  protected $contentHubService;

  /**
   * The Per page record.
   */
  protected $per_page_record;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $configFactory, ContentHubInterface $content_hub_service) {
    $this->configFactory = $configFactory;
    $this->contentHubService = $content_hub_service;
    $this->per_page_record = 2000;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('ln_contenthub.ln_contenthub_services')
  );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    Drupal::logger('syncronizer_contenthub_media_queue')
      ->notice('Queue Item working ');
    $param = [];
    $last_execution = Drupal::state()->get('ln_contenthub.next_execution');
    $param['lastModified'] = $last_execution;
    $param['pageSize'] = $this->per_page_record;
    $search_results = $this->contentHubService->query($param);
    Drupal::logger('syncronizer_contenthub_media_queue')
      ->notice('Developed <pre><code>' . print_r($search_results, TRUE) . '</code></pre');
    if (isset($search_results) && count($search_results) > 0) {
      foreach ($search_results as $result) {
        // Check media exist in site.
        $query = \Drupal::entityQuery('media')
          ->condition('field_media_ln_contenthub_id', $result->id);
        $entity_id = $query->execute();
        $entity_id = reset($entity_id);
        if (!empty($entity_id)) {
          // Update the media.
          $this->contentHubService->updateMediaEntity($entity_id, $result);
        }
      }
    }
  }

}
