<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RESTAuth {
  /**
   * [BasicAuth description]
   * @var string
   */
  const BASIC   = "Basic";
  /**
   * [API_KEY description]
   * @var string
   */
  const API_KEY = "ApiKey";
  /**
   * [BEARER description]
   * @var string
   */
  const BEARER  = "Bearer";
  /**
   * [OAUTH2 description]
   * @var string
   */
  const OAUTH2  = "OAUTH2";
  /**
   * [CUSTOM description]
   * @param  string $header [description]
   * @return string         [description]
   */
  public static function CUSTOM(string $header):string {
    return 'HTTP_'.str_replace('-', '_', $header);
  }
}
?>
