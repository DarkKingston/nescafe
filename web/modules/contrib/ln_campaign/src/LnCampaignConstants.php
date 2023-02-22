<?php

namespace Drupal\ln_campaign;

interface LnCampaignConstants{

  const LN_CAMPAING_WORKFLOWS_ELEMENT = 'ln_campaign_workflows_element';
  const LN_CAMPAING_WORKFLOWS_CATEGORY = 'ln_campaign';
  const LN_CAMPAING_WORKFLOWS_STATE_PENDING = 'ln_campaign_pending';
  const LN_CAMPAING_WORKFLOWS_STATE_VALID = 'ln_campaign_valid';
  const LN_CAMPAING_WORKFLOWS_STATE_INVALID = 'ln_campaign_invalid';
  const LN_CAMPAING_WORKFLOWS_STATE_PAID = 'ln_campaign_paid';
  const LN_CAMPAING_WORKFLOWS_TRANSITION_PENDING = 'ln_campaign_pending';
  const LN_CAMPAING_WORKFLOWS_TRANSITION_VALIDATION = 'ln_campaign_validation';
  const LN_CAMPAING_WORKFLOWS_TRANSITION_INVALIDATION = 'ln_campaign_invalidation';
  const LN_CAMPAING_WORKFLOWS_TRANSITION_PAID = 'ln_campaign_paid';
  const LN_CAMPAING_WEBFORM_CATEGORY = 'ln_campaign';
  const LN_CAMPAING_EMAIL_ELEMENT = 'ln_campaign_email';
  const LN_CAMPAING_WEBFORM_EMAIL_FIELD = 'ln_campaign_email';
  const LN_CAMPAING_WEBFORM_PINCODE_FIELD = 'ln_campaign_pincode';
  const LN_CAMPAING_WEBFORM_WORKFLOW_FIELD = 'ln_campaign_workflow';
  const LN_CAMPAING_WEBFORM_TICKET_FIELD = 'ln_campaign_ticket';
  const LN_CAMPAING_PINCODE_FIELD = 'field_ln_campaign_pincodes';
  const LN_CAMPAING_MOMENT_FIELD = 'field_ln_campaing_moment';

}
