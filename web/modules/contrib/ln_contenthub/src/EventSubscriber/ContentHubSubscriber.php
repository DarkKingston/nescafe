<?php

namespace Drupal\ln_contenthub\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\media\Entity\Media;

/**
 * Subscribe to KernelEvents::REQUEST events and show message if there are Content Hub images to expire.
 */
class ContentHubSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['CheckForExpire'];
    return $events;
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is
   * dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function CheckForExpire(GetResponseEvent $event) {
    $current_user_roles = \Drupal::currentUser()->getRoles();
    if (in_array("administrator", $current_user_roles) || in_array("media_creator", $current_user_roles) || in_array("content_manager", $current_user_roles)) {
      $query = \Drupal::entityQuery('media');
      $entity_ids = $query->execute();
      $expiry_asset = false;
      foreach ($entity_ids as $entity_id) {
        $media = Media::load($entity_id);
		    $media_status = $media->get('status')->getString();
        if ($media->bundle() == 'content_hub' && $media_status) {
          $expiration_date = isset($media->get('field_media_ln_contenthub_ipr_ex')->getValue()[0]['value']) ? $media->get('field_media_ln_contenthub_ipr_ex')->getValue()[0]['value'] : FALSE;
          if ($expiration_date && $this->isNearToExpire(strtotime($expiration_date))) {
            $expiry_asset = true;
            /*Drupal::messenger()->addMessage(t('Content Hub Media <b>%name</b> will be expire on <b>%date</b>',
              [
                '%name' => $media->getName(),
                '%date' => $expiration_date,
              ]), 'warning');
            */
          }
        } else if ($media->bundle() == 'content_hub_document' && $media_status) {
          $expiration_date = isset($media->get('field_media_ln_contenthub_ipr_ex')->getValue()[0]['value']) ? $media->get('field_media_ln_contenthub_ipr_ex')->getValue()[0]['value'] : FALSE;
          if ($expiration_date && $this->isNearToExpire(strtotime($expiration_date))) {
            $expiry_asset = true;
            /*\Drupal::messenger()->addMessage(t('Content Hub Document Media <b>%name</b> will be expire on <b>%date</b>',
              [
                '%name' => $media->getName(),
                '%date' => $expiration_date,
              ]), 'warning');
            */
          }
        } else if ($media->bundle() == 'content_hub_video' && $media_status) {
          $expiration_date = isset($media->get('field_media_ln_contenthub_ipr_ex')->getValue()[0]['value']) ? $media->get('field_media_ln_contenthub_ipr_ex')->getValue()[0]['value'] : FALSE;
          if ($expiration_date && $this->isNearToExpire(strtotime($expiration_date))) {
            $expiry_asset = true;
            /*\Drupal::messenger()->addMessage(t('Content Hub Video Media <b>%name</b> will be expire on <b>%date</b>',
              [
                '%name' => $media->getName(),
                '%date' => $expiration_date,
              ]), 'warning');
            */
          }
        }
      }
      if ($expiry_asset == true) {

        \Drupal::messenger()->addMessage(t('ContentHub Assets will expire soon. <a target = "blank" href="@link"> Check status here</a>.',
          [
            '@link' => '/admin/config/lightnest/ln-contenthub/expiry-assets',
          ]), 'warning');
      }
    }
  }

  /**
   * This method checks if a expiration date is over three months.
   *
   * @param $expiration_date
   *
   * @return bool
   */
  protected function isNearToExpire($expiration_date) {
    return ($expiration_date <= strtotime('+2 months') && ($expiration_date >= time())) ? TRUE : FALSE;
  }

}
