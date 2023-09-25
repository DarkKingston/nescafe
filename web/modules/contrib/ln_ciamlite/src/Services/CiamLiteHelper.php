<?php

namespace Drupal\ln_ciamlite\Services;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;

class CiamLiteHelper implements CiamLiteHelperInterface{

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory){
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function buildGigyaNewsletterScreen($unique_id){
    $ln_ciamlite_settings = $this->configFactory->get('ln_ciamlite.settings');
    if (!empty($this->configFactory->get('gigya.settings')->get('gigya.gigya_api_key'))
      && $this->configFactory->get('gigya.global')->get('gigya.enableRaaS') == TRUE
      && $ln_ciamlite_settings->get('gigya.screen_set') && $ln_ciamlite_settings->get('gigya.mobile_screen_set')
      && $ln_ciamlite_settings->get('gigya.start_screen')) {

      $session_type = $this->configFactory->get('gigya_raas.settings')->get('gigya_raas.session_type');
      $session_time = $this->configFactory->get('gigya_raas.settings')->get('gigya_raas.session_time');
      $expiration = $session_type == "dynamic" ? -1 : $session_time;


      $unique_screen_id = Html::getUniqueId($unique_id);
      $screen = array(
        'screenSet'       => $ln_ciamlite_settings->get('gigya.screen_set'),
        'mobileScreenSet' => $ln_ciamlite_settings->get('gigya.mobile_screen_set'),
        'startScreen'     => $ln_ciamlite_settings->get('gigya.start_screen'),
        'sessionExpiration' => $expiration,
      );
      $attachments['drupalSettings']['gigya']['raas'][$unique_screen_id] = $screen;
      $attachments['library'][] = 'ln_ciamlite/LnCiamliteGigyaBlock';

      return [
        '#theme' => 'ln_ciamlite_gigya_screen_block',
        '#screen_id' => $ln_ciamlite_settings->get('gigya.start_screen'),
        '#element_id' => $unique_screen_id,
        '#attached' => $attachments
      ];
    }

    return [];
  }

}
