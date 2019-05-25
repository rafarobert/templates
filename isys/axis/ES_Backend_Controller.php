<?php
/**
 * Created by PhpStorm.
 * User: RaFaEl
 * Date: 2/22/2018
 * Time: 12:01 AM
 */

use Propel\Runtime\ActiveQuery\Criteria as Criteria;

class ES_Backend_Controller extends ES_Controller
{
    function __construct()
    {
        $CI = $this->initLoaded();
        parent::__construct();
        $this->data['subLayout'] = 'backend/_subLayout';

        $this->data['siteTitle'] = config_item('site_title');
        $this->data['metaTitle'] = config_item('meta_title');
        $this->data['metaName'] = config_item('meta_name');
        $this->data['metaKeywords'] = config_item('meta_keywords');
        $this->data['metaThemeColor'] = config_item('meta_theme_color"');
        $this->data['metaDescription'] = config_item('meta_descripcion');

        // ------------- img configurations ----------------
        $this->data['imgMaxHeight'] = config_item('img_max_height');
        $this->data['imgMaxWidth'] = config_item('img_max_width');
        $this->data['fileMaxSize'] = config_item('file_max_size');
        $this->data['fileTypes'] = config_item('file_types');
        $this->data['fileTypesJs'] = config_item('file_types_js');
        // -------------------------------------------------


//        $editTagsSet = CiSettingsQuery::create()->select(['EditTag'])->find()->getData();
//        $editTagsOpt = CiOptionsQuery::create()->select(['EditTag'])->find()->getData();
//        $editTags = array_merge($editTagsSet,$editTagsOpt);
//        $this->data['editTags'] = $editTags;
    }

}
