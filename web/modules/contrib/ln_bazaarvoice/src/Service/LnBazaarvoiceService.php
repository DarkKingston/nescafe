<?php

namespace Drupal\ln_bazaarvoice\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ln_bazaarvoice\LnBazaarvoiceConstants;
use Drupal\token\TokenInterface;
use function explode;

/**
 * Class LnBazaarvoiceService.
 */
class LnBazaarvoiceService implements LnBazaarvoiceServiceInterface {

  /**
   * Bazaarvoice settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */

  protected $config;

  /**
   * Configuration state Drupal Site.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */

  protected $languageManager;

  /**
   * The token service.
   *
   * @var \Drupal\token\TokenInterface
   */
  protected $token;

  /**
   * LnBazaarvoiceService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\token\TokenInterface $token
   *   The token service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $languageManager, TokenInterface $token) {
    $this->config = $config_factory->get('ln_bazaarvoice.settings');
    $this->languageManager = $languageManager;
    $this->token = $token;
  }

  /**
   * @inheritdoc
   */
  public function getBazaarvoiceJsPath() {
    $client_name = $this->config->get('client_name');
    $site_id = $this->config->get('site_id');

    $environment = 'staging';
    if ($this->config->get('environment') == LnBazaarvoiceConstants::ENVIRONMENT_PRO) {
      $environment = 'production';
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $locale = $this->config->get('locale')[$langcode] ?? '';

    return "https://apps.bazaarvoice.com/deployments/{$client_name}/{$site_id}/{$environment}/{$locale}/bv.js";
  }

  /**
   * @inheritdoc
   */
  public function getProductInfo($bazaarvoice_id, EntityInterface $entity, $dcc_info) {
    $mapping = [];
    $dcc_attrs = explode("\n", $dcc_info);
    foreach ($dcc_attrs as $dcc_attr){
      $dcc_attr = trim($dcc_attr);
      [$dcc_attribute, $token] = explode('|', $dcc_attr);
      if(!empty($dcc_attribute) && !empty($token)){
        $mapping[trim($dcc_attribute)] = $this->token->replace(trim($token), [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
      }
    }
    $mapping = ['productId' => $bazaarvoice_id] + $mapping;
    return $mapping;
  }
}
