<?php

namespace Drupal\mobile_number_login_otp\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Render\BareHtmlPageRendererInterface;
use Drupal\Core\Url;
use Drupal\mobile_number_login_otp\AccountAuth;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserStorageInterface;
use Drupal\user\UserFloodControlInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;

/**
 * The AccountLoginForm Class.
 */
class AccountLoginForm extends FormBase {

  /**
   * The user flood control service.
   *
   * @var \Drupal\user\UserFloodControl
   */
  protected $userFloodControl;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The user authentication object.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The bare HTML renderer.
   *
   * @var \Drupal\Core\Render\BareHtmlPageRendererInterface
   */
  protected $bareHtmlPageRenderer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a new UserLoginForm.
   *
   * @param \Drupal\user\UserFloodControlInterface $user_flood_control
   *   The user flood control service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Render\BareHtmlPageRendererInterface $bare_html_renderer
   *   The renderer.
   * @param \Drupal\mobile_number_login_otp\AccountAuth $account_auth
   *   Mobile number login otp gateway service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Configuration object.
   */
  public function __construct(UserFloodControlInterface $user_flood_control, UserStorageInterface $user_storage, UserAuthInterface $user_auth, RendererInterface $renderer, BareHtmlPageRendererInterface $bare_html_renderer = NULL, AccountAuth $account_auth, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config) {
    // Ignoring phpcs errors as make it compatible with older versions.
    // @codingStandardsIgnoreStart
    if (!$user_flood_control instanceof UserFloodControlInterface) {
      @trigger_error('Passing the flood service to ' . __METHOD__ . ' is deprecated in drupal:9.1.0 and is replaced by user.flood_control in drupal:10.0.0. See https://www.drupal.org/node/3067148', E_USER_DEPRECATED);
      $user_flood_control = \Drupal::service('user.flood_control');
    }
    if (!$bare_html_renderer instanceof BareHtmlPageRendererInterface) {
      @trigger_error('Calling UserLoginForm::__construct() without the $bare_html_renderer argument is deprecated in drupal:9.4.0 and will be required before drupal:10.0.0. See https://www.drupal.org/node/3251987.', E_USER_DEPRECATED);
      $bare_html_renderer = \Drupal::service('bare_html_page_renderer');
    }
    // @codingStandardsIgnoreEnd
    $this->userFloodControl = $user_flood_control;
    $this->userStorage = $user_storage;
    $this->userAuth = $user_auth;
    $this->renderer = $renderer;
    $this->bareHtmlPageRenderer = $bare_html_renderer;
    $this->accountAuth = $account_auth;
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.flood_control'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('user.auth'),
      $container->get('renderer'),
      $container->get('bare_html_page_renderer'),
      $container->get('mobile_number_login_otp.account.auth'),
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'account_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['mobilenumber'] = [
      '#type' => 'tel',
      '#title' => $this->t('Mobile Number'),
      '#description' => $this->t('Enter the Mobilenumber'),
      '#maxlength' => 60,
      '#attributes' => ['class' => ['mobile-number-login']],
      '#wrapper_attributes' => ['class' => ['mobile-wrapper']],
      '#size' => 60,
      '#field_suffix' => Markup::create("<div class='otp-send'><span class='mobilenumber-error'></span><a href='javascript:void(0);'>" . $this->t('Send OTP') . "</a><div class='otp-counter'></div></div>"),
      '#required' => TRUE,
    ];
    $form['otp'] = [
      '#type' => 'number',
      '#title' => $this->t('Enter OTP'),
      '#description' => $this->t('Please enter the otp'),
      '#attributes' => ['class' => ['otp-input']],
      '#wrapper_attributes' => ['class' => ['otp-wrapper']],
      '#required' => TRUE,
    ];
    $form['extension'] = [
      '#type' => 'hidden',
      '#value' => '+91',
    ];

    $form['#attached']['library'][] = 'mobile_number_login/mobile-number-login';
    $form['#attached']['library'][] = 'mobile_number_login_otp/mobile-number-login-otp';
    $form['#attached']['drupalSettings']['default_countries_mobile_login'] = $this->config->get('mobilenumberlogin.settings')->get('default_countries');

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Log in'),
    ];

    $form['normallink'] = Link::createFromRoute($this->t('Login with Password'), 'user.login')->toRenderable();
    $form['normallink']['#attributes']['class'][] = 'button button--primary normal-button';
    $form['normallink']['#weight'] = 50;

    $form['#validate'][] = '::validateMobile';
    $form['#validate'][] = '::validateAuthentication';
    $form['#validate'][] = '::validateFinal';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (empty($uid = $form_state->get('uid'))) {
      return;
    }
    $account = $this->userStorage->load($uid);

    if (!$this->getRequest()->request->has('destination')) {
      $form_state->setRedirect(
        'entity.user.canonical',
        ['user' => $account->id()]
      );
    }
    else {
      $this->getRequest()->query->set('destination', $this->getRequest()->request->get('destination'));
    }

    user_login_finalize($account);
  }

  /**
   * Sets an error if supplied username has been blocked.
   */
  public function validateMobile(array &$form, FormStateInterface $form_state) {
    if (!$form_state->isValueEmpty('mobilenumber') && $this->mobileIsBlocked($form_state->getValue('extension'), $form_state->getValue('mobilenumber'))) {
      $form_state->setErrorByName('mobilenumber', $this->t('The mobile number %mobile has not been activated or is blocked.', ['%mobile' => $form_state->getValue('mobilenumber')]));
    }
  }

  /**
   * Checks supplied mobile/otp against local users table.
   *
   * If successful, $form_state->get('uid') is set to the matching user ID.
   */
  public function validateAuthentication(array &$form, FormStateInterface $form_state) {
    $otp = trim($form_state->getValue('otp'));
    $floodConfig = $this->config('user.flood');
    if (!$form_state->isValueEmpty('mobilenumber') && strlen($otp) > 0) {
      if (!$this->userFloodControl->isAllowed('user.failed_login_ip', $floodConfig->get('ip_limit'), $floodConfig->get('ip_window'))) {
        $form_state->set('flood_control_triggered', 'ip');
        return;
      }
      $accounts = $this->userStorage->loadByProperties([
        'field_login_mobile_number' => $form_state->getValue('extension') . '-' . $form_state->getValue('mobilenumber'),
        'status' => 1,
      ]);
      $account = reset($accounts);
      if ($account) {
        if ($floodConfig->get('uid_only')) {
          $identifier = $account->id();
        }
        else {
          $identifier = $account->id() . '-' . $this->getRequest()->getClientIP();
        }
        $form_state->set('flood_control_user_identifier', $identifier);

        if (!$this->userFloodControl->isAllowed('user.failed_login_user', $floodConfig->get('user_limit'), $floodConfig->get('user_window'), $identifier)) {
          $form_state->set('flood_control_triggered', 'user');
          return;
        }
      }

      $uid = $this->accountAuth->authenticate($form_state->getValue('extension'), $form_state->getValue('mobilenumber'), $otp);
      if (!$uid) {
        $form_state->set('otp', $this->t('OTP is incorrect'));
        return;
      }
      $form_state->set('uid', $uid);
    }
  }

  /**
   * Checks for usernames blocked by user administration.
   *
   * @param string $extension
   *   A string containing of extension.
   * @param int $mobile
   *   User mobile.
   *
   * @return bool
   *   TRUE if the user is blocked, FALSE otherwise.
   */
  private function mobileIsBlocked($extension, $mobile) {
    return (bool) $this->entityTypeManager->getStorage('user')->getQuery()
      ->accessCheck(FALSE)
      ->condition('field_login_mobile_number', $extension . '-' . $mobile)
      ->condition('status', 0)
      ->execute();
  }

  /**
   * Checks if user was not authenticated, or if too many logins were attempted.
   *
   * This validation function should always be the last one.
   */
  public function validateFinal(array &$form, FormStateInterface $form_state) {
    $flood_config = $this->config('user.flood');
    if (!$form_state->get('uid')) {
      $this->userFloodControl->register('user.failed_login_ip', $flood_config->get('ip_window'));
      if ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
        $this->userFloodControl->register('user.failed_login_user', $flood_config->get('user_window'), $flood_control_user_identifier);
      }

      if ($flood_control_triggered = $form_state->get('flood_control_triggered')) {
        if ($flood_control_triggered == 'user') {
          $message = $this->formatPlural($flood_config->get('user_limit'), 'There has been more than one failed login attempt for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', 'There have been more than @count failed login attempts for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => Url::fromRoute('user.pass')->toString()]);
        }
        else {
          $message = $this->t('Too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => Url::fromRoute('user.pass')->toString()]);
        }
        $response = $this->bareHtmlPageRenderer->renderBarePage(['#markup' => $message], $this->t('Login failed'), 'maintenance_page');
        $response->setStatusCode(403);
        $form_state->setResponse($response);
      }
      else {
        // $user_input = $form_state->getUserInput();
        // $query = isset($user_input['mobilenumber']) ? [
        // 'mobilenumber' => $user_input['mobilenumber'],
        // ] : [];
        $form_state->setErrorByName('mobilenumber', $this->t('Unrecognized mobile number or otp.'));
        $accounts = $this->userStorage->loadByProperties([
          'field_login_mobile_number' => $form_state->getValue('extension') . '-' . $form_state->getValue('mobilenumber'),
          'status' => 1,
        ]);
        if (!empty($accounts)) {
          $this->logger('user')->notice('Login attempt failed for %user.', ['%user' => $form_state->getValue('mobilenumber')]);
        }
        else {
          $this->logger('user')->notice('Login attempt failed from %ip.', ['%ip' => $this->getRequest()->getClientIp()]);
        }
      }
    }
    elseif ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
      $this->userFloodControl->clear('user.failed_login_user', $flood_control_user_identifier);
    }
  }

}
