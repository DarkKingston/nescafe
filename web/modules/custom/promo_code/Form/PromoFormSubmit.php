<?php

namespace Drupal\promo_code\Form;

use Drupal\Core\Form\FormStateInterface;

class PromoFormSubmit {

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Обработка полученных данных из формы
    // ...
    print_r($_POST);
    drupal_set_message('Форма успешно отправлена');
  }
}
