<?php

namespace Drupal\ln_campaign\Plugin\WorkflowType;

use Drupal\workflows\Plugin\WorkflowTypeBase;

/**
 * Workflow field Workflow type for core workflows module.
 *
 * @WorkflowType(
 *   id = "ln_campaign_workflows_element",
 *   label = @Translation("Campaign workflow"),
 *   required_states = {},
 *   forms = {
 *     "configure" = "\Drupal\ln_campaign\Form\LnCWorkflowsElementConfigureForm"
 *   },
 * )
 */
class LnCWorkflowsElement extends WorkflowTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getInitialState() {
    if (!isset($this->configuration['initial_state']) || $this->configuration['initial_state'] == '') {
      return FALSE;
    }

    return $this->getState($this->configuration['initial_state']);
  }

}
