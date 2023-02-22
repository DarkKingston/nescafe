<?php


namespace Drupal\ln_seo_hreflang_content;


interface LnHreflangContentConstants {

  /**
   * Migration Id.
   */
  const MIGRATION_LN_HREFLANG_IMPORT_ID = 'ln_hreflang_import';

  /**
   * Migration csv headers
   */
  const CSV_LN_HREFLANG_IMPORT_HEADERS = ['Path', 'Links'];

  /**
   * Import batch size
   */
  const LN_HREFLANG_IMPORT_BATCH_SIZE = 200;

  /**
   * CSV header Offset
   */
  const CSV_READER_HEADER_OFFSET = 0;

  /**
   * CSV header delimiter
   */
  const CSV_READER_HEADER_DELIMITER = ';';

}