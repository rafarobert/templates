<?php
/**
 * Created by Estic.
 * User: #userCreated
 * Date: #dateCreated
 * Time: #timeCreated
 */

use \Propel\Runtime\ActiveQuery\Criteria as Criteria;

trait ES_Config_Trait
{
  //>>>setInitFunctions<<<
  public function configUcObjItem()
  {
    return config_item('lcItem');
  }
  //<<<setInitFunctions>>>
}
