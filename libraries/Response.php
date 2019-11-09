<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Response extends CI_Controller
{
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
   * @return Response         [description]
   */
  public function setJSON(bool $json):Response
  {
    $this->json = $json;
    return $this;
  }
  public function send():void
  {
    http_response_code($this->code);
    echo !$this->json ? $this->data : n_encode($this->data);
    if (get_instance()->config->item('rest')['response_exit']) {
      exit(EXIT_SUCCESS);
    }
  }
}
?>
