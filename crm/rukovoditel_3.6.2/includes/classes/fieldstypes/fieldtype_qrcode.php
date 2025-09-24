<?php

/**
 * Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 * https://www.rukovoditel.net.ru/
 * 
 * CRM Руководитель - это свободное программное обеспечение, 
 * распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 * Государственная регистрация программы для ЭВМ: 2023664624
 * https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */

class fieldtype_qrcode
{

    public $options;

    function __construct()
    {
        $this->options = array('title' => TEXT_FIELDTYPE_QRCODE_TITLE);
    }

    function get_configuration()
    {
        $cfg = array();

        $cfg[] = array('title' => TEXT_QRCODE_PATTERN . fields::get_available_fields_helper($_POST['entities_id'], 'fields_configuration_pattern'),
            'name' => 'pattern',
            'type' => 'textarea',
            'tooltip' => TEXT_ENTER_TEXT_PATTERN_INFO,
            'params' => array('class' => 'form-control'));

        $cfg[] = array(
            'title' => TEXT_CODE_ERROR_CORRECTION,
            'name' => 'ecc',
            'type' => 'dropdown',
            'choices' => array('l' => TEXT_CODE_ERROR_CORRECTION_L, 'm' => TEXT_CODE_ERROR_CORRECTION_M, 'q' => TEXT_CODE_ERROR_CORRECTION_Q, 'h' => TEXT_CODE_ERROR_CORRECTION_H),
            'params' => array('class' => 'form-control input-medium'));

        $cfg[] = array(
            'title' => TEXT_PIXEL_SIZE,
            'name' => 'pixel_size',
            'type' => 'dropdown',
            'choices' => array('2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6),
            'params' => array('class' => 'form-control input-medium'));

        $cfg[] = array('title' => TEXT_HIDE_FIELD_ON_INFO_PAGE, 'name' => 'hide_field_if_empty', 'type' => 'checkbox');
        $cfg[] = array('title' => DISPLAY_QR_CODE_ON_ITEM_PAGE, 'name' => 'display_qr_code_on_item_page', 'type' => 'checkbox');
        
        

        return $cfg;
    }

    function render($field, $obj, $params = array())
    {
        return '';
    }

    function process($options)
    {
        return '';
    }

    function output($options)
    {
        global $app_user;

        $html = '';

        $cfg = new fields_types_cfg($options['field']['configuration']);

        $entities_id = $options['field']['entities_id'];

        $item = $options['item'];

        $fields_access_schema = users::get_fields_access_schema($entities_id, $app_user['group_id']);

        if(isset($options['custom_pattern']))
        {
            $pattern = $options['custom_pattern'];
        }
        else
        {
            $pattern = $cfg->get('pattern');
        }


        $html = self::prepare_pattern($entities_id,$item, $pattern);
        

        if(isset($options['is_email']))
        {
            if(isset($options['hide_attachments_url']) and $options['hide_attachments_url']==true)
            {
                $filename = 'QRcode.'  . $app_user['id'] . '.' . $item['id'] . '.' . $options['field']['id'] . '.png';
                return '<img src="cid:' . $filename . '">';
            }
            else
            {
                return '';
            }
        }
        elseif(isset($options['is_export']) and !isset($options['is_print']))
        {
            return $html;
        }
        else
        {
            if($cfg->get('display_qr_code_on_item_page')==1 or isset($options['is_print']))
            {
                return '<img src="data:image/png;base64,' . base64_encode(QRcode::png($html, false, $cfg->get('ecc'), $cfg->get('pixel_size'))) . '">';
            }
            else
            {
                return $html;
            }
        }

        
    }
    
    static function get_code_img($entities_id, $item, $field_id)
    {
        global $app_fields_cache;
        
        if(!isset_field($entities_id, $field_id)) return '';
        
        $field = $app_fields_cache[$entities_id][$field_id];
        
        if($field['type']!='fieldtype_qrcode') return '';
                        
        $cfg = new settings($field['configuration']);
        
        $pattern = $cfg->get('pattern');
        
        $html = self::prepare_pattern($entities_id,$item, $pattern);
        
        if(strlen($html))
        {
            return QRcode::png($html, false, $cfg->get('ecc'), $cfg->get('pixel_size'));
        }
        else
        {
            return '';
        }                
    }
    
    static function prepare_pattern($entities_id, $item, $pattern)
    {
        global $app_user;
        
        $html = '';
        
        if(!strlen($pattern)) return '';
        
        $pattern_original = $pattern;
                        
        $pattern = str_replace('[current_user_name]', $app_user['name'], $pattern);
        
        if(preg_match_all('/\[(\w+)\]/', $pattern, $matches))
        {
            //use to check if formulas fields using in text pattern
            $formulas_fields = false;

            foreach($matches[1] as $matches_key => $fields_id)
            {
                $field_query = db_query("select f.* from app_fields f where f.type not in ('fieldtype_action') and (f.id ='" . db_input($fields_id) . "' or type='fieldtype_" . db_input($fields_id) . "') and  f.entities_id='" . db_input($entities_id) . "'");
                if($field = db_fetch_array($field_query))
                {
                    //check field access
                    if(isset($fields_access_schema[$field['id']]))
                    {
                        if($fields_access_schema[$field['id']] == 'hide')
                            continue;
                    }

                    switch($field['type'])
                    {
                        case 'fieldtype_parent_item_id':
                            $enitites_info = db_find('app_entities', $entities_id);

                            if($enitites_info['parent_id'] > 0 and $item['parent_item_id'] > 0)
                            {
                                $value = items::get_heading_field($enitites_info['parent_id'], $item['parent_item_id']);
                            }
                            else
                            {
                                $value = '';
                            }
                            break;
                        case 'fieldtype_created_by':
                            $value = $item['created_by'];
                            break;
                        case 'fieldtype_date_added':
                            $value = $item['date_added'];
                            break;
                        case 'fieldtype_action':
                        case 'fieldtype_id':
                            $value = $item['id'];
                            break;
                        case 'fieldtype_formula':
                            //check if formula value exist in item and if not then do extra query to calcualte it
                            if(strlen($item['field_' . $field['id']]) == 0)
                            {
                                //prepare forumulas query
                                if(!$formulas_fields)
                                {
                                    $formulas_fields_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($entities_id, '') . " from app_entity_" . $entities_id . " e where id='" . $item['id'] . "'");
                                    $formulas_fields = db_fetch_array($formulas_fields_query);
                                }

                                $value = $item['field_' . $field['id']] = $formulas_fields['field_' . $field['id']];
                            }
                            else
                            {
                                $value = $item['field_' . $field['id']];
                            }
                            break;
                        default:
                            $value = $item['field_' . $field['id']];
                            break;
                    }

                    $output_options = array('class' => $field['type'],
                        'value' => $value,
                        'field' => $field,
                        'item' => $item,
                        'is_export' => true,
                        'path' => '');

                    if(in_array($field['type'], array('fieldtype_textarea_wysiwyg')))
                    {
                        $output = trim(fields_types::output($output_options));
                    }
                    elseif($field['type'] == 'fieldtype_parent_item_id')
                    {
                        $output = $value;
                    }
                    else
                    {
                        $output = trim(strip_tags(fields_types::output($output_options)));
                    }

                    //echo '<br>' . $fields_id . ' ' . $output . ' ' . $matches[0][$matches_key];

                    $pattern = str_replace($matches[0][$matches_key], $output, $pattern);
                }
            }

            //check if fields was replaced
            if(preg_replace('/\[(\d+)\]/', '', $pattern_original) != $pattern)
            {
                $html = $pattern;
            }
        }
        
        return $html;
        
    }

}
