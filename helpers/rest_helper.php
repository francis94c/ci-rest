<?php
declare(strict_types=1);

if (!function_exists('response')) {
  function response($data, int $code):RESTResponse
  {
    return new RESTResponse($data, $code);
  }
}
