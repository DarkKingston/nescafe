<?php

namespace Drupal\ln_seo_hreflang;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\ln_seo_hreflang\Form\PathFilterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a list controller for the hreflang entity type.
 */
class LnHreflangListBuilder extends BulkFormListBuilder {

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
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new LnHreflangListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface           $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface        $storage
   * @param \Drupal\Core\Datetime\DateFormatterInterface      $date_formatter
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   * @param \Symfony\Component\HttpFoundation\Request         $current_request
   * @param \Drupal\Core\Form\FormBuilderInterface            $form_builder
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage,
    DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination,
    Request $current_request,  FormBuilderInterface $form_builder, EntityStorageInterface $action_storage) {
    parent::__construct($entity_type, $storage, $action_storage, $form_builder);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
    $this->currentRequest = $current_request;
    $this->formBuilder = $form_builder;
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
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('form_builder'),
      $container->get('entity_type.manager')->getStorage('action')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery();

    $search = $this->currentRequest->query->get('search');
    if ($search) {
      $query->condition('path', $search, 'CONTAINS');
    }

    $sort = $this->currentRequest->query->get('sort');
    if ($sort) {
      $query->sort('path', $sort);
    }else{
      $query->sort('id', 'desc');
    }

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    // Allow the entity query to sort using the table header.
    $header = $this->buildHeader();
    $query->tableSort($header);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function render() {

    $keys = $this->currentRequest->query->get('search');
    $build['ln_seo_hreflang_path_filter_form'] = $this->formBuilder->getForm(PathFilterForm::class, $keys);
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total hreflangs: @total', ['@total' => $total]);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['link'] = [
      'data' => $this->t('Path'),
      'specifier' => 'path',
      'field' => 'path',
      'sort' => 'desc',
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ln_seo_hreflang\LnHreflangInterface */
    $row['link']['data']['path'] = [
      '#type' => 'link',
      '#title' => $entity->getPath(),
      '#url' => $entity->getUrl(),
    ];
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
    return $operations;
  }

}
