<?php

namespace Drupal\ln_ciam\Utility;

use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGenerator as BaseLinkGenerator;
use Drupal\ln_ciam\LnCiamConstants;

class LinkGenerator extends BaseLinkGenerator {
  use StringTranslationTrait;

  public function generate($text, Url $url) {
    if($url->isRouted()){
      $config = \Drupal::config('ln_ciam.settings');

      $attributes = $url->getOption('attributes');
      if(empty($attributes)){
        $attributes=[];
      }
      switch ($url->getRouteName()){
        case 'user.login':
          if($config->get('enable_login')){
            $attributes['id'] = Html::getUniqueId(LnCiamConstants::LINK_LOGIN_CSS_CLASS);
            $attributes['class'][] = LnCiamConstants::LINK_LOGIN_CSS_CLASS;
          }
          break;
        case 'user.register':
          if($config->get('enable_register')){
            $attributes['id'] = Html::getUniqueId(LnCiamConstants::LINK_REGISTER_CSS_CLASS);
            $attributes['class'][] = LnCiamConstants::LINK_REGISTER_CSS_CLASS;
          }
          break;
      }

      $url->setOption('attributes', $attributes);
    }

    return parent::generate($text, $url);
  }
}
