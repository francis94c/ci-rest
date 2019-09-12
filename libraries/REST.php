<?php
declare(strict_types=1);
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('RESTAuth.php');
require_once('RESTResponse.php');
require_once('RESTExceptions.php');

class REST {

  /**
   * [private description]
   * @var [type]
   */
  private $ci;
  /**
   * [private description]
   * @var [type]
   */
  private $api_key_limit_column;
  /**
   * [private description]
   * @var [type]
   */
  private $api_key_column;
  /**
   * [private description]
   * @var [type]
   */
  private $per_hour;
  /**
   * [private description]
   * @var [type]
   */
  private $ip_per_hour;
  /**
   * [private description]
   * @var [type]
   */
  private $show_header;
  /**
   * [private description]
   * @var [type]
   */
  private $whitelist;
  /**
   * [private description]
   * @var [type]
   */
  private $checked_rate_limit = false;
  /**
   * [private description]
   * @var [type]
   */
  private $header_prefix;
  /**
   * [private description]
   * @var [type]
   */
  private $limit_api;

  /**
   * [public description]
   * @var [type]
   */
  public  $userId;
  /**
   * [public description]
   * @var [type]
   */
  public  $apiKeyHeader;
  /**
   * [public description]
   * @var [type]
   */
  public  $token;
  /**
   * [public description]
   * @var [type]
   */
  public $allowedIPs;
  /**
   * [PACKAGE description]
   * @var string
   */
  const   PACKAGE    = "francis94c/ci-rest";
  /**
   * [RATE_LIMIT description]
   * @var string
   */
  const   RATE_LIMIT = "RateLimit";

  /**
   * [__construct This is the part of the code that takes care of all
   * authentiations. allowijg you to focus on build wonderfult things at REST.
   * pun intended ;-)]
   * @param array|null $params Initialization parameters from the Slint system.
   *                           There's no use for this arg yet.
   */
  function __construct($params=null) {
    $this->ci =& get_instance();
    // Load Config If Exists.
    $this->ci->config->load('rest', true, true);
    // Load Database.
    $this->ci->load->database();
    // Load Model.
    $this->ci->load->splint(self::PACKAGE, '*RESTModel', 'rest_model');
    $this->rest_model =& $this->ci->rest_model;
    $config = [
      'users_table'           => $this->ci->config->item('rest')['basic_auth']['users_table'] ?? null,
      'users_id_column'       => $this->ci->config->item('rest')['basic_auth']['id_column'] ?? null,
      'users_username_column' => $this->ci->config->item('rest')['basic_auth']['username_column'] ?? null,
      'users_email_column'    => $this->ci->config->item('rest')['basic_auth']['email_column'] ?? null,
      'users_password_column' => $this->ci->config->item('rest')['basic_auth']['password_column'] ?? null,
      'api_key_table'         => $this->ci->config->item('rest')['api_key_auth']['api_key_table'] ?? null,
      'api_key_column'        => $this->ci->config->item('rest')['api_key_auth']['api_key_column'] ?? null,
      'api_key_limit_column'  => $this->ci->config->item('rest')['api_key_auth']['api_key_limit_column'] ?? null
    ];
    $this->rest_model->init($config);
    // Load Variable(s) from Config.
    $this->allowedIPs = $this->ci->config->item('rest')['allowed_ips'] ?? ['127.0.0.1', '[::1]'];
    $this->apiKeyHeader = $this->ci->config->item('rest')['api_key_header'] ?? 'X-API-KEY';
    $this->api_key_limit_column = $this->ci->config->item('rest')['api_key_auth']['api_key_limit_column'] ?? null;
    $this->api_key_column = $this->ci->config->item('rest')['api_key_auth']['api_key_column'] ?? null;
    $this->limit_api = $this->ci->config->item('rest')['api_limiter']['api_limiter'] ?? false;
    $this->per_hour = $this->ci->config->item('rest')['api_limiter']['per_hour'] ?? 100;
    $this->ip_per_hour = $this->ci->config->item('rest')['api_limiter']['ip_per_hour'] ?? 50;
    $this->show_header = $this->ci->config->item('rest')['api_limiter']['show_header'] ?? null;
    $this->whitelist = $this->ci->config->item('rest')['api_limiter']['whitelist'] ?? null;
    $this->header_prefix = $this->ci->config->item('rest')['api_limiter']['header_prefix'] ?? 'X-RateLimit-';
    // Authenticate
    $this->authenticate();

    // Generic Rate Limiter.
    if ($this->limit_api && !$this->checked_rate_limit &&
    ($this->ci->config->item('rest')['api_limiter']['limit_by_ip'] ?? false)) {
      $this->api_rest_limit_by_ip_address();
    }

    log_message('debug', 'REST Request Authenticated and REST Library Initialized.');
  }
  /**
   * [authenticate description]
   */
  private function authenticate():void {
    $uri_auths = $this->ci->config->item('rest')['uri_auth'] ?? null;
    // Match Auth Routes.
    // The below algorithm is similar to the one Code Igniter uses in its
    // Routing Class.
    if ($uri_auths == null || !is_array($uri_auths)) return;
    $auths = null;
    foreach ($uri_auths as $uri => $auth_array) {
      // Convert wildcards to RegEx.
			$uri = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $uri);
      if (preg_match('#^'.$uri.'$#', uri_string())) $auths = $auth_array; // Assign Authentication Steps.
      break;
    }
    //$auths = $this->ci->config->item('rest')['uri_auth'][uri_string()] ?? null;
    if ($auths == null) return; // No authentication(s) to acrry out.
    // $this->process_auth() terminates the script if authentication fails
    // It will call the callable in the rest.php config file under
    // response_callbacks which matches the necesarry RESTResponse constant
    // before exiting. Which callable is called in any situation is documented
    // in README.md
    if (is_scalar($auths)) {
      $this->process_auth($auths);
      return;
    }
    foreach ($auths as $auth) $this->process_auth($auth);
  }
  /**
   * [process_auth description]
   * @param  string $auth [description]
   * @return bool         [description]
   */
  private function process_auth(string &$auth):void {
    switch ($auth) {
      case RESTAuth::IP: $this->ip_auth(); break;
      case RESTAuth::BASIC: $this->basic_auth(); break;
      case RESTAuth::API_KEY: $this->api_key_auth(); break;
      case RESTAuth::OAUTH2: $this->bearer_auth(RESTAuth::OAUTH2); break;
      case RESTAuth::BEARER: $this->bearer_auth(); break;
      default: $this->custom_auth($auth);
    }
  }
  /**
   * [ip_auth description]
   */
  private function ip_auth():void {
    if (!in_array($this->ci->input->ip_address(), $this->allowedIPs)) {
      $this->handle_response(RESTResponse::UN_AUTHORIZED, RESTAuth::IP); // Exits.
    }
  }
  /**
   * [bearer_auth description]
   */
  private function bearer_auth($auth=RESTAuth::BEARER):void {
    $authorization = $this->get_authorization_header();
    if ($authorization == null || substr_count($authorization, " ") != 1) {
      $this->handle_response(RESTResponse::BAD_REQUEST, $auth); // Exits.
    }
    $token = explode(" ", $authorization);
    if ($token[0] != "Bearer") {
      $this->handle_response(RESTResponse::BAD_REQUEST, $auth); // Exits.
    }
    $this->token = $token[1];
    // Call Up Custom Implemented Bearer/Token Authorization.
    // Callback Check.
    if (!isset($this->ci->config->item('rest')['auth_callbacks'][$auth])) {
      $this->handle_response(RESTResponse::NOT_IMPLEMENTED, $auth); // Exits.
    }
    // Authorization.
    if (!$this->ci->config->item('rest')['auth_callbacks'][$auth]($this, $this->token)) {
      $this->handle_response(RESTResponse::UN_AUTHORIZED, $auth); // Exits.
    }
  }
  /**
   * [basic_auth description]
   */
  private function basic_auth():void {
    $username = $_SERVER['PHP_AUTH_USER'] ?? null;
    $password = $_SERVER['PHP_AUTH_PW'] ?? null;
    if (!$username || !$password) $this->handle_response(RESTResponse::BAD_REQUEST, RESTAuth::BASIC); // Exits.
    if (!$this->rest_model->basicAuth($this, $username, $password)) $this->handle_response(RESTResponse::UN_AUTHORIZED, RESTAuth::BASIC); // Exits.
  }
  /**
   * [api_key_auth description]
   */
  private function api_key_auth():void {
    if (!isset($_SERVER['HTTP_' . str_replace("-", "_", $this->apiKeyHeader)])) {
      $this->handle_response(RESTResponse::BAD_REQUEST, RESTAuth::API_KEY); // Exits.
    }
    $apiKey = $this->rest_model->getAPIKeyData(
      $_SERVER['HTTP_' . str_replace("-", "_", $this->apiKeyHeader)]
    );
    if ($apiKey == null) {
      $this->handle_response(RESTResponse::UN_AUTHORIZED, RESTAuth::API_KEY); // Exits.
    }
    // API KEY Auth Passed Above.
    if ($this->limit_api && $this->api_key_limit_column != null && $apiKey[$this->api_key_limit_column] == 1) {
      // Trunctate Rate Limit Data.
      $this->rest_model->truncateRatelimitData();
      // Check Whitelist.
      if (in_array($this->ci->input->ip_address(), $this->whitelist)) {
        $this->checked_rate_limit = true; // Ignore Limit By IP.
        return;
      }
      // Should we acyually Limit?
      if ($this->per_hour > 0) {
        $client = hash('md5', $this->ci->input->ip_address() . "%" . $apiKey[$this->api_key_column]);
        $limitData = $this->rest_model->getLimitData($client, '_api_keyed_user');
        if ($limitData == null) {
          $limitData = [];
          $limitData['count'] = 0;
          $limitData['reset_epoch'] = gmdate('d M Y H:i:s', time() + (60 * 60));
          $limitData['start'] = date('d M Y H:i:s');
        }
        if ($this->per_hour - $limitData['count'] > 0) {
          if (!$this->rest_model->insertLimitData($client, '_api_keyed_user')) {
            $this->handle_response(RESTResponse::INTERNAL_SERVER_ERROR, self::RATE_LIMIT); // Exits.
          }
          ++$limitData['count'];
          if ($this->show_header) {
            header($this->header_prefix.'Limit: '.$this->per_hour);
            header($this->header_prefix.'Remaining: '.($this->per_hour - $limitData['count']));
            header($this->header_prefix.'Reset: '.strtotime($limitData['reset_epoch']));
          }
        } else {
          header('Retry-After: '.(strtotime($limitData['reset_epoch']) - strtotime(gmdate('d M Y H:i:s'))));
          $this->handle_response(RESTResponse::TOO_MANY_REQUESTS, self::RATE_LIMIT); // Exits.
        }
      }
    }
    $this->checked_rate_limit = true; // Ignore Limit By IP.
  }
  /**
   * [api_rest_limit_by_ip_address description]
   * TODO: Implement.
   */
  private function api_rest_limit_by_ip_address():void {
    // Trunctate Rate Limit Data.
    $this->rest_model->truncateRatelimitData();
    // Check Whitelist.
    if (in_array($this->ci->input->ip_address(), $this->whitelist)) return;
    // Should we acyually Limit?
    if ($this->ip_per_hour > 0) {
      $client = hash('md5', $this->ci->input->ip_address());
      $limitData = $this->rest_model->getLimitData($client, '_ip_address');
      if ($limitData == null) {
        $limitData = [];
        $limitData['count'] = 0;
        $limitData['reset_epoch'] = gmdate('d M Y H:i:s', time() + (60 * 60));
        $limitData['start'] = date('d M Y H:i:s');
      }
      if ($this->ip_per_hour - $limitData['count'] > 0) {
        if (!$this->rest_model->insertLimitData($client, '_ip_address')) {
          $this->handle_response(RESTResponse::INTERNAL_SERVER_ERROR, self::RATE_LIMIT); // Exits.
        }
        ++$limitData['count'];
        if ($this->show_header) {
          header($this->header_prefix.'Limit: '.$this->ip_per_hour);
          header($this->header_prefix.'Remaining: '.($this->ip_per_hour - $limitData['count']));
          header($this->header_prefix.'Reset: '.strtotime($limitData['reset_epoch']));
        }
      } else {
        header('Retry-After: '.(strtotime($limitData['reset_epoch']) - strtotime(gmdate('d M Y H:i:s'))));
        $this->handle_response(RESTResponse::TOO_MANY_REQUESTS, self::RATE_LIMIT); // Exits.
      }
    }
  }
  /**
   * [custom_auth description]
   * @param string $auth [description]
   */
  private function custom_auth(string &$auth):void {
    // Header Check.
    if (!isset($_SERVER[$auth])) {
      $this->handle_response(RESTResponse::BAD_REQUEST, $auth);
    }
    // Callback Check.
    if (!isset($this->ci->config->item('rest')['auth_callbacks'][$auth])) {
      $this->handle_response(RESTResponse::NOT_IMPLEMENTED, $auth); // Exits.
    }
    // Authentication.
    if (!$this->ci->config->item('rest')['auth_callbacks'][$auth]($this, $this->ci->security->xss_clean($_SERVER[$auth]))) {
      $this->handle_response(RESTResponse::UN_AUTHORIZED, $auth); // Exits.
    }
  }
  /**
   * [get_authorization_header description]
   * @return [type] [description]
   */
  private function get_authorization_header():?string {
    if (isset($_SERVER['Authorization'])) {
      return trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
      return trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
      $requestHeaders = apache_request_headers();

      // Avoid Surprises.
      $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

      if (isset($requestHeaders['Authorization'])) {
        return trim($requestHeaders['Authorization']);
      }
    }
    return null;
  }
  /**
   * [handle_response description]
   * @param int $code [description]
   */
  private function handle_response(int $code, $auth=null):void {
    http_response_code($code);
    header("Content-Type: application/json");
    if (isset($this->ci->config->item('rest')['response_callbacks'][$code])) {
      $this->ci->config->item('rest')['response_callbacks'][$code]($auth);
    }
    if (ENVIRONMENT != 'testing') exit($code);
    throw new Exception("Error $code in $auth", $code);
  }
}
?>
