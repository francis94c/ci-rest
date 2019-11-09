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
  function __construct($data, int $code)
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
   * [setJSON description]
   * @date   2019-11-09
   * @param  bool       $json [description]
   * @return RESTResponse         [description]
   */
  public function setJSON(bool $json):RESTResponse
  {
    $this->json = $json;
    return $this;
  }
  /**
   * [send description]
   * @date 2019-11-09
   */
  public function send():void
  {
    http_response_code($this->code);

    if ($data != null) echo !$this->json ? $this->data : json_encode($this->data);

    if (get_instance()->config->item('rest')['response_exit']) {
      exit(EXIT_SUCCESS);
    }
  }
}
?>
