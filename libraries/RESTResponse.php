<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RESTResponse extends CI_Controller
{
  // Response Codes
  // 40*
  const BAD_REQUEST           = 400;
  const UN_AUTHORIZED         = 401;
  const FORBIDDEN             = 403;
  const NOT_ACCEPTABLE        = 406;
  const TOO_MANY_REQUESTS     = 429;
  // 50*
  const INTERNAL_SERVER_ERROR = 500;
  const NOT_IMPLEMENTED       = 501;
  /**
   * [protected HTTP Response Code]
   * @var int
   */
  protected $code;
  /**
   * [protected Response Data]
   * @var mixed
   */
  protected $data;
  /**
   * [protected Shoud Response be JSON Encoded?]
   * @var bool
   */
  protected $json;
  function __construct($data=null, int $code=null)
  {
    $this->data = $data;
    $this->code = $code;
  }
  /**
   * [__toString description]
   * @date   2019-11-09
   * @return string     [description]
   */
  public function __toString():string
  {
    return !$this->json ? $this->data : n_encode($this->data);
  }
  /**
   * [json description]
   * @date   2019-11-11
   * @param  [type]       $data [description]
   * @param  int          $code [description]
   * @return RESTResponse       [description]
   */
  public function json($data, int $code):RESTResponse
  {
    $this->json = true;
    $this->code = $code;
    $this->data = $data;
    return $this;
  }
  /**
   * [send description]
   * @date  2019-11-11
   * @param boolean    $exit [description]
   */
  public function send(bool $exit=false):void
  {
    http_response_code($this->code ?? 200);

    if ($this->json) header('Content-Type: application/json');

    if ($this->data !== null) echo !$this->json ? $this->data : json_encode($this->data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

    if ($exit) exit(EXIT_SUCCESS);
  }
}
?>
