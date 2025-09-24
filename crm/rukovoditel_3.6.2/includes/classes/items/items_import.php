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

class items_import
{
    static function prepare_default_choices($entity_id, $sql_data)
    {
        global $app_fields_cache;
        
        foreach($app_fields_cache[$entity_id] as $field)
        {
            if(!isset($sql_data['field_' . $field['id']]) and in_array($field['type'], fields_types::get_types_wich_choices()))
            {
                $cfg = new fields_types_cfg($field['configuration']);

                if ($cfg->get('use_global_list') > 0)
                {
                    $check_query = db_query("select id from app_global_lists_choices where lists_id = '" . db_input($cfg->get('use_global_list')) . "' and is_default=1");
                }
                else
                {
                    $check_query = db_query("select id from app_fields_choices where fields_id='" . $field['id'] . "' and is_default=1");
                }

                if ($check = db_fetch_array($check_query))
                {
                    $sql_data['field_' . $field['id']] = $check['id'];
                }
            }
        }
        
        return $sql_data;
    }
}
