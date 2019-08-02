<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RESTResponse {
  // 40*
  const BAD_REQUEST           = 400;
  const UN_AUTHORIZED         = 401;
  const FORBIDDEN             = 403;
  const NOT_ACCEPTABLE        = 406;
  const TOO_MANY_REQUESTS     = 429;
  // 50*
  const INTERNAL_SERVER_ERROR = 500;
  const NOT_IMPLEMENTED       = 501;
}
