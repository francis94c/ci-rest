[![Build Status](https://travis-ci.org/francis94c/ci-rest.svg?branch=master)](https://travis-ci.org/francis94c/ci-rest) [![Coverage Status](https://coveralls.io/repos/github/francis94c/ci-rest/badge.svg?branch=master)](https://coveralls.io/github/francis94c/ci-rest?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/francis94c/ci-rest/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/francis94c/ci-rest/?branch=master)

# ci-rest
A REST API Library/Framework for Code Igniter.

This library is currently in progress together with another project that depends on it, so as to enable me design it in such a way that cuts through various applications.

I'll be documenting along the way, before the full code finally makes it here for its first release.

# Synopsis

This library makes it possible to develop REST servers in Code Igniter, having your code responsible for validating authorizations in one place and the codes for actually accessing resources in another place.

This allows you write uniform maintainable server-side code.

Think of it as a library that validates clients as per your specifications, and let's your main code run if authorization passes.

You also get to take actions or customize responses when authorization fails.

This also takes care of API Rate Limiting.

A very detailed write up of how to install and use this library follows below.


### Installation ###
Download and Install Splint from https://splint.cynobit.com/downloads/splint and run the below from the root of your Code Igniter project.
```bash
splint install francis94c/ci-rest
```

### Usage ###
You can load the library anywhere as below.
```php
$this->load->package('francis94c/ci-rest');
```
If your Code Igniter is a full REST API, It'll be better to add the package name to the `$config['splint']` array in `config/autoload.php`.
```php
$config['splint'] [
  'francis94c/ci-rest'
];
```
Then create authentication rules amongst other rules that must be processed in a file `config/rest.php` every time the library is loaded.
Below is a sample config file
```php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['api_key_header'] = "X-API-KEY";

$config['auth'] = [
  RESTAuth::BASIC,
  RESTAuth::API_KEY => REST::AUTH_GRAVITY,
  RESTAuth::BEARER  => REST::AUTH_PASSIVE | REST::AUTH_FINAL,
  RESTAuth::SECRET
];

$config['auth_callbacks'] = [
  RESTAuth::SECRET => function (&$context, $token):bool {
    return $token == 'valid';
  },
  RESTAuth::BEARER => function (&$context, $token) {
    return false;
  }
];

$config['response_callbacks'] = [
  RESTResponse::BAD_REQUEST => function(&$auth, $reason):void {
    echo(json_encode([
      'error' => $reason
    ]));
  },
  RESTResponse::UN_AUTHORIZED => function(&$auth):void {
    echo(json_encode([
      'error' => 'UnAuthorized'
    ]));
  },
  RESTResponse::NOT_ACCEPTABLE => function(&$auth):void {
    echo (json_encode([
      'error' => 'Not Acceptable'
    ]));
  },
  RESTResponse::NOT_IMPLEMENTED => function(&$auth): void {
    echo (json_encode([
      'error' => "$auth Authentication not implemented"
    ]));
  }
];

$config['api_limiter'] = [
  'api_limiter'    => true,
  'per_hour'       => 100,
  'show_header'    => true,
  'header_prefix'  => 'X-RateLimit-',
  'limit_by_ip'    => false,
  'ip_per_hour'    => 50,
  'whitelist'      => [
    '127.0.0.1',
    '::1'
  ]
];

$config['api_key_auth'] = [
  'api_key_table'        => 'api_keys',
  'api_key_column'       => 'api_key',
  'api_key_limit_column' => '_limit',
];
```
