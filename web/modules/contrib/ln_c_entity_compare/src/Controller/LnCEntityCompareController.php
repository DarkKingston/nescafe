<?php

namespace Drupal\ln_c_entity_compare\Controller;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\ln_c_entity_compare\Entity\Bundle\LnCEntityCompareBundleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Lightnest Components: Entity compare routes.
 */
class LnCEntityCompareController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service
   *
   * @var Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * Route callback for rendering entity fields as a template
   *
   * Only works for Ajax calls.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *  The request.
   * 
   * @param LnCEntityCompareBundleInterface $paragraph
   *  Paragraph entity which triggered the request
   *
   * @param string $eid
   *  ID of the requested entity
   * 
   * @return \Drupal\Core\Ajax\AjaxResponse
   *  The AJAX response.
   */
  public function render(Request $request, LnCEntityCompareBundleInterface $paragraph, $eid) {

    if (!$request->isXmlHttpRequest()) {
      throw new NotFoundHttpException();
    }

    $response = new AjaxResponse();

    // Attempt to load requested entity
    $settings = $paragraph->getParagraphSettings();
    $entity_storage = $this->entityTypeManager->getStorage($settings['entity_type']);
    $entity = $entity_storage->load($eid);
    
    if ($entity) {
      // Get correct translation and make sure access is granted
      $entity = $this->entityRepository->getTranslationFromContext($entity);
      $access = $entity->access('view', NULL, TRUE);
      if ($access->isAllowed()) {
        // Get render array of entity fields
        $fields = $paragraph->getFieldsToRender([$entity]);
        $render_array = $paragraph->getCachedEntityTemplate([$entity], $fields);

        // Create command to append the template into the DOM
        $selector = '#' . $paragraph->getParagraphWrapperId();
        $command = new AppendCommand($selector, $render_array);
        $response->addCommand($command);
      }
    }

    return $response;
  }

}
