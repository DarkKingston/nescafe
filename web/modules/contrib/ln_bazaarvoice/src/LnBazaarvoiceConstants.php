<?php

namespace Drupal\ln_bazaarvoice;


interface LnBazaarvoiceConstants {
  const ENVIRONMENT_STAG = 'staging';
  const ENVIRONMENT_PRO = 'production';

  const MODE_RATING_SUMMARY = 'rating_summary';
  const MODE_REVIEWS = 'reviews';
  const MODE_QUESTIONS = 'questions';
  const MODE_REVIEW_HIGHLIGHTS = 'review_highlights';
  const MODE_INLINE_RATING = 'inline_rating';
  const MODE_SELLER_RATINGS = 'seller_ratings';

  const OLD_FIELD_NAME = 'field_bv_product_id';
}
