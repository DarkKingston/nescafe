<?php

namespace Drupal\ln_campaign\Element;

use Drupal\Core\Render\Element\Email;


/**
 * Provides a form input element for entering an email address.
 *
 * Properties:
 * - #default_value: An RFC-compliant email address.
 * - #size: The size of the input element in characters.
 * - #pattern: A string for the native HTML5 pattern attribute.
 *
 * Example usage:
 * @code
 * $form['email'] = [
 *   '#type' => 'ln_campaign_email',
 *   '#title' => $this->t('Email'),
 *   '#pattern' => '*@example.com',
 * ];
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Textfield
 *
 * @FormElement("ln_campaign_email")
 */
class LnCEmail extends Email {


}
