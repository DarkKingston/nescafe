<?php

namespace Drupal\ln_srh\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ln_srh\SRHConstants;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SRHController extends ControllerBase{

  public function goSRH($srh_id){
    /** @var \Drupal\ln_srh\Services\SRHUtilsInterface $srh_utils */
    $srh_utils = \Drupal::service('ln_srh.utils');
    if($recipe = $srh_utils->getRecipeBySRHId($srh_id)){
      if($recipe->bundle() !== SRHConstants::SRH_RECIPE_BUNDLE || !$recipe->isPublished()){
        throw new NotFoundHttpException();
      }
      return new RedirectResponse($recipe->toUrl()->toString(),301);
    }

    throw new NotFoundHttpException();
  }
}
