<?php

namespace Drupal\ln_campaign\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * A widget for inport date from csv.
 *
 * @FieldWidget(
 *   id = "ln_campaign_csv_daterange",
 *   label = @Translation("Campaign CSV Daterange"),
 *   field_types = {
 *     "daterange"
 *   },
 *   multiple_values = TRUE
 * )
 */

class LnCampaignCSVDaterange extends LnCampaignCSV {

  /**
   * {@inheritdoc}
   */
  public function getCsvColumns(){
    return ['value','end_value'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTableHeader(){
    return [$this->t('Start Date'), $this->t('End Date')];
  }

  public function checkRow($row){
    $columns = $this->getCsvColumns();
    if(count($columns) != count($row)){
      return FALSE;
    }
    foreach ($columns as $key => $column){
      $date = new DrupalDateTime($row[$key]);
      if($date->hasErrors()){
        return FALSE;
      }
    }
    return TRUE;
  }
}
