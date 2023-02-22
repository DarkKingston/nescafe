<?php

namespace Drupal\ln_price_spider\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class LnPriceSpider.
 *
 * @package Drupal\ln_price_spider\Service
 */
class LnPriceSpiderService implements LnPriceSpiderServiceInterface {

  /**
   * LnPriceSpider constructor.
   *
   * @param $config
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct($config, Connection $database, LanguageManagerInterface $languageManager) {
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool|mixed|string|string[]|null
   */
  public function getEntityLocale(EntityInterface $entity) {
    $langcode = method_exists($entity, 'language') ? $entity->language()
      ->getId() : $this->langaugeManager->getCurrentLanguage()->getId();

    return $this->getBazaarvoiceLocaleCode($langcode);
  }

  /**
   * @param $locale_code
   *
   * @return false|int
   */
  public function isValidLocaleCode($locale_code) {
    return preg_match('/[a-z]{2}_[A-Z]{2}/', $locale_code);
  }

  /**
   * @param $locale
   *
   * @return bool|mixed
   */
  public function getPriceSpiderCountryCode($locale) {
    $locale_codes = $this->defaultLocaleCodes();

    return isset($locale_codes[$locale]) ? $locale_codes[$locale] : FALSE;
  }

  /**
   * @return array
   */
  private function defaultLocaleCodes() {
    return [
      'af' => 'NA',
      'ak' => 'GH',
      'sq' => 'AL',
      'am' => 'ET',
      'ar' => 'DZ',
      'hy' => 'AM',
      'as' => 'IN',
      'az' => 'AZ',
      'eu' => 'ES',
      'be' => 'BY',
      'bn' => 'BD',
      'bs' => 'BA',
      'br' => 'FR',
      'bg' => 'BG',
      'my' => 'MM',
      'ca' => 'AD',
      'zh' => 'CN',
      'kw' => 'GB',
      'hr' => 'HR',
      'cs' => 'CZ',
      'da' => 'DK',
      'nl' => 'NL',
      'dz' => 'BT',
      'en' => 'US',
      'eo' => 'EO',
      'et' => 'EE',
      'ee' => 'GH',
      'fo' => 'FO',
      'fi' => 'FI',
      'fr' => 'FR',
      'ff' => 'CM',
      'gl' => 'ES',
      'lg' => 'UG',
      'ka' => 'GE',
      'de' => 'DE',
      'el' => 'GR',
      'gu' => 'IN',
      'ha' => 'GH',
      'he' => 'IL',
      'hi' => 'IN',
      'hu' => 'HU',
      'is' => 'IS',
      'ig' => 'NG',
      'id' => 'ID',
      'ga' => 'IE',
      'it' => 'IT',
      'ja' => 'JP',
      'kl' => 'GL',
      'kn' => 'IN',
      'ks' => 'IN',
      'kk' => 'KZ',
      'km' => 'KH',
      'ki' => 'KE',
      'rw' => 'RW',
      'ko' => 'KR',
      'ky' => 'KG',
      'lo' => 'LA',
      'lv' => 'LV',
      'ln' => 'AO',
      'lt' => 'LT',
      'lu' => 'CD',
      'lb' => 'LU',
      'mk' => 'MK',
      'mg' => 'MG',
      'ms' => 'BN',
      'ml' => 'IN',
      'mt' => 'MT',
      'gv' => 'IM',
      'mr' => 'IN',
      'mn' => 'MN',
      'ne' => 'IN',
      'nd' => 'ZW',
      'se' => 'SE',
      'no' => 'NO',
      'nb' => 'NO',
      'nn' => 'NO',
      'or' => 'IN',
      'om' => 'ET',
      'os' => 'GE',
      'ps' => 'AF',
      'fa' => 'AF',
      'pl' => 'PL',
      'pt' => 'PT',
      'pa' => 'PK',
      'qu' => 'BO',
      'ro' => 'RO',
      'rm' => 'CH',
      'rn' => 'BI',
      'ru' => 'RU',
      'sg' => 'CF',
      'gd' => 'GB',
      'sr' => 'RS',
      'sh' => 'BA',
      'sn' => 'ZW',
      'ii' => 'CN',
      'si' => 'LK',
      'sk' => 'SK',
      'sl' => 'SI',
      'so' => 'SO',
      'es' => 'ES',
      'sw' => 'KE',
      'sv' => 'SE',
      'tl' => 'PH',
      'ta' => 'IN',
      'te' => 'IN',
      'th' => 'TH',
      'bo' => 'CN',
      'ti' => 'ER',
      'to' => 'TO',
      'tr' => 'TR',
      'uk' => 'UA',
      'ur' => 'IN',
      'ug' => 'CN',
      'uz' => 'UZ',
      'vi' => 'VN',
      'cy' => 'GB',
      'fy' => 'NL',
      'yi' => 'YI',
      'yo' => 'BJ',
      'zu' => 'ZA',
    ];
  }

  /**
   * @return string
   */
  public function getPriceSpiderLangCode() {
    return \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

}
