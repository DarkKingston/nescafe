<?php

namespace Drupal\ln_srh_full;

class SRHComplementsBatchConfiguration {

  /**
   * @var SRHComplementsBatchConfiguration|null
   */
  private static $defaultConfiguration = NULL;

  /**
   * @var null|array
   */
  protected $complementIds;

  /**
   * @var boolean
   */
  protected $onlyPublished = false;

  /**
   * @var int
   *
   * Number of returned complements per page (Max: 100)
   */
  protected $pageSize;

  /**
   * @var int
   *
   * Pages to synchronize in each batch
   */
  protected $batchPages;

  /**
   * @var int
   *
   * Complements to synchronize in each batch.
   */
  protected $batchComplements;

  /**
   * @var string
   */
  protected $locale;

  /**
   * @var int
   *
   * Timestamp - only complements that were updated after this date will be
   *   synchronized.
   */
  protected $fromDate = 0;

  /**
   * @var bool
   *
   * Remove synchronized complements from cron queue
   */
  protected $clearCronQueue = false;

  /**
   * Gets the default configuration instance
   *
   * @return SRHComplementsBatchConfiguration
   */
  public static function getDefaultConfiguration() {
    if (self::$defaultConfiguration === NULL) {
      self::$defaultConfiguration = new SRHComplementsBatchConfiguration();
    }

    return self::$defaultConfiguration;
  }

  /**
   * Sets the default configuration instance
   *
   * @param SRHComplementsBatchConfiguration $config An instance of the SRHComplementsBatchConfiguration Object
   */
  public static function setDefaultConfiguration(SRHComplementsBatchConfiguration $config) {
    self::$defaultConfiguration = $config;
  }

  /**
   * @return array|null
   */
  public function getComplementIds(): ?array {
    return $this->complementIds;
  }

  /**
   * @param array|null $complementIds
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setComplementIds(?array $complementIds) {
    $this->complementIds = $complementIds;

    return $this;
  }

  /**
   * @return bool
   */
  public function isOnlyPublished(): bool {
    return $this->onlyPublished;
  }

  /**
   * @param bool $onlyPublished
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setOnlyPublished(bool $onlyPublished) {
    $this->onlyPublished = $onlyPublished;

    return $this;
  }

  /**
   * @return int
   */
  public function getPageSize() {
    return $this->pageSize;
  }

  /**
   * @param int $pageSize
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setPageSize(int $pageSize) {
    $this->pageSize = $pageSize;

    return $this;
  }

  /**
   * @return int
   */
  public function getBatchPages() {
    return $this->batchPages;
  }

  /**
   * @param int $batchPages
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setBatchPages(int $batchPages) {
    $this->batchPages = $batchPages;

    return $this;
  }

  /**
   * @return int
   */
  public function getBatchComplements() {
    return $this->batchComplements;
  }

  /**
   * @param int $batchComplements
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setBatchComplements(int $batchComplements) {
    $this->batchComplements = $batchComplements;

    return $this;
  }

  /**
   * @return int
   */
  public function getFromDate() {
    return $this->fromDate;
  }

  /**
   * @param int $fromDate
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setFromDate(int $fromDate) {
    $this->fromDate = $fromDate;

    return $this;
  }

  /**
   * @return bool
   */
  public function isClearCronQueue(): bool {
    return $this->clearCronQueue;
  }

  /**
   * @param bool $clearCronQueue
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setClearCronQueue(bool $clearCronQueue) {
    $this->clearCronQueue = $clearCronQueue;

    return $this;
  }

  /**
   * @return string
   */
  public function getLocale() {
    return $this->locale;
  }

  /**
   * @param string $locale
   *
   * @return \Drupal\ln_srh_full\SRHComplementsBatchConfiguration
   */
  public function setLocale(string $locale) {
    $this->locale = $locale;

    return $this;
  }

}
