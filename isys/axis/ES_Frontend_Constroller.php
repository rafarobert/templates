<?php
/**
 * Created by PhpStorm.
 * User: RaFaEl
 * Date: 6/8/2017
 * Time: 2:27 AM
 */

use \Propel\Runtime\ActiveQuery\Criteria as Criteria;

class ES_Frontend_Constroller extends ES_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->data['layout'] = strReplace('layouts/','',$this->router->directory).'_layout';
        $this->data['subLayout'] = 'frontend/_subLayout';
        $this->data['metaTitle'] = 'Impuestos Nacionales';
    }
}
