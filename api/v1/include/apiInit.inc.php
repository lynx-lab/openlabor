<?php

  /*
   * ADA common data handler
   */
  $common_dh = $GLOBALS['common_dh'];
  if(!$common_dh instanceof AMA_Common_DataHandler) {
    $common_dh = AMA_Common_DataHandler::instance();
    $GLOBALS['common_dh'] = $common_dh;
  }
