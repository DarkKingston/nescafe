<?php

namespace Drupal\ln_campaign\Plugin\WebformElement;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\webform\Plugin\WebformElement\Email;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'email' element.
 *
 * @WebformElement(
 *   id = "ln_campaign_email",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Email.php/class/Email",
 *   label = @Translation("Campaign Email"),
 *   description = @Translation("Provides a form element for entering an email address."),
 *   category = @Translation("Advanced elements"),
 * )
 */
class LnCEmail extends Email {

  /**
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element,$webform_submission);
    $default_value = $element['#default_value'] ?? '';
    $element['#default_value'] = $this->currentUser->isAuthenticated() ? $this->currentUser->getEmail() : $default_value;
    $element['#type'] = $this->currentUser->isAuthenticated() ? 'hidden' : $element['#type'];
  }
}
