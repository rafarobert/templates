<?php
/**
 * Created by PhpStorm.
 * User: rafaelgutierrezgaspar
 * Date: 2019-05-22
 * Time: 00:12
 */

use \Propel\Runtime\ActiveQuery\Criteria as Criteria;

defined("BASEPATH") OR exit("No direct script access allowed");

class Ctrl_Config extends ES_Controller
{

  function __construct()
  {
    parent::__construct();
  }


  public function build(){

    $_config =& get_config();
    $framePath = ORMPATH."config/";
    $fileName = "ES_Config.php";
    $this->data['setInitFunctions'] = '';

    $this->data = $this->setDefaultData($this->data);
    foreach ($_config as $item => $value){
      $this->data['lcItem'] = $item;
      $this->data['UcObjItem'] = ucfirst(setObject($item));
      $this->data['setInitFunctions'] .= $this->load->view(["template_ES_Config" => "setInitFunctions"], $this->data, true, true);
    }

    if (createFolder($framePath)) {
      $phpContent = $this->load->view("template_ES_Config", $this->data, true, true, true);
      if(file_exists($framePath . $fileName)){
        deleteFile($framePath . $fileName);
      }
      write_file($framePath . $fileName, $phpContent);
    }

  }

  public function setDefaultData($data){
    $database = ucfirst($this->db->database);
    $data['dbName'] = $database;
    $data["userCreated"] = config_item('soft_user');
    $data["dateCreated"] = date('d/m/Y');
    $data["timeCreated"] = date("g:i a");
    return $data;
  }
}
