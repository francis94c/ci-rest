<?php
declare(strict_types=1);

if (!function_exists('response')) {
  function response($data=null, int $code=null):RESTResponse
  {
    return new RESTResponse($data, $code);
  }
}
