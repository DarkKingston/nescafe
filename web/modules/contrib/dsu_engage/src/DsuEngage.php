<?php

/**
 * @file
 * Contains Drupal\dsu_engage\DsuEngage.
 */

namespace Drupal\dsu_engage;

class DsuEngage {

  const PERMISSION_ADMINISTER_DSU_ENGAGE = 'administer dsu engage';
  const PERMISSION_ADMINISTER_DSU_ENGAGE_ADVANCED = 'administer advanced dsu engage';
  const PERMISSION_ADMINISTER_FULL_DSU_ENGAGE = 'administer full dsu engage';

  //Administration Form settings
  const API_ENDPOINT_TOKEN_URL = 'dsu_engage_api_token_url';
  const API_ENDPOINT_URL = 'dsu_engage_api_endpoint_url';
  const API_CLIENT_ID = 'dsu_engage_api_client_id';
  const API_CLIENT_SECRET = 'dsu_engage_api_client_secret';
  const API_CLIENT_CERTIFICATE = 'dsu_engage_api_client_certificate';
  const API_AUDIENCE_URL = 'dsu_engage_api_audience_url';

  //Hidden fields in the form
  const API_BRAND = 'dsu_engage_api_brand';
  const API_MARKET = 'dsu_engage_api_market';
  const API_CONSUMER_CONTACT_ORIGIN = 'dsu_engage_api_contact_origin';
  const API_ENGAGE_TOKEN_EXPIRATION = 'dsu_engage_password_expiration';

  // Configuration fields
  const SHOW_FIELD_REQUEST_TYPE = 'dsu_engage_show_field_request_type';

  const SHOW_FIELD_REQUEST_TYPE_OPTIONS = 'dsu_engage_show_field_request_type_options';

  const SHOW_FIELD_PRODUCT_DESCRIPTION_QUESTION = 'dsu_engage_show_field_product_description_question';
  const SHOW_FIELD_PRODUCT_DESCRIPTION_COMPLAINT = 'dsu_engage_show_field_product_description_complaint';
  const SHOW_FIELD_PRODUCT_DESCRIPTION_PRAISE = 'dsu_engage_show_field_product_description_praise';

  const SHOW_FIELD_FIRST_NAME_QUESTION = 'dsu_engage_show_field_first_name_question';
  const SHOW_FIELD_FIRST_NAME_COMPLAINT = 'dsu_engage_show_field_first_name_complaint';
  const SHOW_FIELD_FIRST_NAME_PRAISE = 'dsu_engage_show_field_first_name_praise';

  const SHOW_FIELD_LAST_NAME_QUESTION = 'dsu_engage_show_field_last_name_question';
  const SHOW_FIELD_LAST_NAME_COMPLAINT = 'dsu_engage_show_field_last_name_complaint';
  const SHOW_FIELD_LAST_NAME_PRAISE = 'dsu_engage_show_field_last_name_praise';

  const SHOW_FIELD_PREFERRED_CHANNEL_QUESTION = 'dsu_engage_show_field_preferred_channel_question';
  const SHOW_FIELD_PREFERRED_CHANNEL_COMPLAINT = 'dsu_engage_show_field_preferred_channel_complaint';
  const SHOW_FIELD_PREFERRED_CHANNEL_PRAISE = 'dsu_engage_show_field_preferred_channel_praise';

  const SHOW_FIELD_EMAIL_QUESTION = 'dsu_engage_show_field_email_question';
  const SHOW_FIELD_EMAIL_COMPLAINT = 'dsu_engage_show_field_email_complaint';
  const SHOW_FIELD_EMAIL_PRAISE = 'dsu_engage_show_field_email_praise';

  const SHOW_FIELD_PHONE_QUESTION = 'dsu_engage_show_field_phone_question';
  const SHOW_FIELD_PHONE_COMPLAINT = 'dsu_engage_show_field_phone_complaint';
  const SHOW_FIELD_PHONE_PRAISE = 'dsu_engage_show_field_phone_praise';

  const SHOW_FIELD_BAR_CODE_QUESTION = 'dsu_engage_show_field_bar_code_question';
  const SHOW_FIELD_BAR_CODE_COMPLAINT = 'dsu_engage_show_field_bar_code_complaint';
  const SHOW_FIELD_BAR_CODE_PRAISE = 'dsu_engage_show_field_bar_code_praise';

  const SHOW_FIELD_BEST_BEFORE_DATE_QUESTION = 'dsu_engage_show_field_best_before_date_question';
  const SHOW_FIELD_BEST_BEFORE_DATE_COMPLAINT = 'dsu_engage_show_field_best_before_date_complaint';
  const SHOW_FIELD_BEST_BEFORE_DATE_PRAISE = 'dsu_engage_show_field_best_before_date_praise';

  const SHOW_FIELD_STREET_QUESTION = 'dsu_engage_show_field_street_question';
  const SHOW_FIELD_STREET_COMPLAINT = 'dsu_engage_show_field_street_complaint';
  const SHOW_FIELD_STREET_PRAISE = 'dsu_engage_show_field_street_praise';

  const SHOW_FIELD_CITY_QUESTION = 'dsu_engage_show_field_city_question';
  const SHOW_FIELD_CITY_COMPLAINT = 'dsu_engage_show_field_city_complaint';
  const SHOW_FIELD_CITY_PRAISE = 'dsu_engage_show_field_city_praise';

  const SHOW_FIELD_ZIP_CODE_QUESTION = 'dsu_engage_show_field_zip_code_question';
  const SHOW_FIELD_ZIP_CODE_COMPLAINT = 'dsu_engage_show_field_zip_code_complaint';
  const SHOW_FIELD_ZIP_CODE_PRAISE = 'dsu_engage_show_field_zip_code_praise';

  const SHOW_FIELD_STATE_QUESTION = 'dsu_engage_show_field_state_question';
  const SHOW_FIELD_STATE_COMPLAINT = 'dsu_engage_show_field_state_complaint';
  const SHOW_FIELD_STATE_PRAISE = 'dsu_engage_show_field_state_praise';

  const SHOW_FIELD_COUNTRY_QUESTION = 'dsu_engage_show_field_country_question';
  const SHOW_FIELD_COUNTRY_COMPLAINT = 'dsu_engage_show_field_country_complaint';
  const SHOW_FIELD_COUNTRY_PRAISE = 'dsu_engage_show_field_country_praise';

  const SHOW_FIELD_ATTACHMENTS_QUESTION = 'dsu_engage_show_field_attachments_question';
  const SHOW_FIELD_ATTACHMENTS_COMPLAINT = 'dsu_engage_show_field_attachments_complaint';
  const SHOW_FIELD_ATTACHMENTS_PRAISE = 'dsu_engage_show_field_attachments_praise';

  const MANDATORY_FIELD_PRODUCT_DESCRIPTION_QUESTION = 'dsu_engage_mandatory_field_product_description_question';
  const MANDATORY_FIELD_PRODUCT_DESCRIPTION_COMPLAINT = 'dsu_engage_mandatory_field_product_description_complaint';
  const MANDATORY_FIELD_PRODUCT_DESCRIPTION_PRAISE = 'dsu_engage_mandatory_field_product_description_praise';

  const MANDATORY_FIELD_FIRST_NAME_QUESTION = 'dsu_engage_mandatory_field_first_name_question';
  const MANDATORY_FIELD_FIRST_NAME_COMPLAINT = 'dsu_engage_mandatory_field_first_name_complaint';
  const MANDATORY_FIELD_FIRST_NAME_PRAISE = 'dsu_engage_mandatory_field_first_name_praise';

  const MANDATORY_FIELD_LAST_NAME_QUESTION = 'dsu_engage_mandatory_field_last_name_question';
  const MANDATORY_FIELD_LAST_NAME_COMPLAINT = 'dsu_engage_mandatory_field_last_name_complaint';
  const MANDATORY_FIELD_LAST_NAME_PRAISE = 'dsu_engage_mandatory_field_last_name_praise';

  const MANDATORY_FIELD_PREFERRED_CHANNEL_QUESTION = 'dsu_engage_mandatory_field_preferred_channel_question';
  const MANDATORY_FIELD_PREFERRED_CHANNEL_COMPLAINT = 'dsu_engage_mandatory_field_preferred_channel_complaint';
  const MANDATORY_FIELD_PREFERRED_CHANNEL_PRAISE = 'dsu_engage_mandatory_field_preferred_channel_praise';

  const MANDATORY_FIELD_EMAIL_QUESTION = 'dsu_engage_mandatory_field_email_question';
  const MANDATORY_FIELD_EMAIL_COMPLAINT = 'dsu_engage_mandatory_field_email_complaint';
  const MANDATORY_FIELD_EMAIL_PRAISE = 'dsu_engage_mandatory_field_enail_praise';

  const MANDATORY_FIELD_PHONE_QUESTION = 'dsu_engage_mandatory_field_phone_question';
  const MANDATORY_FIELD_PHONE_COMPLAINT = 'dsu_engage_mandatory_field_phone_complaint';
  const MANDATORY_FIELD_PHONE_PRAISE = 'dsu_engage_mandatory_field_phone_praise';

  const MANDATORY_FIELD_BAR_CODE_QUESTION = 'dsu_engage_mandatory_field_bar_code_question';
  const MANDATORY_FIELD_BAR_CODE_COMPLAINT = 'dsu_engage_mandatory_field_bar_code_complaint';
  const MANDATORY_FIELD_BAR_CODE_PRAISE = 'dsu_engage_mandatory_field_bar_code_praise';

  const MANDATORY_FIELD_BEST_BEFORE_DATE_QUESTION = 'dsu_engage_mandatory_field_best_before_date_question';
  const MANDATORY_FIELD_BEST_BEFORE_DATE_COMPLAINT = 'dsu_engage_mandatory_field_best_before_date_complaint';
  const MANDATORY_FIELD_BEST_BEFORE_DATE_PRAISE = 'dsu_engage_mandatory_field_best_before_date_praise';

  const MANDATORY_FIELD_STREET_QUESTION = 'dsu_engage_mandatory_field_street_question';
  const MANDATORY_FIELD_STREET_COMPLAINT = 'dsu_engage_mandatory_field_street_complaint';
  const MANDATORY_FIELD_STREET_PRAISE = 'dsu_engage_mandatory_field_street_praise';

  const MANDATORY_FIELD_CITY_QUESTION = 'dsu_engage_mandatory_field_city_question';
  const MANDATORY_FIELD_CITY_COMPLAINT = 'dsu_engage_mandatory_field_city_complaint';
  const MANDATORY_FIELD_CITY_PRAISE = 'dsu_engage_mandatory_field_city_praise';

  const MANDATORY_FIELD_ZIP_CODE_QUESTION = 'dsu_engage_mandatory_field_zip_code_question';
  const MANDATORY_FIELD_ZIP_CODE_COMPLAINT = 'dsu_engage_mandatory_field_zip_code_complaint';
  const MANDATORY_FIELD_ZIP_CODE_PRAISE = 'dsu_engage_mandatory_field_zip_code_praise';

  const MANDATORY_FIELD_STATE_QUESTION = 'dsu_engage_mandatory_field_state_question';
  const MANDATORY_FIELD_STATE_COMPLAINT = 'dsu_engage_mandatory_field_state_complaint';
  const MANDATORY_FIELD_STATE_PRAISE = 'dsu_engage_mandatory_field_state_praise';

  const MANDATORY_FIELD_COUNTRY_QUESTION = 'dsu_engage_mandatory_field_country_question';
  const MANDATORY_FIELD_COUNTRY_COMPLAINT = 'dsu_engage_mandatory_field_country_complaint';
  const MANDATORY_FIELD_COUNTRY_PRAISE = 'dsu_engage_mandatory_field_country_praise';

  const MANDATORY_FIELD_ATTACHMENTS_QUESTION = 'dsu_engage_mandatory_field_attachments_question';
  const MANDATORY_FIELD_ATTACHMENTS_COMPLAINT = 'dsu_engage_mandatory_field_attachments_complaint';
  const MANDATORY_FIELD_ATTACHMENTS_PRAISE = 'dsu_engage_mandatory_field_attachments_praise';

}
