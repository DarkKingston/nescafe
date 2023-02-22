<?php

namespace Drupal\ln_campaign\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\ln_campaign\Entity\LnCampaign;

class LnCampaignHelper{

  public function checkTimeOutPromotion(LnCampaign $campaign){
    /** @var DrupalDateTime $date_ini */
    $date_ini = $campaign->get('active_date')->start_date;
    /** @var DrupalDateTime $date_end */
    $date_end = $campaign->get('active_date')->end_date;
    $now = new DrupalDateTime('now',$date_ini->getTimezone());
    if($now < $date_ini || $now > $date_end){
      return TRUE;
    }
    return FALSE;
  }

}
