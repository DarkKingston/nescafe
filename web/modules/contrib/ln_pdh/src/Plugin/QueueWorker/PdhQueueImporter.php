<?php

namespace Drupal\ln_pdh\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_pdh\PdhImporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Imports PDH products.
 *
 * @QueueWorker(
 *   id = "ln_pdh_queue_importer",
 *   title = @Translation("PDH Product importer"),
 *   cron = {"time" = 30}
 * )
 */
class PdhQueueImporter extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * DSU PDH Products Importer service.
   *
   * @var \Drupal\ln_pdh\PdhImporterInterface
   */
  protected $importer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PdhImporterInterface $importer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->importer = $importer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_pdh.importer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    $this->getLogger('syncronizer_pdh_queue')->notice($this->t('PDH Importer queue importing item @item_id.', ['@item_id' => $item->gtin]));

    $this->importer->toggleSolrSearchIndexingServer(FALSE);
    $this->importer->syncProduct($item);
    $this->importer->toggleSolrSearchIndexingServer(TRUE);
  }

}
