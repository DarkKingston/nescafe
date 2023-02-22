<?php

namespace Drupal\dsu_security\Service;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class UserPasswordResetRedirect.
 *
 * @package Drupal\dsu_security\Service
 */
class UserPasswordResetRedirect implements UserPasswordResetRedirectInterface {

  /**
   * UserPasswordResetRedirect constructor.
   */
  public function __construct() {

  }

  /**
   * Redirect reset password form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function userResetPasswordFormRedirect(FormStateInterface $form_state) {
    // Resets the form error status so no form fields are highlighted.
    $form_state->setRebuild();
    $form_state->clearErrors();
    // Removes "username is not recognized as a username or an email address.".
    \Drupal::messenger()->deleteAll();
    // Set message for user display.
    $account = user_load_by_mail($form_state->getValue('name'));
    if (empty($account)) {
      // No success, try to load by name.
      $account = user_load_by_name($form_state->getValue('name'));
    }
    // Only in case of valid user exist.
    if ($account && \Drupal::currentUser()->isAnonymous()) {
      $langcode = \Drupal::LanguageManager()->getCurrentLanguage()->getId();
      $mail = _user_mail_notify('password_reset', $account, $langcode);
      if (!empty($mail)) {
        \Drupal::messenger()
          ->addMessage(t('An email with further instructions has been sent.'), 'status');
      }
    }
    else if (\Drupal::config('user.settings')->get('notify.password_reset')) {
      // Show message if user is not exist for security.
      \Drupal::messenger()
        ->addMessage(t('An email with further instructions has been sent.'), 'status');
    }

    // Redirect on user login page.
    $response = new RedirectResponse(Url::fromRoute('user.page')->toString());
    $request = \Drupal::request();
    $request->getSession()->save();
    $response->prepare($request);
    \Drupal::service('kernel')->terminate($request, $response);
    $response->send();
  }

}
