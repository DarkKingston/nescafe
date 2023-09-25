<?php

namespace Drupal\ln_campaign\Plugin\Field\FieldWidget;

/**
 * A widget for inport data string from csv.
 *
 * @FieldWidget(
 *   id = "ln_campaign_csv_string",
 *   label = @Translation("Campaign CSV String"),
 *   field_types = {
 *     "string"
 *   },
 *   multiple_values = TRUE
 * )
 */

class LnCampaignCSVString extends LnCampaignCSV {

  /**
   * {@inheritdoc}
   */
  public function getCsvColumns(){
    return ['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTableHeader(){
    return [$this->t('Value')];
  }

  public function checkRow($row){
    $columns = $this->getCsvColumns();
    if(count($columns) != count($row)){
      return FALSE;
    }

    return TRUE;
  }
}
