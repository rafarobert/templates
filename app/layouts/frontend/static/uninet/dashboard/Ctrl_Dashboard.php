<?php
/**
 * Created by PhpStorm.
 * User: RaFaEl
 * Date: 7/1/2017
 * Time: 1:56 AM
 */

class Ctrl_Dashboard extends ES_Frontend_Constroller {

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->data['subview'] = 'static/impuestos/dashboard/index';
    }

}
