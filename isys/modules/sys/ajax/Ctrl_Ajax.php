<?php
/**
 * Created by PhpStorm.
 * User: RaFaEl
 * Date: 03/05/2018
 * Time: 01:29 AM
 * @property CI_Migration $migration
 */

use \Propel\Runtime\ActiveQuery\Criteria as Criteria;

defined("BASEPATH") OR exit("No direct script access allowed");

class Ctrl_Ajax extends ES_Estic_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('sys/model_ajax');
    }

    public function set_global_vars(){
        $aResponse = array(
            'ROOTPATH' => ROOTPATH,
            'WEB_SERVER' => WEBSERVER,
            'WEB_ROOT' => WEBROOT,
            'PROTOCOL' => PROTOCOL,
            'BASEPATH' => BASEPATH,
            'APPPATH' => APPPATH,
            'ORMPATH' => ORMPATH,
            'FCPATH' => FCPATH,
            'SYSDIR' => SYSDIR,
        );
        echo json_encode($aResponse);

        exit();
    }
    public function index(){
        echo 'hello, from ajax';
    }

    public function savetopdf(){
        $this->load->library('api2Pdf','fd97a66b-f199-4bd9-84aa-a0332f7a26b1'); //or wherever you have stored the file

        $data = json_decode(file_get_contents('php://input'), true);
        $api_response = $this->api2pdf->headless_chrome_from_html($data["content"]);
        $this->fromAjax = true;
        $response = array();
        $response['error'] = $this->error;
        $response["data"] = $api_response->pdf;
        return $response;
    }

    public function export($module = '', $class = '', $method = '', $id= ''){
        $sys = config_item('sys');

        $methodExcepts = ['index'];

        if(!in_array($method, $methodExcepts)){

            // ************** inicia el submodulo ************
            if($this->{"init".ucfirst(setObject($class))}(true)){

                return $this->{"ctrl_$class"}->$method($id);
            }
        }
    }

    public function exportFields($table = ''){
        $aReturn = array();
        if(validateVar($table)){
            $fields = $this->dbforge->getArrayFieldsFromTable($table);
            $vFields = array();
            foreach ($fields as $field => $settings){
                if(compareArrayStr($settings,'table','ci_options')){
                    $vFields[] = $field;
                }
            }
            if(validateVar($vFields, 'array')){
                $fields = array_keys($fields);
                $fields = array_combine($fields,$fields);
                $vFields = array_combine($vFields,$vFields);
                $vFields['fields'] = $fields;
                $vFields['error'] = 'ok';
            } else {
                $vFields['error'] = 'La tabla seleccionada no contiene columnas que referencien a la tabla ci_options';
            }
            $aReturn = $vFields;
        } else {
            $aReturn['error'] = 'Debe introducir como parametro el nombre de una tabla';
        }
        echo json_encode($aReturn);
    }

    public function filterOptions(){
        $name = $this->input->post('name');
        $pk = $this->input->post('pk');
        $checked = $this->input->post('check');
        $tipo = str_replace('id_option_','',$name);
        if($checked == 'true'){
            $oTipo = HbfOpcionesQuery::create()
                ->findOneByIdOption($pk);
            $aTipo = array(
                'id_option' => $oTipo->getIdOption(),
                'tabla' => $oTipo->getTabla(),
                'tipo' => $oTipo->getTipo(),
                'nombre' => $oTipo->getNombre(),
                'descripcion' => $oTipo->getDescripcion()
            );
            echo json_encode($aTipo);
        } else {
            $oTipos = HbfOpcionesQuery::create()
                ->filterByTipo($tipo)
                ->find();
            foreach ($oTipos as $k => $oTipo){
                $aTipos[$k] = array(
                    'id_option' => $oTipo->getIdOption(),
                    'tabla' => $oTipo->getTabla(),
                    'tipo' => $oTipo->getTipo(),
                    'nombre' => $oTipo->getNombre(),
                    'descripcion' => $oTipo->getDescripcion()
                );
            }
            echo json_encode($aTipos);
        }
    }

    public function deleteRegistryFromDB(){
        $dir = $this->input->post('dir');
        $dir = preg_replace(['/^\//','/\/$/'],'',$dir);
        list($mod, $table, $method, $pk) = substr_count($dir,'/') == 3 ? explode('/',$dir) : [];
        $this->{"init_$table"}(true);
        $response = $this->{"model_$table"}->{$method}($pk);
        if(validateArray($response,'message') && validateArray($response,'code')){
            preg_match_all("/`(.*?)`/",$response['message'],$aMessage);
            if(validateArray($aMessage,1)){
                $response['constraints'] = $aMessage[1];
                if(compareArrayNum($response,'code',1451)){
                    list($foreignMod, $foreignTable) = getModSubMod($aMessage[1][1]);
                    list($localMod, $localTable) = getModSubMod($aMessage[1][4]);
                    list($foreignTableS, $foreignP) = setSingularPlural($foreignTable);
                    list($localTableS, $localTableP) = setSingularPlural($localTable);
                    $response['SQL'] = $response['message'];
                    $response['message'] = "No se puede eliminar el registro debido a que existe un $foreignTableS que hace referencia a esta $localTableS";
                }
            }
            $response['error'] = 'Hubo un error al eliminar el registro';
        } else {
            $response = array();
            $view = $this->{"ctrl_$table"}->index();
            if(validateVar($view,'array')){
                list($response['view'], $response['error']) = $view;
            } else {
                $response['error'] = 'ok';
                $response['view'] = $view;
            }
            $response['message'] = 'El registro fue eliminado de forma definitiva exitosamente';
        }
        echo json_encode($response);
    }

    public function remove($modulo,$class,$method,$pk){
        $this->load->library('migration');
        $post = $this->input->post();
        $sys = config_item('sys');

        $response = array();
        if($this->{"init".ucfirst(setObject($class))}(true)) {
            if($response = $this->{"model_$class"}->{$method}($pk)) {
                if(inArray('tableRef',$post) && inArray('idTableRef',$post) && inArray('fieldTableRef',$post)){
                    $tableRef = $post['tableRef'];
                    $pkTableRef = ucfirst($post['pkTableRef']);
                    $idTableRef = ucfirst($post['idTableRef']);
                    $fieldTableRef = $post['fieldTableRef'];
                    list($classRefMod,$classRefName) = getModSubMod($tableRef);
                    $this->{"init".ucfirst($classRefName)}(true);
                    $objRef = $this->{"model_$classRefName"}->{"findOneBy$pkTableRef"}($idTableRef);
                    $aIdsFiles = std2array($objRef->{"get$fieldTableRef"}());
                    if(in_array($pk,$aIdsFiles)){
                        $key = array_search($pk,$aIdsFiles);
                        unset($aIdsFiles[$key]);
                        $objRef->{"set$fieldTableRef"}($aIdsFiles);
                        $data = $objRef->save();
                    }
                }
            }
            if(validateArray($response,'message') && validateArray($response,'code')) {
                preg_match_all("/`(.*?)`/",$response['message'],$aMessage);
                if(validateArray($aMessage,1)){
                    $response['constraints'] = $aMessage[1];
                    if(compareArrayNum($response,'code',1451)){
                        list($foreignMod, $foreignTable) = getModSubMod($aMessage[1][1]);
                        list($localMod, $localTable) = getModSubMod($aMessage[1][4]);
                        list($foreignTableS, $foreignP) = setSingularPlural($foreignTable);
                        list($localTableS, $localTableP) = setSingularPlural($localTable);
                        $response['SQL'] = $response['message'];
                        $response['message'] = "No se puede eliminar el registro debido a que existe un $foreignTableS que hace referencia a esta $localTableS";
                    }
                }
                $response['error'] = 'Hubo un error al eliminar el registro';
            } else {
                $response = array();
                $view = $this->{"ctrl_$class"}->index();
                if(validateVar($view,'array')){
                    list($response['view'], $response['error']) = $view;
                } else {
                    $response['error'] = 'ok';
                    $response['view'] = $view;
                }
                $response['message'] = 'El registro fue eliminado de forma exitosa';
            }
        } else {
            $response['error'] = "error";
            $response['message'] = "Algo salio mal, Tu archivo no pudo ser eliminado";
        }
        echo json_encode($response);
        exit;
    }


}
