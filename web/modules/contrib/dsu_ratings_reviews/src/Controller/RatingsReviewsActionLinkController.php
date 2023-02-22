<?php

namespace Drupal\dsu_ratings_reviews\Controller;

use Drupal;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityInterface;
use Drupal\dsu_ratings_reviews\RatingsReviewsFlagAdapter;
use Drupal\flag\Ajax\ActionLinkFlashCommand;
use Drupal\flag\Controller\ActionLinkController;
use Drupal\flag\FlagInterface;
use LogicException;

/**
 * Controller responses to flag and unflag action links.
 *
 * The response is a set of AJAX commands to update the
 * link in the page.
 */
class RatingsReviewsActionLinkController extends ActionLinkController {

  /**
   * {@inheritDoc}
   */
  public function flag(FlagInterface $flag, $entity_id) {
    // Customize behaviour only for our flags.
    /** @var \Drupal\dsu_ratings_reviews\RatingsReviewsFlagAdapter $adaptations */
    $adaptations = Drupal::classResolver()
      ->getInstanceFromDefinition(RatingsReviewsFlagAdapter::class);
    $comment_flags = $adaptations->getControlledFlags();
    if (!in_array($flag->id(), $comment_flags)) {
      return parent::flag($flag, $entity_id);
    }

    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->flagService->getFlaggableById($flag, $entity_id);

    try {
      $this->flagService->flag($flag, $entity);
    } catch (LogicException $e) {
      // Fail silently so we return to the entity, which will show an updated
      // link for the existing state of the flag.
    }

    // This method wasn't fully inherited to force using child class method.
    return $this->generateResponse($flag, $entity, $flag->getMessage('flag'));
  }

  /**
   * {@inheritDoc}
   */
  private function generateResponse(FlagInterface $flag, EntityInterface $entity, $message) {
    // Create a new AJAX response.
    $response = new AjaxResponse();

    $this->generateCommand($flag, $entity, $message, $response);

    // Prepare reverse command as well, to update reverse button.
    /** @var \Drupal\dsu_ratings_reviews\RatingsReviewsFlagAdapter $adaptations */
    $adaptations = Drupal::classResolver()
      ->getInstanceFromDefinition(RatingsReviewsFlagAdapter::class);
    $reverse_flag_id = $adaptations->getReverseFlag($flag->id());
    if (!empty($reverse_flag_id)) {
      $reverse_flag = $this->flagService->getFlagById($reverse_flag_id);
      $this->generateCommand($reverse_flag, $entity, $message, $response);
    }

    return $response;
  }

  /**
   * Generates a response after the flag has been updated.
   *
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag entity.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param string $message
   *   (optional) The message to flash.
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The response object to complement.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response object.
   */
  private function generateCommand(FlagInterface $flag, EntityInterface $entity, string $message, AjaxResponse $response) {
    // Get the link type plugin.
    $link_type = $flag->getLinkTypePlugin();

    // Generate the link render array.
    $link = $link_type->getAsFlagLink($flag, $entity);

    // Generate a CSS selector to use in a JQuery Replace command.
    $selector = '.js-flag-' . Html::cleanCssIdentifier($flag->id()) . '-' . $entity->id();

    // Create a new JQuery Replace command to update the link display.
    $replace = new ReplaceCommand($selector, $this->renderer->renderPlain($link));
    $response->addCommand($replace);

    // Push a message pulsing command onto the stack.
    $pulse = new ActionLinkFlashCommand($selector, $message);
    $response->addCommand($pulse);

    return $response;
  }

}
