<?php

namespace Drupal\ln_tint_connector;

interface LnTintConstants
{
  const TINT_IFRAME_SELECT_OPTION_CLICKFORME = 'clickformore';
  const TINT_IFRAME_SELECT_OPTIONS_PAGINATE = 'paginate';
  const TINT_IFRAME_SELECT_OPTIONS_INFINITE = 'infinite';

  const TINT_IFRAME_SELECT_OPTIONS = [
    self::TINT_IFRAME_SELECT_OPTION_CLICKFORME => 'Clickformore',
    self::TINT_IFRAME_SELECT_OPTIONS_PAGINATE => 'Paginate',
    self::TINT_IFRAME_SELECT_OPTIONS_INFINITE => 'Infinite Scroll',
  ];

  const TINT_SELECT_OPTIONS_CUSTOM = 'custom';
  const TINT_SELECT_OPTIONS_IFRAME = 'iframe';

  const TINT_SELECT_OPTIONS = [
    self::TINT_SELECT_OPTIONS_CUSTOM => 'Custom',
    self::TINT_SELECT_OPTIONS_IFRAME => 'Iframe',
  ];

  const API_URL = 'https://api.tintup.com/v2/tints/';
}
