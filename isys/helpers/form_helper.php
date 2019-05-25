<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Form Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/helpers/form_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists('form_open'))
{
	/**
	 * Form Declaration
	 *
	 * Creates the opening portion of the form.
	 *
	 * @param	string	the URI segments of the form destination
	 * @param	array	a key/value pair of attributes
	 * @param	array	a key/value pair hidden data
	 * @return	string
	 */
	function form_open($action = '', $attributes = array(), $hidden = array())
	{
		$CI =& get_instance();

		// If no action is provided then set to the current url
		if ( ! $action)
		{
			$action = $CI->config->site_url($CI->uri->uri_string());
		}
		// If an action is not a full URL then turn it into one
		elseif (strpos($action, '://') === FALSE)
		{
			$action = $CI->config->site_url($action);
		}

		$attributes = _attributes_to_string($attributes);

		if (stripos($attributes, 'method=') === FALSE)
		{
			$attributes .= ' method="post"';
		}

		if (stripos($attributes, 'accept-charset=') === FALSE)
		{
			$attributes .= ' accept-charset="'.strtolower(config_item('charset')).'"';
		}

		$form = '<form action="'.$action.'"'.$attributes.">\n";

		// Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
		if ($CI->config->item('csrf_protection') === TRUE && strpos($action, $CI->config->base_url()) !== FALSE && ! stripos($form, 'method="get"'))
		{
			$hidden[$CI->security->get_csrf_token_name()] = $CI->security->get_csrf_hash();
		}

		if (is_array($hidden))
		{
			foreach ($hidden as $name => $value)
			{
				$form .= '<input type="hidden" name="'.$name.'" value="'.html_escape($value).'" style="display:none;" />'."\n";
			}
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_open_multipart'))
{
	/**
	 * Form Declaration - Multipart type
	 *
	 * Creates the opening portion of the form, but with "multipart/form-data".
	 *
	 * @param	string	the URI segments of the form destination
	 * @param	array	a key/value pair of attributes
	 * @param	array	a key/value pair hidden data
	 * @return	string
	 */
	function form_open_multipart($action = '', $attributes = array(), $hidden = array())
	{
		if (is_string($attributes))
		{
			$attributes .= ' enctype="multipart/form-data"';
		}
		else
		{
			$attributes['enctype'] = 'multipart/form-data';
		}

		return form_open($action, $attributes, $hidden);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_hidden'))
{
	/**
	 * Hidden Input Field
	 *
	 * Generates hidden fields. You can pass a simple key/value string or
	 * an associative array with multiple values.
	 *
	 * @param	mixed	$name		Field name
	 * @param	string	$value		Field value
	 * @param	bool	$recursing
	 * @return	string
	 */
	function form_hidden($name, $value = '', $recursing = FALSE)
	{
		static $form;

		if ($recursing === FALSE)
		{
			$form = "\n";
		}

		if (is_array($name))
		{
//			foreach ($name as $key => $val)
//			{
//				form_hidden($key, $val, TRUE);
//            }
//
//            return $form;
//            $name['type'] = 'hidden';

            if(inArray('class',$name)){

                $name['class'] .= ' display-none';

            } else {

                $name['class'] = 'display-none';
            }
            return form_input($name,$value);
		}

		if ( ! is_array($value))
		{
			$form .= '<input type="text" name="'.$name.'" value="'.html_escape($value)."\" class=\"display-none\"/>\n";
		}
		else
		{
			foreach ($value as $k => $v)
			{
				$k = is_int($k) ? '' : $k;
				form_hidden($name.'['.$k.']', $v, TRUE);
			}
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_input'))
{
	/**
	 * Text Input Field
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_input($data = '', $value = '', $extra = '')
	{
		$defaults = array(
			'type' => 'text',
			'name' => is_array($data) ? '' : $data,
			'value' => $value
		);
		$toReturn =  '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
		if(validateArray($data,'helpText')){
		    $helpText = $data['helpText'];
		    $toReturn .= "<span class='help-block m-b-none'>$helpText</span>\n";
        }

        return $toReturn;
	}
}

if ( ! function_exists('form_email'))
{
	/**
	 * Text Input Field
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_email($data = '', $value = '', $extra = '')
	{
		$defaults = array(
			'type' => 'email',
			'name' => is_array($data) ? '' : $data,
			'value' => $value
		);
		$toReturn =  '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
		if(validateArray($data,'helpText')){
		    $helpText = $data['helpText'];
		    $toReturn .= "<span class='help-block m-b-none'>$helpText</span>\n";
        }

        return $toReturn;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_password'))
{
	/**
	 * Password Field
	 *
	 * Identical to the input function but adds the "password" type
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_password($data = '', $value = '', $extra = '')
	{
		is_array($data) OR $data = array('name' => $data);
		$data['type'] = 'password';
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_upload'))
{
	/**
	 * Upload Field
	 *
	 * Identical to the input function but adds the "file" type
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_upload($data = '', $value = '', $extra = '')
	{
	    if($value != ''){
	        $data['value'] = $value;
        }
		$defaults = array('type' => 'file', 'name' => '');
		is_array($data) OR $data = array('name' => $data);
		$data['type'] = 'file';


		return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_textarea'))
{
	/**
	 * Textarea field
	 *
	 * @param	mixed	$data
	 * @param	string	$value
	 * @param	mixed	$extra
	 * @return	string
	 */
	function form_textarea($data = '', $value = '', $extra = '')
	{
		$defaults = array(
			'name' => is_array($data) ? '' : $data,
			'cols' => '40',
			'rows' => '10'
		);

		if ( ! is_array($data) OR ! isset($data['value']))
		{
			$val = $value;
		}
		else
		{
			$val = $data['value'];
			unset($data['value']); // textareas don't use the value attribute
		}

		return '<textarea '._parse_form_attributes($data, $defaults)._attributes_to_string($extra).'>'
			.html_escape($val)
			."</textarea>\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_multiselect'))
{
	/**
	 * Multi-select menu
	 *
	 * @param	string
	 * @param	array
	 * @param	mixed
	 * @param	mixed
	 * @return	string
	 */
	function form_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
	{
		$extra = _attributes_to_string($extra);
		if (stripos($extra, 'multiple') === FALSE)
		{
			$extra .= ' multiple="multiple"';
		}

		return form_dropdown($name, $options, $selected, $extra);
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('form_dropdown'))
{
	/**
	 * Drop-down Menu
	 *
	 * @param	mixed	$data
	 * @param	mixed	$options
	 * @param	mixed	$selected
	 * @param	mixed	$extra
	 * @return	string
	 */
	function form_dropdown($data = '', $options = array(), $selected = array(), $extra = '')
	{
        $bNone = false;

        if(is_object($selected)){
            $selected = std2array($selected);
        }
        if(validateArray($data, 'related')){
            $aRelated = array();
            foreach ($data['related'] as $idWanted => $idRelated){
                if(isset($options[$idRelated])){
                    $aRelated[$idWanted] = $options[$idRelated];
                }
            }
            $options = $aRelated;
        }
        if(!validateVar($options, 'array', false) &&
            validateVar($options, 'string', false) &&
            $extra = ''){
            $extra = $selected;
            $selected = $options;
            $options = array();
        }
		$defaults = array();

		if (is_array($data))
		{
			if (isset($data['selected']))
			{
				$selected = $data['selected'];
				unset($data['selected']); // select tags don't have a selected attribute
			}

			if (isset($data['options']) && validateVar($data['options'],'array'))
			{
				$options = $data['options'];
				unset($data['options']); // select tags don't use an options attribute
			}
		}
		else
		{
			$defaults = array('name' => $data);
		}

		is_array($selected) OR $selected = array($selected);
		is_array($options) OR $options = array($options);

		// If no selected state was submitted we will attempt to set it automatically
		if (empty($selected))
		{
			if (is_array($data))
			{
				if (isset($data['name'], $_POST[$data['name']]))
				{
					$selected = array($_POST[$data['name']]);
				}
			}
			elseif (isset($_POST[$data]))
			{
				$selected = array($_POST[$data]);
			}
		}

		$extra = _attributes_to_string($extra);

		$multiple = (count($selected) > 1 && stripos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

		$form = '<select '.rtrim(_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";

		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val))
			{
				if (empty($val))
				{
					continue;
				}

				$form .= '<optgroup label="'.$key."\">\n";
				if(!$bNone) {
                    $form .= '<option value="" name="'.$data['name'].'">Ninguno</option>';
                    $bNone = true;
                }
				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
					$form .= '<option value="'.html_escape($optgroup_key).'"'.$sel.'>'
						.(string) $optgroup_val."</option>\n";
				}

				$form .= "</optgroup>\n";
			}
			else
			{
                if (!$bNone) {
                    $form .= '<option value="" name="' . $data['name'] . '">Ninguno</option>';
                    $bNone = true;
                }
                $form .= '<option value="' . html_escape($key) . '"'
                    . (in_array($key, $selected) ? ' selected="selected"' : '') . '>'
                    . (string)$val . "</option>\n";

            }
		}

		return $form."</select>\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_checkbox'))
{
	/**
	 * Checkbox Field
	 *
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @return	string
	 */
	function form_checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
	{
	    if(validateVar($value, 'array')){
            $data['option'] = array_values($value)[0];
            $value = array_keys($value)[0];
        }
		$defaults = array('type' => 'checkbox', 'name' => ( ! is_array($data) ? $data : ''), 'value' => $value);
        $data['value'] = $value;
		if (is_array($data) && array_key_exists('checked', $data))
		{
		    if(validateArray($data,'checked')){
                $checked = validateVar($data['checked'],'bool') ? $data['checked'] : FALSE;
            }

			if ($checked == FALSE)
			{
				unset($data['checked']);
			}
			else
			{
				$data['checked'] = 'checked';
			}
		}

		if ($checked == TRUE)
		{
			$defaults['checked'] = 'checked';
		}
		else
		{
			unset($defaults['checked']);
		}
        if(validateArray($data,'option')){
		    $options = [$value => $data['option']];
            return set_options($data,$options,$checked,$extra);
        } else {
            return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />";
        }
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_radio'))
{
	/**
	 * Radio Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @return	string
	 */
	function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		is_array($data) OR $data = array('name' => $data);
		$data['type'] = 'radio';
		return form_checkbox($data, $value, $checked, $extra);
	}
}
if ( ! function_exists('form_radios'))
{
	/**
	 * Radio Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @return	array
	 */
    function form_radios($data = '', $options = array(), $checked = FALSE, $extra = '')
    {
        if(!validateVar($options, 'array', false) &&
            validateVar($options, 'bool', false &&
            $extra == '')
        ){
            $extra = $checked;
            $checked = $options;
            $options = array();
        }

        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'radio';
        return set_options($data,$options,$checked,$extra);
    }
}

if ( ! function_exists('form_checkboxes'))
{
    /**
     * Radio Button
     *
     * @param	mixed
     * @param	string
     * @param	bool
     * @param	mixed
     * @return	array
     */
    function form_checkboxes($data = '', $options = '', $checked = FALSE, $extra = '')
    {
        if(!validateVar($options, 'array', false) &&
            validateVar($options, 'bool', false &&
                $extra == '')
        ){
            $extra = $checked;
            $checked = $options;
            $options = array();
        }
        is_array($data) OR $data = array('name' => $data);
        return set_options($data,$options,$checked,$extra);
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('distribute_options'))
{
	/**
	 * Submit Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function distribute_options($data, $options)
	{
        $fields = array();
        // **************** busca imagenes ******************
        if(validateArray($data,'table')){
            $table = $data['table'];
        } else if(validateArray($data,'subTable')){
            $table = $data['subTable'];
        }
        if(validateArray($data,'table') || validateArray($data,'subTable')){
            $delimiter = ' ';
            list($mod,$submod) = getModSubMod($table);
            foreach ($options as $i => $value){
                if(strhas($value,'|')){
                    $delimiter = '|';
                    $fields[$i] = explode('|',$value);
                } else if(strhas($value,',')){
                    $delimiter = ',';
                    $fields[$i] = explode(',',$value);
                }
            }
        }
        $aOptions = array();
        $aImgs = array();
        $aData = array();
        foreach ($fields as $i => $objects){
            foreach ($objects as $j => $field){
                $fieldName = str_replace(' ','',$field);
                $fileParts = explode('.',$fieldName);
                if(validateArray($fileParts,1)){
                    $fieldName = $fileParts[0].'-thumb_50.'.$fileParts[1];
                } else {
                    $fieldName = $fieldName;
                }
                $fileUrl = "assets/img/$submod/thumbs/".$fieldName;
                if(is_file(FCPATH.$fileUrl) && !strhas($fileUrl,"<") && !strhas($fileUrl,">")){
                    $aImgs[$i][$j] = site_url($fileUrl);
                } else if(validateVar($field,'numeric')){
                    $aData[$i][$j] = intval($field);
                } else {
                    $aOptions[$i][$j] = $field;
                }
            }
        }
        foreach ($aOptions as $i => $option){
            $options[$i] = implode($delimiter,$option);
        }
        // *************** hasta aqui se busca imagenes **************
        return [$options, $aImgs, $aData];
	}
}

if ( ! function_exists('set_options'))
{
	/**
	 * Submit Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function set_options($data, $options, $checked = FALSE, $extra = '')
	{
        list($options, $aImgs, $aData) = distribute_options($data, $options);
        $iCheckOpen = validateArray($data,'i-checks') || (validateArray($data,'class') && strstr($data['class'],'i-check')) ? "<div class='i-checks'>" : "";
        $iCheckClose = validateArray($data,'i-checks') || (validateArray($data,'class') && strstr($data['class'],'i-check')) ? "</div>" : "";
        $labelOpen = '<label>';
        $labelClose = '</label>';
        if(validateArray($data,'options')){
            $options = $data['options'];
            unset($data['options']);
        }
        if(validateVar($options,'array')){
            $htmlOptions = '';
            foreach ($options as $key => $option){

                // ****************** incluye data *******************
                if(validateVar($aData,'array')){
                    foreach ($aData[$key] as $l => $idRef){
                        $data['data-'.$l] = $idRef;
                    }
                }
                if(validateVar($aImgs,'array')){
                    foreach ($aImgs[$key] as $img){
                        $htmlOptions .= img($img);
                    }
                }
                // ****************** hasta aqui incluye  imagenes *******************
                $checkeds = [];
                if(validateArray($data,'checked')){
                    $checkeds = $data['checked'];
                } else if(validateVar($checked,'array')){
                    $checkeds = $checked;
                }
                unset($data['option']);
                if(strstr($data['name'],'[]')){
                    $dataName = str_replace('[]','',$data['name']);
                    $data['id'] = 'input'.ucfirst($dataName).ucfirst(cleanWhiteSpaces($option));
                } else {
                    $data['id'] = 'input'.ucfirst($data['name']).ucfirst(cleanWhiteSpaces($option));
                }
                if(validateVar($checkeds,'array')){
                    if(in_array($option,$checkeds) || in_array($key,$checkeds)){
                        $htmlOptions .= $iCheckOpen.$labelOpen.form_checkbox($data, $key, true, $extra).'<span>'.ucfirst($option)."</span>$labelClose.$iCheckClose";
                    } else {
                        $htmlOptions .= $iCheckOpen.$labelOpen.form_checkbox($data, $key, false, $extra).'<span>'.ucfirst($option)."</span>$labelClose.$iCheckClose";
                    }
                } else {
                    if(is_object($checked)){
                        foreach ($checked as $chk){
                            if($chk == $key){
                                $htmlOptions .= $iCheckOpen.$labelOpen.form_checkbox($data, $key, true, $extra).'<span>'.ucfirst($option)."</span>$labelClose.$iCheckClose";
                            } else {
                                $htmlOptions .= $iCheckOpen.$labelOpen.form_checkbox($data, $key, false, $extra).'<span>'.ucfirst($option)."</span>$labelClose.$iCheckClose";
                            }
                        }
                    } else {
                        $data['id'] = 'input'.ucfirst($data['name']).ucfirst(cleanWhiteSpaces($option));
                        if($checked == $key){
                            $htmlOptions .= $iCheckOpen.$labelOpen.form_checkbox($data, strval($key), true, $extra).'<span>'.ucfirst($option)."</span>$labelClose.$iCheckClose";
                        } else {
                            $htmlOptions .= $iCheckOpen.$labelOpen.form_checkbox($data, strval($key), false, $extra).'<span>'.ucfirst($option)."</span>$labelClose.$iCheckClose";
                        }
                    }
                }
            }
            return $htmlOptions;
        } else {
            return 'Options not Registered';
        }
	}
}

if ( ! function_exists('form_submit'))
{
	/**
	 * Submit Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_submit($data = '', $value = '', $extra = '')
	{
		$defaults = array(
			'type' => 'submit',
			'name' => is_array($data) ? '' : $data,
			'value' => $value
		);

		return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_reset'))
{
	/**
	 * Reset Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_reset($data = '', $value = '', $extra = '')
	{
		$defaults = array(
			'type' => 'reset',
			'name' => is_array($data) ? '' : $data,
			'value' => $value
		);

		return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_button'))
{
	/**
	 * Form Button
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_button($data = '', $content = '', $extra = '')
	{
		$defaults = array(
			'name' => is_array($data) ? '' : $data,
			'type' => 'button'
		);

		if (is_array($data) && isset($data['content']))
		{
			$content = $data['content'];
			unset($data['content']); // content is not an attribute
		}

		return '<button '._parse_form_attributes($data, $defaults)._attributes_to_string($extra).'>'
			.$content
			."</button>\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_label'))
{
	/**
	 * Form Label Tag
	 *
	 * @param	string	The text to appear onscreen
	 * @param	string	The id the label applies to
	 * @param	string	Additional attributes
	 * @return	string
	 */
	function form_label($label_text = '', $id = '', $attributes = array())
	{

		$label = '<label';

		if ($id !== '')
		{
			$label .= ' for="'.$id.'"';
		}

		if (is_array($attributes) && count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' '.$key.'="'.$val.'"';
			}
		}

		return $label.'>'.$label_text.'</label>';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_fieldset'))
{
	/**
	 * Fieldset Tag
	 *
	 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
	 * use form_fieldset_close()
	 *
	 * @param	string	The legend text
	 * @param	array	Additional attributes
	 * @return	string
	 */
	function form_fieldset($legend_text = '', $attributes = array())
	{
		$fieldset = '<fieldset'._attributes_to_string($attributes).">\n";
		if ($legend_text !== '')
		{
			return $fieldset.'<legend>'.$legend_text."</legend>\n";
		}

		return $fieldset;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_fieldset_close'))
{
	/**
	 * Fieldset Close Tag
	 *
	 * @param	string
	 * @return	string
	 */
	function form_fieldset_close($extra = '')
	{
		return '</fieldset>'.$extra;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_close'))
{
	/**
	 * Form Close Tag
	 *
	 * @param	string
	 * @return	string
	 */
	function form_close($extra = '')
	{
		return '</form>'.$extra;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_prep'))
{
	/**
	 * Form Prep
	 *
	 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
	 *
	 * @deprecated	3.0.0	An alias for html_escape()
	 * @param	string|string[]	$str		Value to escape
	 * @return	string|string[]	Escaped values
	 */
	function form_prep($str)
	{
		return html_escape($str, TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_value'))
{
	/**
	 * Form Value
	 *
	 * Grabs a value from the POST array for the specified field so you can
	 * re-populate an input field or textarea. If Form Validation
	 * is active it retrieves the info from the validation class
	 *
	 * @param	string	$field		Field name
	 * @param	string	$default	Default value
	 * @param	bool	$html_escape	Whether to escape HTML special characters or not
	 * @return	string
	 */
	function set_value($field, $default = '', $html_escape = TRUE)
	{
		$CI =& get_instance();

		if(validateVar($default,'array') || validateVar($default,'object')){

        }
		$value = (isset($CI->form_validation) && is_object($CI->form_validation) && $CI->form_validation->has_rule($field))
			? $CI->form_validation->set_value($field, $default)
			: $CI->input->post($field, FALSE);

		isset($value) OR $value = $default;
		return ($html_escape) ? html_escape($value) : $value;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_select'))
{
	/**
	 * Set Select
	 *
	 * Let's you set the selected value of a <select> menu via data in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function set_select($field, $value = '', $default = FALSE)
	{
		$CI =& get_instance();

		if (isset($CI->form_validation) && is_object($CI->form_validation) && $CI->form_validation->has_rule($field))
		{
			return $CI->form_validation->set_select($field, $value, $default);
		}
		elseif (($input = $CI->input->post($field, FALSE)) === NULL)
		{
			return ($default === TRUE) ? ' selected="selected"' : '';
		}

		$value = (string) $value;
		if (is_array($input))
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($input as &$v)
			{
				if ($value === $v)
				{
					return ' selected="selected"';
				}
			}

			return '';
		}

		return ($input === $value) ? ' selected="selected"' : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_checkbox'))
{
	/**
	 * Set Checkbox
	 *
	 * Let's you set the selected value of a checkbox via the value in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function set_checkbox($field, $value = '', $default = FALSE)
	{
		$CI =& get_instance();

		if (isset($CI->form_validation) && is_object($CI->form_validation) && $CI->form_validation->has_rule($field))
		{
			return $CI->form_validation->set_checkbox($field, $value, $default);
		}

		// Form inputs are always strings ...
		$value = (string) $value;
		$input = $CI->input->post($field, FALSE);

		if (is_array($input))
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($input as &$v)
			{
				if ($value === $v)
				{
					return ' checked="checked"';
				}
			}

			return '';
		}

		// Unchecked checkbox and radio inputs are not even submitted by browsers ...
		if ($CI->input->method() === 'post')
		{
			return ($input === $value) ? ' checked="checked"' : '';
		}

		return ($default === TRUE) ? ' checked="checked"' : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_radio'))
{
	/**
	 * Set Radio
	 *
	 * Let's you set the selected value of a radio field via info in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param	string	$field
	 * @param	string	$value
	 * @param	bool	$default
	 * @return	string
	 */
	function set_radio($field, $value = '', $default = FALSE)
	{
		$CI =& get_instance();

		if (isset($CI->form_validation) && is_object($CI->form_validation) && $CI->form_validation->has_rule($field))
		{
			return $CI->form_validation->set_radio($field, $value, $default);
		}

		// Form inputs are always strings ...
		$value = (string) $value;
		$input = $CI->input->post($field, FALSE);

		if (is_array($input))
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($input as &$v)
			{
				if ($value === $v)
				{
					return ' checked="checked"';
				}
			}

			return '';
		}

		// Unchecked checkbox and radio inputs are not even submitted by browsers ...
		if ($CI->input->method() === 'post')
		{
			return ($input === $value) ? ' checked="checked"' : '';
		}

		return ($default === TRUE) ? ' checked="checked"' : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_error'))
{
	/**
	 * Form Error
	 *
	 * Returns the error for a specific form field. This is a helper for the
	 * form validation class.
	 *
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function form_error($field = '', $prefix = '', $suffix = '')
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			return '';
		}

		return $OBJ->error($field, $prefix, $suffix);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('validation_errors'))
{
	/**
	 * Validation Error String
	 *
	 * Returns all the errors associated with a form submission. This is a helper
	 * function for the form validation class.
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function validation_errors($prefix = '', $suffix = '')
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			return '';
		}

		return $OBJ->error_string($prefix, $suffix);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_parse_form_attributes'))
{
	/**
	 * Parse the form attributes
	 *
	 * Helper function used by some of the form helpers
	 *
	 * @param	array	$attributes	List of attributes
	 * @param	array	$default	Default values
	 * @return	string
	 */
	function _parse_form_attributes($attributes, $default)
	{
		if (is_array($attributes))
		{
			foreach ($default as $key => $val)
			{
				if (isset($attributes[$key]))
				{
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}

			if (count($attributes) > 0)
			{
				$default = array_merge($attributes, $default);
			}
		}

		$att = '';

		foreach ($default as $key => $val)
		{
			if ($key === 'value')
			{
				$val = html_escape($val);
			}
			elseif ($key === 'name' && ! strlen($default['name']))
			{
				continue;
			}
			if(is_object($val)){
			    $val = std2array($val);
            }
            if(validateVar($val)){
                $att .= $key.'="'.$val.'" ';
            }
		}

		return $att;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_attributes_to_string'))
{
	/**
	 * Attributes To String
	 *
	 * Helper function used by some of the form helpers
	 *
	 * @param	mixed
	 * @return	string
	 */
	function _attributes_to_string($attributes)
	{
		if (empty($attributes))
		{
			return '';
		}

		if (is_object($attributes))
		{
			$attributes = (array) $attributes;
		}

		if (is_array($attributes))
		{
			$atts = '';

			foreach ($attributes as $key => $val)
			{
				$atts .= ' '.$key.'="'.$val.'"';
			}

			return $atts;
		}

		if (is_string($attributes))
		{
			return ' '.$attributes;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_get_validation_object'))
{
	/**
	 * Validation Object
	 *
	 * Determines what the form validation class was instantiated as, fetches
	 * the object and returns it.
	 *
	 * @return	mixed
	 */
	function &_get_validation_object()
	{
		$CI =& get_instance();

		// We set this as a variable since we're returning by reference.
		$return = FALSE;

		if (FALSE !== ($object = $CI->load->is_loaded('Form_validation')))
		{
			if ( ! isset($CI->$object) OR ! is_object($CI->$object))
			{
				return $return;
			}

			return $CI->$object;
		}

		return $return;
	}
}


if ( ! function_exists('form_select'))
{
    function form_select($data = '', $options = array(), $selected = array(), $extra = '')
    {
        echo form_dropdown($data, $options, $selected, $extra);
    }
}

if ( ! function_exists('form_number'))
{
    function form_number($data = '', $values = array(), $selected = array(), $extra = '')
    {
        $data['type'] = 'number';
        if(validateVar($values,'array',false)){
            echo form_dropdown($data, $values, $selected, $extra);
        } else {
            echo form_input($data, $values, $extra);
        }
    }
}

if ( ! function_exists('form_default'))
{
    function form_default($data = '', $values = array(), $selected = array(), $extra = '')
    {
        if(validateVar($values,'array',false)){
            echo form_dropdown($data, $values, $selected, $extra);
        } else {
            if(validateVar($selected) && $extra == ''){
                $extra = $selected;
            }
            echo form_input($data, $values, $extra);
        }
    }
}

if ( ! function_exists('form_static'))
{
    function form_static($data = '', $values = array(), $extra = '')
    {
        if(validateVar($values,'array',false)){
            echo form_dropdown($data, $values, [], $extra);
        } else if(validateVar($values)){
            echo "<p $extra>$values</p>";
        }
    }
}

if ( ! function_exists('form_disabled'))
{
    function form_disabled($data = '', $values = array(), $selected = array(), $extra = '')
    {
        $extra = strpos($extra,'disabled') > -1 ? '' : 'disabled';
        if(validateVar($values,'array',false)){
            echo form_dropdown($data, $values, $selected, $extra);
        } else {
            echo form_input($data, $values, $extra);
        }
    }
}

if ( ! function_exists('form_image'))
{
    /**
     * Upload Field
     *
     * Identical to the input function but adds the "file" type
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_image($data = '', $value = '', $extra = '')
    {
        $data['accept'] = 'image/';
        $data['class'] = validateArray($data,'class') ? $data['class'].' hide' : 'hide';
        if($value != ''){
            $data['value'] = $value;
        }
        $defaults = array('type' => 'file', 'name' => '');
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'file';


        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}