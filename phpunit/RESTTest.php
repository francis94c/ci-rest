<?php
declare(strict_types=1);
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPUnit\Framework\TestCase;

class RESTTest extends TestCase {
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
  private $obj_count = 0;

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
    // Verify that URI can be modified.
    self::$ci->uri->set_uri_string("a/uri");
    self::assertEquals("a/uri", uri_string());
    // Everything about this library, happens in it's connstructors.
    // Some static PHPUnit assertions will take place in the rest.php file
    // traditionally provided by the user and loaded from application/config by
    // default.
    // However, for the purpose of this test, we are going to Hack Code CodeIgniter
    // with a Splint Config variable to allow us load config files from where
    // ever we want. This happens below.
    self::$ci->load->add_package_path(APPPATH . 'splints/' . self::PACKAGE . "/phpunit/");
    //self::$ci->config->set_item('st_config_path_prefix', '../splints/' . self::PACKAGE . "/phpunit/config/");
  }
  /**
   * [testBasicAuthentication description]
   */
  public function testBasicAuth():void {
    // Simulate Request To 'basic/auth'
    self::$ci->config->set_uri_string("basic/auth");
    // Simulate Basic Authorization
    $_SERVER['PHP_AUTH_USER'] = "francis94c";
    $_SERVER['PHP_AUTH_PW'] = "0123456789";
    self::$ci->load->splint(self::PACKAGE, '+REST', null, 'rest_' . $this->obj_count++);
    $this->assertEquals(1, self::$ci->{'rest_'.($this->obj_count - 1)}->userId);
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
