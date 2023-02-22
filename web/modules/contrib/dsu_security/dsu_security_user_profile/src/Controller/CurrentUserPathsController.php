<?php

namespace Drupal\dsu_security_user_profile\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a generic controller for UserProfile.
 *
 * @package Drupal\dsu_security_user_profile\Controller
 */
class CurrentUserPathsController extends ControllerBase {

  /**
   * Load user profile form with user id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current url request.
   *
   * @return array
   *   return form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function editUserProfile(Request $request) {
    $current_user = \Drupal::currentUser();
    return $this->getUserForm('user', $current_user->id());
  }

  /**
   * Function to redirect the edit route to custom route.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   return form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function alter_user_edit_route() {
    $param_uid = \Drupal::routeMatch()->getRawParameter('user');
    $current_user = \Drupal::currentUser();
    // Redirect to the custom url from configuration.
    $config = \Drupal::config('dsu_security_user_profile.settings');
    if($config->get('roles')){
      $result = array_intersect($current_user->getRoles(), $config->get('roles'));
      if ($current_user->id() === $param_uid && !empty($result)) {
        return $this->redirect('dsu_security_user_profile.edit_my_profile');
      }
    }

    return $this->getUserForm('user', $param_uid);
  }

  /**
   * Load entity form from the entity_id.
   *
   * @param object $entity
   *   Entity object.
   * @param int $entity_id
   *   Entity id.
   *
   * @return array
   *   return form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getUserForm($entity, $entity_id) {
    $userObj = \Drupal::entityTypeManager()
      ->getStorage($entity)
      ->load($entity_id);
    $form = \Drupal::entityTypeManager()
      ->getFormObject($entity, 'default')
      ->setEntity($userObj);
    return \Drupal::formBuilder()->getForm($form);
  }

  /**
   * Function to redirect the user route to custom route.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   return form object or redirect on route.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function userPageView() {
    $param_uid = \Drupal::routeMatch()->getRawParameter('user');
    $current_user = \Drupal::currentUser();
    if ($current_user->id() === $param_uid) {
      return $this->redirect('dsu_security_user_profile.my_profile');
    }
    else {
      return $this->renderUserById($param_uid);
    }
  }

  /**
   * Load profile update form form user id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request current uri.
   *
   * @return object
   *   return form object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function userProfile(Request $request) {
    $current_user = \Drupal::currentUser();
    return $this->renderUserById($current_user->id());
  }

  /**
   * Render user by user_id.
   *
   * @param int $uid
   *   Current user id.
   *
   * @return array
   *   return form array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function renderUserById($uid) {
    $view_builder = $this->entityTypeManager()->getViewBuilder('user');
    $storage = $this->entityTypeManager()->getStorage('user');
    $user = $storage->load($uid);
    $view = $view_builder->view($user, 'default');

    // Set user cache context for render array.
    CacheableMetadata::createFromRenderArray($view)->addCacheContexts(['user'])
      ->applyTo($view);

    return $view;
  }

}
