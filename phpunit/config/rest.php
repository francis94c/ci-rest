<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['api_key_header'] = "X-API-KEY";

$config['uri_auth'] = [
  'basic/auth' => [RESTAuth::BASIC]
];

$config['auth_callbacks'] = [

  RESTAuth::CUSTOM('X-APP-ID')    => function (&$context, $value):bool {
    return true;
  },

  RESTAuth::CUSTOM('X-DEVICE-ID') => function (&$context, $value):bool {
    return true;
  },

  RESTAuth::BEARER                => function (&$context, $token):bool {
    return true;
  },

  RESTAuth::OAUTH2                => function (&$context, $token):bool {
    return true;
  }

];

$config['response_callbacks'] = [

  RESTResponse::BAD_REQUEST => function(&$auth):void {
    echo(json_encode([
      'error' => 'Bad Request'
    ]));
  },

  RESTResponse::UN_AUTHORIZED      => function(&$auth):void {
    echo (json_encode([
      'error' => 'Un-Authorized'
    ]));
  },

  RESTResponse::NOT_ACCEPTABLE     => function(&$auth):void {
    echo (json_encode([
      'error' => 'Not Acceptable'
    ]));
  },

  RESTResponse::NOT_IMPLEMENTED    => function(&$auth): void {
    echo (json_encode([
      'error' => "$auth Authentication not implemented"
    ]));
  }
];

$config['api_limiter'] = [
  'api_limiter'   => true,
  'per_hour'      => 100,
  'show_header'   => true,
  'header_prefix' => 'X-RateLimit-',
  'limit_by_ip'   => true,
  'ip_per_hour'   => 50,
  'whitelist'   => [
    '127.0.0.1',
    '::1'
  ]
];

$config['basic_auth'] = [
  'users_table'     => 'users',
  'id_column'       => 'id',
  'email_column'    => 'email',
  'password_column' => 'password',
  'username_column' => 'username'
];

$config['api_key_auth'] = [
  'api_key_table'        => 'api_keys',
  'api_key_column'       => 'api_key',
  'api_key_limit_column' => '_limit',
];
