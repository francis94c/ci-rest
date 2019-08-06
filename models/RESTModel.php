<?php
declare(strict_types=1);
defined('BASEPATH') OR exit('No direct script access allowed');

class RESTModel extends CI_Model {

  /**
   * [private description]
   * @var [type]
   */
  private $users_table;
  /**
   * [private description]
   * @var [type]
   */
  private $users_id_column;
  /**
   * [private description]
   * @var [type]
   */
  private $users_email_column;
  /**
   * [private description]
   * @var [type]
   */
  private $users_username_column;
  /**
   * [private description]
   * @var [type]
   */
  private $users_password_column;
  /**
   * [private description]
   * @var [type]
   */
  private $api_key_table;
  /**
   * [private description]
   * @var [type]
   */
  private $api_key_column;
  /**
   * [private description]
   * @var [type]
   */
  private $api_key_limit_column;
  /**
   * [init description]
   * @param array $config [description]
   */
  public function init(array $config):void {
    $this->users_table = $config['users_table'];
    $this->users_id_column = $config['users_id_column'];
    $this->users_email_column = $config['users_email_column'];
    $this->users_username_column = $config['users_username_column'];
    $this->users_password_column = $config['users_password_column'];
    $this->api_key_table = $config['api_key_table'];
    $this->api_key_column = $config['api_key_column'];
    $this->api_key_limit_column = $config['api_key_limit_column'];
  }
  /**
   * [basic_auth description]
   * @param  object $context  [description]
   * @param  string $username [description]
   * @param  string $password [description]
   * @return bool             [description]
   */
  public function basicAuth(object &$context, string $username, string $password):bool {
    // Basic Checks.
    if ($this->users_table == null || $this->users_email_column == null || $this->users_password_column == null) {
      return false;
    }
    // Database Query.
    if ($this->users_id_column != null) {
      $this->db->select($this->users_id_column);
    }
    $this->db->select($this->users_password_column);
    $this->db->from($this->users_table);
    $this->db->where($this->users_email_column, $username);
    if ($this->users_username_column != null) {
      $this->db->or_where($this->users_username_column, $username);
    }
    $query = $this->db->get();
    if ($query->num_rows() == 0) return false;
    // Authenticate.
    if (password_verify($password, $query->result()[0]->{$this->users_password_column})) {
      if ($this->users_id_column != null) $context->userId = $query->result()[0]->{$this->users_id_column};
      return true;
    }
    return false;
  }
  /**
   * [getAPIKeyData description]
   * @param  string $apiKey [description]
   * @return array          [description]
   */
  public function getAPIKeyData(string $apiKey):?array {
    // Preliminary Check.
    if ($this->api_key_table == null || $this->api_key_column == null) return null;
    // Query.
    $this->db->select($this->api_key_column);
    if ($this->api_key_limit_column != null) $this->db->select($this->api_key_limit_column);
    $this->db->from($this->api_key_table);
    $this->db->where($this->api_key_column, $apiKey);
    $query = $this->db->get();
    // Process Result.
    if ($query->num_rows() > 0) return $query->result_array()[0];
    return null;
  }
  /**
   * [truncateRatelimitData description]
   * @return bool [description]
   */
  public function truncateRatelimitData():bool {
    return $this->db->simple_query('DELETE FROM rest_api_rate_limit WHERE start < (NOW() - INTERVAL 1 HOUR)');
  }
  /**
   * [getLimitData description]
   * @param  string $client [description]
   * @param  string $group  [description]
   * @return [type]         [description]
   */
  public function getLimitData(string $client, string $group):?array {
    $sql = 'SELECT count, start, (`start` + INTERVAL (1 - TIMESTAMPDIFF(HOUR, UTC_TIMESTAMP(), NOW())) HOUR) AS reset_epoch FROM rest_api_rate_limit WHERE client = ? AND _group = ?';
    $query = $this->db->query($sql, [$client, $group]);
    if (!is_scalar($query) && $query->num_rows() > 0) return $query->result_array()[0];
    return null;
  }
  /**
   * [insertLimitData description]
   * @param  string $client [description]
   * @param  string $group  [description]
   * @return bool           [description]
   */
  public function insertLimitData(string $client, string $group):bool {
    $sql = 'INSERT INTO rest_api_rate_limit (client, _group) VALUES (?, ?) ON DUPLICATE KEY UPDATE count = count + 1';
    return $this->db->query($sql, [$client, $group]);
  }
}
