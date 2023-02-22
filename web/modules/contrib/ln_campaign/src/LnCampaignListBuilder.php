<?php

namespace Drupal\ln_campaign;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webform\Entity\Webform;

/**
 * Provides a list controller for the campaign entity type.
 */
class LnCampaignListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * @var FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @var Request
   */
  protected $request;


  /**
   * @param EntityTypeInterface $entity_type
   * @param EntityStorageInterface $storage
   * @param DateFormatterInterface $date_formatter
   * @param RedirectDestinationInterface $redirect_destination
   * @param FormBuilderInterface $formBuilder
   * @param Request $request
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination, FormBuilderInterface $formBuilder, Request $request) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
    $this->formBuilder = $formBuilder;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('redirect.destination'),
      $container->get('form_builder'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  public function load() {
    $entity_query = $this->storage->getQuery();
    $header = $this->buildHeader();
    $entity_query->pager($this->limit);
    $entity_query->tableSort($header);
    $bundle = $this->request->get('bundle') ?? '';
    if ($bundle) {
      $entity_query->condition('bundle', $bundle);
    }
    $uids = $entity_query->execute();

    return $this->storage->loadMultiple($uids);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['form'] = $this->formBuilder->getForm('Drupal\ln_campaign\Form\LnCampaignListBuilderFilter');
    $build['table'] = parent::render();
    $total = $this->getStorage()
      ->getQuery()
      ->count()
      ->execute();
    $build['summary']['#markup'] = $this->t('Total campaigns: @total', ['@total' => $total]);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = [
      'data' => $this->t('ID'),
      'field' => 'id',
      'specifier' => 'id',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['title'] = [
      'data' => $this->t('Title'),
      'field' => 'title',
      'specifier' => 'title'
    ];
    $header['bundle'] = [
      'data' => $this->t('Type'),
      'field' => 'bundle',
      'specifier' => 'bundle'
    ];
    $header['status'] = [
      'data' => $this->t('Status'),
      'field' => 'status',
      'specifier' => 'status'
    ];
    $header['uid'] = [
      'data' => $this->t('Author'),
      'field' => 'uid',
      'specifier' => 'uid'
    ];
    $header['created'] = [
      'data' => $this->t('Created'),
      'field' => 'created',
      'specifier' => 'created'
    ];
    $header['changed'] = [
      'data' => $this->t('Updated'),
      'field' => 'changed',
      'specifier' => 'changed'
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ln_campaign\LnCampaignInterface */
    $row['id'] = $entity->id();
    $row['title'] = $entity->toLink();
    $row['bundle'] = $entity->bundle();
    $row['status'] = $entity->isEnabled() ? $this->t('Published') : $this->t('Unpublished');
    $row['uid']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    ];
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime());
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime());
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $destination = $this->redirectDestination->getAsArray();
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    if($webform = Webform::load($entity->bundle())){
      $operations['results'] = [
        'title' => $this->t('Results'),
        'url' => Url::fromRoute('entity.webform.results_submissions',['webform' => $webform->id()]),
        'query' => ['entity' => 'ln_campaign:' . $entity->id()],
        'weight' => 50,
      ];
    }
    return $operations;
  }



}
