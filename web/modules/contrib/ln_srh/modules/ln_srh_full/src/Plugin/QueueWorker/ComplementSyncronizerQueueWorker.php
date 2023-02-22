<?php

namespace Drupal\ln_srh_full\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh_full\Services\SRHComplementUtilsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;

/**
 * Processes tasks for example module.
 *
 * @QueueWorker(
 *   id = "srh_complement_syncronizer_queue",
 *   title = @Translation("Complement Syncronizer SRH"),
 *   cron = {"time" = 120}
 * )
 */
class ComplementSyncronizerQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Lightnest SRH Complement Utils service.
   *
   * @var SRHComplementUtilsInterface
   */
  protected $srhUtils;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * SyncronizerQueueWorker constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param SRHComplementUtilsInterface $srhUtils
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,SRHComplementUtilsInterface $srhUtils, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->srhUtils = $srhUtils;
    $this->loggerFactory = $loggerFactory->get('ln_srh');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_srh_full.complement_utils'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // Start process to synchronize recipe
    try {
      if(!$this->srhUtils->syncComplement($item)){
        $recipe_name = $item['name'] ?? '';
        throw new \Exception($this->t('An error occurred while creating the complement: @name', ['@name' => $recipe_name]));
      }
    }catch (SRHException $e){
      $this->loggerFactory->warning($e->getMessage());
    }
  }

}
