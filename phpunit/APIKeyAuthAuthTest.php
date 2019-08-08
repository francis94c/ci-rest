<?php
declare(strict_types=1);
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPUnit\Framework\TestCase;

class APIKeyAuthAuthTest extends TestCase {
  /**
   * Code Igniter Instance.
   * @var object
   */
  private static $ci;
  /**
   * Package name for simplicity
   * @var string
   */
  private const PACKAGE = "francis94c/ci-rest";

  /**
   * Prerquisites for the Unit Tests.
   *
   * @covers JWT::__construct
   */
  public static function setUpBeforeClass(): void {
    self::$ci =& get_instance();
    self::$ci->load->database('mysqli://root@localhost/test_db');
    self::$ci->load->helper("url");
    $queries = explode("#@@@", file_get_contents(FCPATH . 'application/splints/' . self::PACKAGE . '/phpunit/database.sql'));
    self::assertTrue(count($queries) > 0);
    self::$ci->load->database();
    foreach ($queries as $query) {
      self::$ci->db->query($query);
    }
  }
  /**
   * [testAPIKeyAuth description]
   */
  public function testAPIKeyAuth():void {
    $_SERVER['HTTP_X_API_KEY'] = "ABCDE";
    $this->assertTrue(true);
  }
  /**
   * [tearDownAfterClass description]
   */
  public static function tearDownAfterClass(): void {
    self::$ci->db->empty_table("api_keys");
    self::$ci->db->empty_table("users");
    self::$ci->db->empty_table("rest_api_rate_limit");
    self::$ci->load->dbforge();
    self::$ci->dbforge->drop_table("api_keys");
    self::$ci->dbforge->drop_table("users");
    self::$ci->dbforge->drop_table("rest_api_rate_limit");
    self::$ci->db->close();
  }
}
