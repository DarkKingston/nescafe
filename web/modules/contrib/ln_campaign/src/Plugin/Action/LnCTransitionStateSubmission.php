<?php

namespace Drupal\ln_campaign\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\user\Entity\User;
use Drupal\webform\WebformSubmissionForm;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\workflows\TransitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class LnCTransitionStateSubmission extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * @var LnCWorkflowsManager $workflowsManager
   */
  protected $workflowsManager;

  /**
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param LnCWorkflowsManager $workflowsManager
   * @param AccountProxyInterface $currentUser
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LnCWorkflowsManager $workflowsManager, AccountProxyInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->workflowsManager = $workflowsManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_campaign_workflows_element.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(WebformSubmissionInterface $webformSubmission = NULL) {
    $transition = FALSE;
    $transaction_message = $this->t('It has not been possible to make the transition for submission @id',['@id' => $webformSubmission->id()]);
    if($webformSubmission){
      $data = $webformSubmission->getData();
      if(isset($data[LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD])){
        $currentState = $data[LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD]['workflow_state'];
        $account = User::load($this->currentUser->id());
        $transitions = $this->workflowsManager->getAvailableTransitionsForWorkflow(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_CATEGORY,$currentState,$account);
        /** @var TransitionInterface $transition */
        if($transition = $transitions[$this->getTransitionId()] ?? FALSE){
          $data[LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD]['transition'] = $transition->id();
          $webformSubmission->setData($data);
          $errors = WebformSubmissionForm::validateWebformSubmission($webformSubmission);
          if (empty($errors)) {
            $result = WebformSubmissionForm::submitWebformSubmission($webformSubmission);
            if($result instanceof $webformSubmission){
              $transition = TRUE;
              $transaction_message = $this->t('The change of status for submission @id has been successfully completed',['@id' => $webformSubmission->id()]);
            }
          }
        }else{
          $transaction_message = $this->t('Submission @id is not allowed this transition',['@id' => $webformSubmission->id()]);
        }
      }
    }
    if(!$transition){
      \Drupal::messenger()->addWarning($transaction_message);
    }else{
      \Drupal::messenger()->addStatus($transaction_message);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\webform\WebformSubmissionInterface $object */
    return $object->access('administer campaigns participations', $account, $return_as_object);
  }

  /**
   * @return string
   */
  abstract public function getTransitionId();

}
