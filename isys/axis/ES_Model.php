<?php
/**
 * Created by PhpStorm.
 * User: RaFaEl
 * Date: 6/11/2017
 * Time: 12:27 AM
 */

use \Propel\Runtime\ActiveQuery\Criteria as Criteria;

Class ES_Model extends ES_Model_Vars {

    /**
     * @var ES_Controller $CI
     */
    protected $CI;
    protected $thread = 0;

    /*
     * OrderBy Directions
     * */
    public static $desc = 'DESC';
    public static $asc = 'ASC';

    /**
     * @var ES_Model $MI
     */
    protected $MI;

    public $rowPerPages = 10;
    public $numPages = 0;

    /**
     * Value for virtual field files.
     *
     * @var        array
     */
    public $files = null;

    /**
     * Value for virtual thumb files files.
     *
     * @var        array
     */
    public $thumbs = null;

    protected $_table_name = '';
    protected $_primary_key = "id";
    protected $_primary_filter = "intval";
    protected $_order_by = '';
    public $rules = array();
    protected $_timestaps = true;
//    protected $_num_thumbs = 5;

    public $uriStrings = ['name','nombre','titulo','title'];
    public $rules_login = array(
        'email' => array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'trim|required|valid_email'
        ),
        'password' => array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required'
        ),
    );

    public $rules_register = array(
        'email' => array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'trim|required|valid_email'
        ),
        "password" => array(
            "field" => "password",
            "label" => "Password",
            "rules" => "trim|required|max_length[128]|matches[password_confirm]"),
        "password_confirm" => array(
            "field" => "password_confirm",
            "label" => "Confirm password",
            "rules" => "trim|matches[password]"
        ),
    );

    private $img_path;
    private $original_path;
    private $thumb_path;

    private static $instance = null;

    function __construct() {
        parent::__construct();
        $this->img_path = realpath(APPPATH.'../assets/img/');
        createFolder($this->img_path);
    }

    public static function create()
    {
        if(!self::$instance){
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public function init()
    {

    }
    public function array_from_post($fields){
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = $this->input->post($field);
        }
        return $data;
    }

    public function get($id = null, $single = false, $bPaginate = false){

        if($id != null){
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->where($this->_primary_key, $id);
            $method = 'row';
        } else if ($single) {
            $method = 'row';
        } else {
            $method = 'result';
        }
        if($this->db->field_exists('estado',$this->_table_name)){
            $this->db->where_in('estado', ['ENABLED','enabled']);
        } else if($this->db->field_exists('status',$this->_table_name)){
            $this->db->where_in('status', ['ENABLED','enabled']);
        }

        if($bPaginate && $page = $this->input->post('page')){
            $this->db->offset($page*$this->rowPerPages);
            $this->db->limit($this->rowPerPages);
        }
        $this->db->order_by($this->_order_by);
        $this->_order_by = '';
        $oResult = $this->db->get($this->_table_name)->$method();

        if($bPaginate){
            if(!$this->input->post('page')){
                $_POST['page'] = 1;
                if(isset($oResult)){
                    $numPages = count($oResult) > 10 ? floatval(count($oResult)/$this->rowPerPages) : 1;

                }
            }
        }
        return $oResult;
    }

    public function paginate(){

        $oResult = $this->get();
        if(isset($oResult)){
            $page = $this->input->post('page');
            $limit = 10;
            $offset = $page*$limit;
            $numPages = count($oResult) ? count($oResult) / $limit : 0;
                $numResult = count($oResult);
                $numPages = $numResult / $limit;
        }

    }

    public function setResultsFromData($oResults){
        $aReturn = array();
        foreach ($oResults as $key => $result){
            $aReturn[$key] = $this->setFromData($result);
        }
        return $aReturn;
    }

    public function get_by_selecting($where, $select, $single = false){
        $this->db->select($this->_primary_key.', '.$select);
        $this->db->where($where);
        return $this->get(null, $single);
    }
    public function get_by($where, $bSelecting = false, $single = false, $orderBy = '', $direction = 'desc'){
        $select = isString($where) ? '' : $this->_primary_key;
        $aWheres = [];
        $i = 0;
        if(!isArray($where)){
            $where = [$where];
        }
        $bSelectAdded = false;
        foreach ($where as $k => $wh){
            if(isString($wh)){
                if(strstr($wh,'|')){
                    $wh = trim($wh,'|');
                    $aWheres[$k] = explode('|',$wh);
                    $wh = null;
                }
            }
            if(isNumeric($k,false)){
                if($i == 0 && isString($wh)){
                    $aWheres = !isNumeric($k, false) ? [$k => $wh] : [];
                }
                if(isArray($wh)){
                    if(inArray(1,$wh, false)){
                        if(isBoolean($wh[1]) && $wh[1] && !strhas($select,$wh[0])){
                            $select .= ", $wh[0]";
                            $bSelectAdded = true;
                        } else {
                            $select .= "";
                            $bSelectAdded = false;
                        }
                    }
                    if(!$bSelectAdded){
                        foreach ($wh as $j => $wh_interno){
                            if(isNumeric($j,false)){
                                $select .= isString($select) ? ', '.$wh_interno : $wh_interno;
                            }
                        }
                    }
                } else if(isString($wh)){
                    $select .= !strhas($select,$wh) ? ", $wh" : "";
                } else if(isNumeric($wh)){
                    $select .= !strhas($select,$wh) ? ", $wh" : "";
                }
            } else if(isString($k)){
                if(isArray($wh)){
                    // Esta parte funciona como valor booleano para determinar si se agrega al select o no
                    if (inArray(1,$wh, false)){
                        $select .= isBoolean($wh[1]) && $wh[1] && !strhas($select,$k) ? ", $k" : "";
                    }
                    // ------------------------------------------------------------------------------
                    $aWheres[$k] = $wh[0];
                } else if(isString($wh)){
                    $aWheres[$k] = $wh;
                    $select .= !strhas($select,$k) ? ", $k" : "";
                } else if(isNumeric($wh)){
                    $aWheres[$k] = $wh;
                    $select .= !strhas($select,$k) ? ", $k" : "";
                }
            } else {
                $select = "";
                $aWheres[$k] = $wh;
            }
            if(!$bSelecting){
                $select = '' ;
            }
            $i++;
        }

        $this->db->select($select);
        foreach($aWheres as $k => $where){
            if(isArray($where)){
                $this->db->where_in($k,$where);
            } else {
                $this->db->where($k,$where);
            }
        }
        if(isString($orderBy)){
            $this->_order_by = "$orderBy $direction";
        }
        return $this->get(null, $single);
    }

    public function get_one_by($where, $bSelecting = false, $single = false){
        $result = $this->get_by($where, $bSelecting, $single);

        if(countStd($result) == 1){
            return isset($result->{0}) ? $result->{0} : isset($result[0]) ? $result[0] : null;
        } else {
            return $result;
        }
    }

    public function setOptions($fields, $delimiter = ' '){
        $options = [];
        if(!isset($delimiter)){
            $delimiter = ', ';
        } else {
            $delimiter = $delimiter." ";
        }
        $primary ='';
        foreach ($fields as $field){
            $primary_key = $this->_primary_key;
            if(isset($field->$primary_key)){
                $key = $field->$primary_key;
                unset($field->$primary_key);
                $options[$key] = '';
                foreach ($field as $setting){
                    $options[$key] .= $setting.$delimiter;
                }
                $field->$primary_key = $key;
                $options[$key] = substr($options[$key],0,strlen($options[$key])-2);
            }
        }

        return $options;
    }

    public function create_es_sessions(){
        $this->dbforge->create_es_sessions();
    }
//
//    public function get_pk_table($table){
//        $sql = "show columns from `$table` where `Key` = 'PRI'";
//        $pkTable = $this->db->query($sql)->row();
//
//    }

    public function verifyDataWithPost($data){

        $posts = $this->input->post();

        foreach ($posts as $keyPost => $valuePost) {

            if(!inArray($keyPost,$data)){
                $data[$keyPost] = $valuePost;
            }
        }
        return $data;
    }

    public function save($data = null, $id = null, $with_id = true){

        $data = $this->verifyDataWithPost($data );

        $this->CI = CI_Controller::get_instance();
        $idToInsert = null;
        $fromEditPerfil = false;
        if($data == null){
            $data = $this->getArrayData();
            $pk = ucfirst(setObject($this->getPrimaryKey()));
            $id = $this->{"get$pk"}();
        } else if(isString($data)){
            return $data;
        }
        $this->load->library('session');
        // set timesatamps
        $now = date('Y-m-d H:i:s');

        if($this->db->field_exists('estado', $this->_table_name) && $id == null){
            $data['estado'] = 'ENABLED';
        } else if($this->db->field_exists('status', $this->_table_name) && $id == null){
            $data['status'] = 'ENABLED';
        }

        if($this->db->field_exists('password', $this->_table_name) && $id == null){
            if(strlen($this->session->hash($data['password'])) != strlen($data['password'])){
                $data['password'] = validateVar($data['password']) || validateVar($data['password'],'numeric') ? $this->session->hash($data['password']) : $data['password'];
            }
        }
        if($this->input->post('fromEditPerfil')){
            $fromEditPerfil = true;
        }
        if($this->db->field_exists('email', $this->_table_name) && $this->_table_name == 'es_users' && !$fromEditPerfil){
            if(!$this->session->_unique_email()){
                return $this->CI->model_users->findOneByEmail($this->CI->input->post('email'))->getArrayData();
//                exit();
            };
        }

        if($this->_timestaps == true){
            if($id == null){
                if($this->db->field_exists('date_created',$this->_table_name) && $this->db->field_exists('date_modified',$this->_table_name) && $this->db->field_exists('change_count',$this->_table_name)){
                    $data['date_created'] = $now;
                    $data['date_modified'] = $now;
                    $data['change_count'] = 0;
                }
            } else {
                if($this->db->field_exists('date_modified', $this->_table_name) && $this->db->field_exists('change_count', $this->_table_name)){
                    $data['date_modified'] = $now;
                    $data['change_count'] = inArray('change_count', $data, false) ? (int)$data['change_count']+1 : 0;
                }
            }
        }

        /**
         * @var Model_Users $oUserLogguedIn
         */
        if($this->db->field_exists('id_user_modified', $this->_table_name)){
            if($this->db->table_exists('es_users') && $this->db->field_exists('id_user','es_users')){

                if($this->_table_name == 'es_logs'){

                    if(is_object($this->CI->oUserLogguedIn)){
                        $data['id_user_modified'] = $this->CI->oUserLogguedIn->id_user;
                        $data['id_user_created'] = $this->CI->oUserLogguedIn->id_user;
                    } else {
                        $data['id_user_modified'] = 1;
                        $data['id_user_created'] = 1;
                    }
                    $oUserLoggued = null;

                } else {

                    $oUserLoggued = $this->session->getObjectUserLoggued();

                    if (is_object($oUserLoggued) && $id == null){
                        $data['id_user_created'] = $oUserLoggued->id_user;
                        $data['id_user_modified'] = $oUserLoggued->id_user;
                    } else if(is_object($oUserLoggued) && $id != null){
                        $data['id_user_modified'] = $oUserLoggued->id_user;
                    } else if($this->CI->input->post('fromAjax') || $this->CI->fromFiles){
                        $data['id_user_modified'] = isset($data['id_user']) ? $data['id_user'] : 1;
                        $data['id_user_created'] = isset($data['id_user']) ? $data['id_user'] : 1;
                    } else if(validateArray($data,'from_session') || $this->input->post('fromSession')){
                        $oUserSaved = $this->CI->model_users->findOneByEmail($data['email']);
                        $data['id_user_modified'] = $oUserSaved->getIdUser();
                        $data['id_user_created'] = $oUserSaved->getIdUser();
                    } else if(!validate_modulo('estic','users') || !validate_modulo('estic','sessions')) {
                      $data['id_user_modified'] = config_item('soft_user_id');
                      $data['id_user_created'] = config_item('soft_user_id');
                    } else {
                        show_error('Se intenta agregar o modificar un registro en la base de datos, en la tabla: '.$this->_table_name.', para ello es necesario haber iniciado sesion, para registrar al usuario que realiza cambios, Por favor inicia sesion y vuelve a intentarlo');
                        exit();
                    }

                }

            }
        }

        $funct_v = function ($key, $vals) {

            $this->CI = class_exists('CI_Controller') ? CI_Controller::get_instance() : null;
            if($this->CI->db->field_exists($key,$this->_table_name)){
                if (isObject($vals)){
                    $vals = std2array($vals);
                }
                if(is_array($vals)){
                    $str = array2str($vals);
                    return $str;
                }
                if(isString($vals)){
                    $isDate = false;
                    $nums = explode('/',$vals);
                    if(count($nums) == 3){
                        foreach ($nums as $num){
                            if(!isNumeric($num)){
                                $isDate = false;
                            }
                            else {
                                $isDate = true;
                            }
                        }
                        if($isDate){
                            $date = new DateTime(implode('-',$nums));
                            $vals = $date->format('Y-m-d H:i:s');
                        }
                    }
                    if(strstr($vals,'&lt;') ||strstr($vals,'&gt;')){
                        $vals = str_replace('&gt;','>',$vals);
                        $vals = str_replace('&lt;','<',$vals);
                    }
                }
                return $vals;
            } return null;

        };
        $funct_k = function ($key) {
            $this->CI = class_exists('CI_Controller') ? CI_Controller::get_instance() : null;
            if($this->CI->db->field_exists($key,$this->_table_name)){
                if(strpos($key,'[]')){
                    $key = substr($key,0,strlen($key)-2);
                }
                return $key;
            } return null;
        };
        $keys = array_map($funct_k, array_keys($data));
        $vals = array_map($funct_v, array_keys($data), array_values($data));
        $data = array_combine($keys,$vals);
        unset($data[null]);
        if(inArray($this->_primary_key,$data) ){
            unset($data[$this->_primary_key]);
        }

        // insert
        if (($id == null || $id == 0) && !isset($data[$this->_primary_key])) {
            $data[$this->_primary_key] = null;
            if (is_numeric($with_id) || is_string($with_id)) {
                $id = $with_id;
                $data[$this->_primary_key] = $with_id;
            }
            $this->db->set($data);
            $this->db->insert($this->_table_name);
            $id = $this->db->insert_id();
            // update
        } else {
            $filter = $this->_primary_filter;
            unset($data[$this->_primary_key]);
            $id = $filter($id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $id);
            $this->db->update($this->_table_name);
        }
        $data[$this->_primary_key] = $id;
        return $data;
    }
//    public function getThumbs($obj,$file = '',$field = ''){
//
//    }

    public function getDataFromPost($object = null)
    {
        $data = $this->input->post();
        if(validateVar($data,'array')){
            $oModelUcObjTableS = $this->setFromData($data,$object);
            return $oModelUcObjTableS;
        } else {
            return $this;
        }
    }

    public function getThumbs($objs, $file = '', $field = ''){
        if(isset($objs->{$this->_primary_key})){
            $obj = $objs;
            unset($objs);
            $objs[0] = $obj;
        }
        foreach ($objs as $k => $obj) {
            list($modSign, $submod) = getModSubMod($this->_table_name);
            $filesFields = array();
            if ($file == '' || $field == '') {
                $aFields = $this->{'table_' . $this->_table_name};
                $funct = function ($val) {
                    if (validateArray($val, 'input')) {
                        if (compareStrStr($val['input'], 'image') || compareStrStr($val['input'], 'img')) {
                            return [$val['field'] => $val['input']];
                        }
                    }
                };
                if(validateVar($aFields, 'array')){
                    $aImgInputs = array_map($funct, $aFields);
                    foreach ($aImgInputs as $field => $imgInput) {
                        if ($imgInput != null) {
                            $filesFields[$field] = $obj->{$field};
                        } else {
                            unset($aImgInputs[$field]);
                        }
                    }
                }
            } else {
                $filesFields = [$field => $file];
            }
            foreach ($filesFields as $field => $file) {
                $aThumbs = array();
                if ($file != '') {
                    $aFilePart = explode('.', $file);
                    if (count($aFilePart) > 1) {
                        $ext = $aFilePart[sizeof($aFilePart) - 1];
                        $name = $aFilePart[sizeof($aFilePart) - 2];
                        $size = 50;
                        if (file_exists(FCPATH . "assets/img/$submod/$file")) {
                            for ($i = 1; $i <= $this->_num_thumbs; $i++) {
                                $thumbFile = $name . "-thumb_$size.$ext";
                                if (file_exists(FCPATH . "assets/img/$submod/thumbs/$thumbFile")) {
                                    $aThumbs[$i] = $thumbFile;
                                    $size += 100;
                                }
                            }
                        }
                    } else {
                        for ($i = 1; $i <= $this->_num_thumbs; $i++) {
                            $aThumbs[$i] = '';
                        }
                    }
                } else {
                    for ($i = 1; $i <= $this->_num_thumbs; $i++) {
                        $aThumbs[$i] = '';
                    }
                }
                foreach ($aThumbs as $i => $thumb) {
                    $obj->{$field . "_thumb$i"} = $thumb;
                }
            }
            if(is_object($objs)){
                $objs->$k = $obj;
            }
            $file = $field = '';
        }
        return $objs;
    }
    public function initLoaded(){
        $CI=CI_Controller::get_instance();
        if($CI != null){
            foreach ($CI as $instance => $value) {
                if($instance == 'data'){
                    foreach ($CI->$instance as $dataKey => $dataVal){
                        $this->$instance[$dataKey] = $dataVal;
                    }
                } else {
                    $this->$instance = $value;
                }
            }
        }
        if(isset($this->load)){
            $models = $this->load->_ci_models;
            foreach ($models as $alias => $name) {
                $this->$name = new $name();
            }
        }
        return $CI;
    }

    public function do_upload($field, $id){

        //$this->initLoaded();
        list($modSign,$submod) = getModSubMod($this->_table_name);
        $dirPictures = ROOTPATH."assets/$submod/";
        $dirPicturesDB = "assets/$submod/";
        createFolder($dirPictures);
        // Settings for images
        $aFileTypes = explode('|',config_item('file_types'));
        $config = array(
            'allowed_types'     => config_item('file_types'),
            'max_size'          => config_item('img_max_size'),
            'max_width'         => config_item('img_max_width'),
            'max_height'        => config_item('img_max_height'),
            'maintain_ratio'    => true,
            'image_library'     => 'gd2',
            'create_thumb'      => TRUE,
            'num_thumbs'        => 3,
            'width'             => 50,
            'height'            => 50,
            'thumb_marker'      => '-thumb_50',
        );
        $this->load->library('upload', $config);
        $this->load->library('image_lib');

        if(isArray($_FILES)){
            foreach ($_FILES as $fName => $fSettings){
                if(isset($fSettings['name'])){
                    $fileName = $fSettings['name'];
                    $aFileNameParts = explode('.',$fileName);
                    $aFileNamePartsV = array();
                    foreach ($aFileNameParts as $aFileNamePart) {
                        $aFileNamePartsV[] = clean($aFileNamePart);
                    }
                    $fSettings['name'] = implode('.',$aFileNamePartsV);
                    $_FILES[$fName]['name'] = implode('.',$aFileNamePartsV);
                } else {
                    return false;
                }
                $dirPictures .= "$fName/";
                $dirPicturesDB .= "$fName/";
                createFolder($dirPictures);
                $dirPicturesThumb = $dirPictures."thumbs";
                $dirPicturesThumbDB = $dirPicturesDB."thumbs";
                createFolder($dirPicturesThumb);
                $this->upload->upload_path = $dirPictures;
                $this->upload->upload_path_db = $dirPicturesDB;
                $config['new_image'] = $dirPicturesThumb;
                $config['new_image_db'] = $dirPicturesThumbDB;
                $files = $_FILES;
                $aThumbs = array();
                $aFiles = explode('|',config_item('file_without_tumbs'));
                $aThumbs = [];
                if (isArray($files[$fName])) {
                    if (!$this->upload->do_upload($fName) && $id == null) {
                        return false;
                    } else {
                        $this->upload->bFileUploaded = true;
                        $this->upload->num_thumbs = $config['num_thumbs'];
                        $this->upload->file_url = "/assets/$submod/$fName/".$this->upload->orig_name;
                        $file = $this->upload->data();
                        $config['source_image'] = ROOTPATH.$file['full_path'];
                        for ($i = 0; $i < $config['num_thumbs']; $i++) {
                            if($this->image_lib->initialize($config)){
                                if(in_array($this->image_lib->dest_ext,$aFiles)){
                                    $this->upload->num_thumbs = 0;
                                    break;
                                } else {
                                    $aThumbs[$i] = $file;
                                    unset($aThumbs[$i]['nro_thumbs']);
                                    $this->image_lib->resize();
                                    $aThumbs[$i]['width'] = $this->image_lib->width;
                                    $aThumbs[$i]['height'] = $this->image_lib->height;
                                    $aThumbs[$i]['name'] = $this->image_lib->dest_name;
                                    $aThumbs[$i]['library'] = $this->image_lib->image_library;
                                    $aThumbs[$i]['thumb_marker'] = $this->image_lib->thumb_marker;
                                    $aThumbs[$i]['url'] = "/assets/$submod/$fName/thumbs/".$this->image_lib->dest_name;
                                    $aThumbs[$i]['raw_name'] = $this->image_lib->dest_name;
                                    $aThumbs[$i]['ext'] = $this->image_lib->dest_ext;
                                    $aThumbs[$i]['path'] = $this->image_lib->dest_folder_db;
                                    $aThumbs[$i]['full_path'] = $this->image_lib->full_dst_path_db;
                                    $config['width'] = $config['width'] + 400;
                                    $config['height'] = $config['height'] + 400;
                                    $config['thumb_marker'] = '-thumb_' . $config['width'];
                                }
                            }
                        }
                        $this->upload->data_thumbs = $aThumbs;
                    }
                    $this->upload->file_name = $files[$fName]['name'];
                    $this->upload->file_type = $files[$fName]['type'];
                }
            }
            return true;
        } else {
            $this->upload->error_msg[] = 'Algo salio mal, no se pudo subir el archivo';
            return false;
        }
    }

    public function setForeignValues($t1Contents, $t1FieldRef, $t2Contents, $t2FieldRef, $bWithAllFields = false){
        $primary_key = $this->_primary_key;
        foreach ($t1Contents as $i => $t1Content){
            foreach ($t2Contents as $j => $t2Content){
                if((isset($t2Content->$t2FieldRef) && isset($t1Content->$t1FieldRef ) && $t2Content->$t2FieldRef == $t1Content->$t1FieldRef)|| $bWithAllFields){
                    $t1FieldsRef = std2array($t1Content);
                    $t2FieldsRef = std2array($t2Content);
                    unset($t1FieldsRef[$primary_key]);
                    unset($t1FieldsRef[$t1FieldRef]);
                    unset($t2FieldsRef[$t2FieldRef]);
                    $t1Contents->$i = new stdClass();
                    $t1Contents->$i->$primary_key = $t1Content->$primary_key;

                    foreach ($t2FieldsRef as $t2field => $t2value){
                        $t1Contents->$i->$t2field = $t2Content->$t2field;
                    }
                    foreach ($t1FieldsRef as $t1field => $t1value){
                        if($bWithAllFields){
                            $t1Contents->$i->{$t1FieldRef.'_'.$t1field} = $t1Content->$t1field;
                        } else {
                            $t1Contents->$i->$t1field = $t1Content->$t1field;
                        }
                    }
                }
            }
        } return $t1Contents;
    }

    public function setForeignFields($t1Contents, $t1FieldRef, $t2Contents, $t2FieldRef, $bWithAllFields = false){
        $primary_key = $this->_primary_key;
        if(validateVar($t2Contents,'object')){
            $t2Contents = [$t2Contents];
        }
        foreach ($t2Contents as $j => $t2Content){
            if(isArray($t1Contents ) || isObject($t1Contents )){
                foreach ($t1Contents as $i => $t1Content){
                    if($t2Content->$t2FieldRef == $t1Content->$t1FieldRef && is_object($t1Content)){
                        $ref = $t1Content->$t1FieldRef;
                        unset($t1Content->$t1FieldRef);
                        foreach ($t1Content as $t1Name => $t1Value){
                            if(isset($t2Contents->$j)){
                                $t2Contents->$j->{$t2FieldRef.'_'.$t1Name} = $t1Value;
                            } else if(isset($t2Contents[$j])){
                                $t2Contents[$j]->{$t2FieldRef.'_'.$t1Name} = $t1Value;
                            } else if(isset($t2Contents->{$t2FieldRef.'_'.$t1Name} )){
                                $t2Contents->{$t2FieldRef.'_'.$t1Name} = $t1Value;
                            }
                        }
                        $t1Content->$t1FieldRef = $ref;
                    }
                }
            }
        } return $t2Contents;
    }

    public function findOneBy($arrayData){

        $oField = $this->get_one_by($arrayData, true);

        return $this->setFromData($oField);
    }

    public function setThumbs($oModel = null, $idsFiles = null)
    {
        $this->CI = CI_Controller::get_instance();
        $aIdsFiles = array();
        if($oModel == null){
            if($this->_table_name == 'es_files' && $this->nro_thumbs != null){
                $oModel = $this;
            }
        }
        if($idsFiles == null) {
            if($this->_table_name == 'es_files' && $this->nro_thumbs != null){
                /**
                 * @var Model_Files $oModel
                 */
                $aIdsFiles[] = $oModel->getIdFile();
            }
        }
        if(validate_modulo('estic','files')){

          $this->load->model("estic/model_files");
          if(isArray($idsFiles) || isObject($idsFiles)){
              $aIdsFiles = $idsFiles;
          } else if(isNumeric($idsFiles)){
              $aIdsFiles[] = $idsFiles;
          }

          foreach ($aIdsFiles as $key => $idFile) {
              if (isNumeric($idFile) || isString($idFile)) {
                  $oFile = $this->model_files->findOneByIdFile($idFile);
                  $this->files[$key] = $oFile;
                  if (isObject($oFile)) {
                      $oThumbFiles = $this->model_files->filterByIdParent($oFile->getIdFile(),null,false);
                      if (isObject($oThumbFiles) || isArray($oThumbFiles)) {
                          /**
                           * @var ES_Model_Files $thumb
                           */
                          $this->files[$key]->{'thumbs'} = array();
                          foreach ($oThumbFiles as $keyThumb => $thumb) {
                              if(is_array($thumb)){
                                  $this->thumbs[$keyThumb] = $thumb;
                                  $this->files[$key]->{'thumbs'}[$keyThumb] = $thumb;
                              } else if (isset($thumb->_table_name)){
                                  $this->thumbs[$keyThumb] = $thumb->toArray();
                                  $this->files[$key]->{'thumbs'}[$keyThumb] = $thumb->toArray();
                              }
                          }
                      }
                  }
              }
          }
        }
        return $this;
    }

    public function getThumb1($model = null)
    {
        $this->load->library('upload');
        $nroThumb = 1;
        $indexThumb = 1;
        $aData = array();
        if(!is_object($model)){
            $model = $this;
        }
        if(isset($model->thumbFiles)){
            if(isArray($model->thumbFiles)){
                foreach ($model->thumbFiles as $key => $thumbData){
                    if($indexThumb == $nroThumb){
                        return $this->setFromData($thumbData);
                    }
                    $indexThumb++;
                }
                $aData['thumbs'] = $model->thumbs;
            }
        }
        return $aData;
    }

    public function getThumb2($model = null)
    {
        $this->load->library('upload');
        $nroThumb = 2;
        $indexThumb = 1;
        if(!is_object($model)){
            $model = $this;
        }
        if(isset($model->thumbFiles)){
            if(isArray($model->thumbFiles)){
                foreach ($model->thumbFiles as $key => $thumbData){
                    if($indexThumb == $nroThumb){
                        return $this->setFromData($thumbData);
                    }
                    $indexThumb++;
                }
                $aData['thumbs'] = $model->thumbs;
            }
        }
        return $aData;
    }

    public function getThumb3($model = null)
    {
        $this->load->library('upload');
        $nroThumb = 3;
        $indexThumb = 1;
        if(!is_object($model)){
            $model = $this;
        }
        $aData = array();
        if(isset($model->thumbFiles)){
            if(isArray($model->thumbFiles)){
                foreach ($model->thumbFiles as $key => $thumbData){
                    if($indexThumb == $nroThumb){
                        return $this->setFromData($thumbData);
                    }
                    $indexThumb++;
                }
                $aData['thumbs'] = $model->thumbs;
            }
        }
        return $aData;
    }

    public function getArrayDataWithThumbs($model = null){
        if($this == null){
            return;
        }
        if(!is_object($model)){
            $model = $this;
        }
        $model->setThumbs();
        $aData = $model->getArrayData();
        if(isset($model->thumbs)){
            if(isArray($model->thumbs)){
                $aData['thumbs'] = $model->thumbs;
            }
        } else if(isset($model->thumbFiles)){
            if(isArray($model->thumbFiles)){
                $aData['thumbs'] = $model->thumbFiles;
            }
        }
        return $aData;
    }

    public function getFiles(){
        if (isset($this->files)){
            foreach ($this->files as $key => $file) {
                if($file == null){
                    unset($this->files[$key]);
                }
            }
            return $this->files;
        }
        return [];
    }

    public function selectBy($selects = null){
        $aSelects = array();
        if(isArray($selects)){
            $aSelects = $selects;
        } else if(isString($selects)){
            $aSelects[] = $selects;
        }
        $aData = $this->get_by(array (
            0 => $aSelects,
        ),true);
        return $aData;
    }

    public function findByIdsFiles($ids){
        $aIds = array();
        if(is_object($ids)){
            $aIds = std2array($ids);
        }
        if(isString($ids) && strstr($ids,'|') && $ids != '|'){
            $aIds = explode('|',trim($ids,'|'));
        }
        if(isNumeric($ids)){
            $aIds[] = $ids;
        }
        if(isArray($ids)){
            $aIds = $ids;
        }
        $aFiles = array();
        if(isArray($aIds)){
            foreach ($aIds as $k => $id){
                $oFile = $this->findOneByIdFile($id);
                if (is_object($oFile)){
                  $oFile->setThumbs();
                  $aFiles[$k] = $oFile->toArray();
                }
            }
        }
        return $aFiles;
    }

    public function findByIdsEtiquetas($aIds){
        if(isString($aIds) && strstr($aIds,'|')){
            $aIds = explode('|',trim($aIds,'|'));
        }
        $aEtiquetas = array();
        if(isArray($aIds)){
            foreach ($aIds as $id){
                $aEtiquetas[] = $this->findOneByIdEtiqueta($id)->getArrayData();
            }
        }
        return $aEtiquetas;
    }

    public function findByIdEtiquetas($aIds){
        if(isString($aIds) && strstr($aIds,'|')){
            $aIds = explode('|',trim($aIds,'|'));
        }
        if(is_object($aIds)){
            $aIds = std2array($aIds);
        }
        $aEtiquetas = array();
        if(is_array($aIds)){
            foreach ($aIds as $id){
                $aEtiquetas[] = $this->findOneByIdEtiqueta($id)->setThumbs()->toArray();
            }
        }
        return $aEtiquetas;
    }

    public function delete($id){
        $filter = $this->_primary_filter;
        $id = $filter($id);

        if(!$id){
            return false;
        }
        if($response = $this->dbforge->setDeleted($this->_table_name,$this->_primary_key,$id)){
            // ------------------ se aplica a archivos con thumbnails ---------------------
            if($this->_table_name == 'es_files' && $this->nro_thumbs != null){
                $oData = $this->filterByIdParent($id);
                foreach ($oData as $file){
                    $this->dbforge->setDeleted($this->_table_name,$this->_primary_key,$file->id_file);
                }
            }
            // ----------------------------------------------------------------------------
        }

        if(validateVar($response, 'array')){
            $response['localTable'] = $this->_table_name;
        }
        return $response;
    }

    /**
     * @var ES_Model $object
     */
    public function saveOrUpdate($id = null, $withId = false){
        $data = $this->getArrayData();
        $data = $this->save($data,$id,$withId);
        $this->setFromData($data,$this);
        $this->aData = $data;
        return $data;
    }

    public function setForeigns($data, $orderBy = '', $direction = 'ASC'){
      $sys = config_item('sys');
        $bFirstThread = is_bool($orderBy) ? $orderBy : false;
        $this->setFromData($data,$this);
        $foreignKeys = $this->dbforge->getTableRelations($this->_table_name);
        $localKeys = array_column($foreignKeys,'idLocal');
        if($data != null){

            foreach ($foreignKeys as $fKey => $ffSettings){
                if(isset($ffSettings['table'])){

                    list($modSign,$submod) = getModSubMod($ffSettings['table']);
                    $modName = $sys[$modSign];
                    if (validate_modulo($modName,$submod)){
                      list($submodS,$submodP) = setSingularPlural($submod);
                      $this->{"init".ucfirst(setObject($submodP))}();
                      $idLocal = $ffSettings['idLocal'];
                      $idForeign = $ffSettings['idForeign'];
                      $field = ucfirst(setObject(strstr($idLocal, 'id_') ? str_replace('id_', '', $idLocal) : $idLocal));

                      if($ffSettings['table'] == $this->getTableName()){

                          $aForeignsFields = array();

                          if(is_array($data) && isset($data[$idLocal])){

                              $foreignDatas = $this->{'filterBy'.ucfirst(setObject($ffSettings['idLocal']))}($data[$idForeign],null,$orderBy,$direction);

                              foreach ($foreignDatas as $foreignData){

                                  $aForeignsFields[] = $foreignData->toArray();
                              }
                              $data['foreigns'][lcfirst($field)] = $aForeignsFields;

                          } else if(is_array($data) && isset($data[setObject($idLocal)])){

                              $foreignDatas = $this->{'filterBy'.ucfirst(setObject($ffSettings['idLocal']))}($data[setObject($idForeign)],null,$orderBy,$direction);

                              foreach ($foreignDatas as $foreignData){

                                  $aForeignsFields[] = $foreignData->toArray();
                              }
                              $data['foreigns'][lcfirst($field)] = $aForeignsFields;

                          } else if(is_array($data) && isset($data[ucfirst(setObject($idLocal))])){

                              $foreignDatas = $this->{'filterBy'.ucfirst(setObject($ffSettings['idLocal']))}($data[ucfirst(setObject($idForeign))],null,$orderBy,$direction);

                              foreach ($foreignDatas as $foreignData){

                                  $aForeignsFields[] = $foreignData->toArray();
                              }
                              $data['foreigns'][lcfirst($field)] = $aForeignsFields;

                          } else if(is_object($data) && objectHas($data,$idLocal,false) && $data->{$idLocal} == null){

                              $foreignDatas = $this->{'filterBy'.ucfirst(setObject($ffSettings['idLocal']))}($data->{$idForeign},null,$orderBy,$direction);

                              foreach ($foreignDatas as $foreignData){

                                  $aForeignsFields[] = $foreignData->toArray();
                              }
                              $data->{'foreigns'}[lcfirst($field)] = $aForeignsFields;

                          } else if(is_object($data) && objectHas($data,setObject($idLocal),false) && $data->{setObject($idLocal)} == null){

                              $foreignDatas = $this->{'filterBy'.ucfirst(setObject($ffSettings['idLocal']))}($data->{setObject($idForeign)},null,$orderBy,$direction);

                              foreach ($foreignDatas as $foreignData){

                                  $aForeignsFields[] = $foreignData->toArray();
                              }
                              $data->{'foreigns'}[lcfirst($field)] = $aForeignsFields;

                          } else if(is_object($data) && objectHas($data,ucfirst(setObject($idLocal)),false) && $data->{ucfirst(setObject($idLocal))} == null){

                              $foreignDatas = $this->{'filterBy'.ucfirst(setObject($ffSettings['idLocal']))}($data->{ucfirst(setObject($idForeign))},null,$orderBy,$direction);

                              foreach ($foreignDatas as $foreignData){

                                  $aForeignsFields[] = $foreignData->toArray();
                              }
                              $data->{'foreigns'}[lcfirst($field)] = $aForeignsFields;
                          }
                      } else {

                          if (is_array($data) && isset($data[$idLocal]) && $data[$idLocal] != null) {

                              $foreignData = std2array($this->{'model_' . $submodP}->get_by([$idForeign => $data[$idLocal]], false, true));

                              if($bFirstThread){

                                  $this->thread++;

                                  if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                    $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->setThumbs()->toArray();

                                  } else {

                                    $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->toArray();
                                  }

                              } else {

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->setThumbs()->toArray();

                                } else {

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->toArray();
                                }
                              }

                          } else if (is_array($data) && isset($data[setObject($idLocal)]) && $data[setObject($idLocal)] != null) {

                              $foreignData = std2array($this->{'model_' . $submodP}->get_by([$idForeign => $data[setObject($idLocal)]], false, true));

                              if($bFirstThread) {

                                  $this->thread++;

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->setThumbs()->toArray();

                                } else {

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->toArray();
                                }

                              } else {

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->setThumbs()->toArray();

                                } else {

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->toArray();

                                }
                              }

                          } else if (is_array($data) && isset($data[ucfirst(setObject($idLocal))]) && $data[ucfirst(setObject($idLocal))] != null) {

                              $foreignData = std2array($this->{'model_' . $submodP}->get_by([$idForeign => $data[ucfirst(setObject($idLocal))]], false, true));

                              if($bFirstThread) {

                                  $this->thread++;

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->setThumbs()->toArray();

                                } else {

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->toArray();

                                }

                              } else {

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->setThumbs()->toArray();

                                } else {

                                  $data['foreigns'][lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->toArray();

                                }

                              }

                          } else if (is_object($data) && objectHas($data,$idLocal) && $data->$idLocal != null) {

                              $foreignData = std2array($this->{'model_' . $submodP}->get_by([$idForeign => $data->$idLocal], false, true));

                              if($bFirstThread) {

                                  $this->thread++;

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->setThumbs()->toArray();

                                } else {

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->toArray();

                                }

                              } else {

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->setThumbs()->toArray();

                                } else {

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->toArray();

                                }
                              }

                          } else if (is_object($data) && objectHas($data,setObject($idLocal)) && $data->{setObject($idLocal)} != null) {

                              $foreignData = std2array($this->{'model_' . $submodP}->get_by([$idForeign => $data->{setObject($idLocal)}], false, true));

                              if($bFirstThread) {

                                  $this->thread++;

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->setThumbs()->toArray();

                                } else {

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->toArray();
                                }

                              } else {

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->setThumbs()->toArray();

                                } else {

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->toArray();

                                }

                              }

                          } else if (is_object($data) && objectHas($data,ucfirst(setObject($idLocal))) && $data->{ucfirst(setObject($idLocal))} != null) {

                              $foreignData = std2array($this->{'model_' . $submodP}->get_by([$idForeign => $data->{ucfirst(setObject($idLocal))}], false, true));

                              if($bFirstThread) {

                                  $this->thread++;

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->setThumbs()->toArray();

                                } else {

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setForeigns($foreignData,false)->toArray();

                                }

                              } else {

                                if($this->_table_name == 'es_files' && $this->nro_thumbs != null){

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->setThumbs()->toArray();

                                } else {

                                  $data->{'foreigns'}[lcfirst($field)] = $this->{'model_' . $submodP}->setFromData($foreignData)->toArray();

                                }
                              }
                          }
                      }
                    }

                }
            }

            $foreignsCommons = [
                'files' => [
                    'ids_files',
                ],
                'etiquetas' => 'id_etiquetas'
            ];

            foreach ($foreignsCommons as $fSubMod => $fCommon){

                if(is_array($fCommon)){

                    foreach ($fCommon as $fSubMod2 => $fCommon2){

                        $aForeignsFields = array();
                        $aForeignCommonName = explode('_',$fCommon2);
                        unset($aForeignCommonName[0]);
                        $fCommonName = implode('_',$aForeignCommonName);
                        list($fCommonS,$fCommonP) = setSingularPlural($fCommonName);

                        if(is_object($data) && objectHas($this,$fCommon2) && (
                                validateVar($this->{$fCommon2}) ||
                                validateVar($this->{$fCommon2}, 'numeric') ||
                                validateVar($this->{$fCommon2}, 'array') ||
                                validateVar($this->{$fCommon2}, 'object'))
                        ){
                            if(!isset($this->{"model_$fCommonP"})){

                                $this->{'init'.ucfirst(setObject($fSubMod))}();
                            }
                            if(in_array($fCommon2,$localKeys)){

                                $data->foreigns[lcfirst(setObject($fCommonS))] = $this->{"model_$fSubMod"}->{'findOneBy'.ucfirst(setObject('id_file'))}($this->{'get'.ucfirst(setObject($fCommon2))}());

                            } else {

                                $data->foreigns[$fCommonP] = $this->{"model_$fSubMod"}->{'findBy'.ucfirst(setObject($fCommon2))}($this->{'get'.ucfirst(setObject($fCommon2))}());
                            }

                        } else if(is_array($data) && objectHas($this,$fCommon2) && (
                                validateVar($this->{$fCommon2}) ||
                                validateVar($this->{$fCommon2}, 'numeric') ||
                                validateVar($this->{$fCommon2}, 'array') ||
                                validateVar($this->{$fCommon2}, 'object'))
                        ) {
                            if(!isset($this->{"model_$fSubMod"})){

                                $this->{'init'.ucfirst(setObject($fSubMod))}();
                            }
                            if(in_array($fCommon2,$localKeys)){

                                $data['foreigns'][lcfirst(setObject($fCommonS))] = $this->{"model_$fSubMod"}->{'findOneBy'.ucfirst(setObject('id_file'))}($this->{'get'.ucfirst(setObject($fCommon2))}());

                            } else {

                                $data['foreigns'][$fCommonP] = $this->{"model_$fSubMod"}->{'findBy'.ucfirst(setObject($fCommon2))}($this->{'get'.ucfirst(setObject($fCommon2))}());
                            }
                        }
                    }
                } else {

                    $aForeignCommonName = explode('_',$fCommon);
                    unset($aForeignCommonName[0]);
                    $fCommonName = implode('_',$aForeignCommonName);
                    list($fCommonS,$fCommonP) = setSingularPlural($fCommonName);

                    if(is_object($data) && isset($this->{$fCommon}) && (
                            validateVar($this->{$fCommon}) ||
                            validateVar($this->{$fCommon}, 'numeric') ||
                            validateVar($this->{$fCommon}, 'array') ||
                            validateVar($this->{$fCommon}, 'object'))
                    ){
                        if(!isset($this->{"model_$fCommonP"})){
                            $this->{'init'.ucfirst(setObject($fSubMod))}();
                        }
                        if(in_array($fCommon,$localKeys)){
                            $data->foreigns[lcfirst(setObject($fCommonS))] = $this->{"model_$fSubMod"}->{'findOneBy'.ucfirst(setObject('id_file'))}($this->{'get'.ucfirst(setObject($fCommon))}());
                        } else {
                            $data->foreigns[$fCommonP] = $this->{"model_$fSubMod"}->{'findBy'.ucfirst(setObject($fCommon))}($this->{'get'.ucfirst(setObject($fCommon))}());
                        }
                    } else if(is_array($data) && isset($this->{$fCommon}) && (
                            validateVar($this->{$fCommon}) ||
                            validateVar($this->{$fCommon}, 'numeric') ||
                            validateVar($this->{$fCommon}, 'array') ||
                            validateVar($this->{$fCommon}, 'object'))
                    ) {
                        if(!isset($this->{"model_$fSubMod"})){
                            $this->{'init'.ucfirst(setObject($fSubMod))}();
                        }
                        if(in_array($fCommon,$localKeys)){
                            $data['foreigns'][lcfirst(setObject($fCommonS))] = $this->{"model_$fSubMod"}->{'findOneBy'.ucfirst(setObject('id_file'))}($this->{'get'.ucfirst(setObject($fCommon))}());
                        } else {
                            $data['foreigns'][$fCommonP] = $this->{"model_$fSubMod"}->{'findBy'.ucfirst(setObject($fCommon))}($this->{'get'.ucfirst(setObject($fCommon))}());
                        }
                    }
                }
            }
        }
        return $data != null ? $this->setFromData($data) : $data;
    }
}
