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

class fieldtype_user_lastname
{

    public $options;

    function __construct()
    {
        $this->options = array('name' => TEXT_FIELDTYPE_USER_LASTNAME_TITLE, 'title' => TEXT_FIELDTYPE_USER_LASTNAME_TITLE);
    }

    function get_configuration()
    {
        $cfg = array();

        $cfg[] = array('title' => TEXT_ALLOW_SEARCH, 'name' => 'allow_search', 'type' => 'checkbox', 'tooltip_icon' => TEXT_ALLOW_SEARCH_TIP);
        $cfg[] = array('title' => TEXT_DISABLE, 'name' => 'is_disabled', 'type' => 'checkbox');

        return $cfg;
    }

    function render($field, $obj, $params = array())
    {
        $cfg = new fields_types_cfg($field['configuration']);
        
        $html = '';
        $requried_class = 'required';
        
        if($cfg->get('is_disabled')==1)
        {
            $requried_class = '';
            $html = '<style>.form-group-8{display:none}</style>';
            $obj['field_' . $field['id']] = '';
        }
        
        return input_tag('fields[' . $field['id'] . ']', $obj['field_' . $field['id']], array('class' => 'form-control input-medium noSpace ' . $requried_class)) . $html;
    }

    function process($options)
    {
        return db_prepare_input($options['value']);
    }

    function output($options)
    {
        return $options['value'];
    }

}
