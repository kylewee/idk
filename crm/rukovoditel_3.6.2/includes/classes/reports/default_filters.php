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

class default_filters {
    
    static function get_reports_id($entities_id,$reports_type, $auto_create_parent = true)
    {
        global $app_logged_users_id;
        
        //create default fitler
        $reports_info_query = db_query("select id from app_reports where entities_id='" . db_input($entities_id). "' and reports_type='" . $reports_type . "'");
        if(!$reports_info = db_fetch_array($reports_info_query))
        {
            $sql_data = array(
                'name'=>'',
                'entities_id'=>$entities_id,
                'reports_type'=>$reports_type,
                'in_menu'=>0,
                'in_dashboard'=>0,
                'listing_order_fields'=>'',	
                'created_by'=>$app_logged_users_id,
            );

            db_perform('app_reports',$sql_data);		
            $reports_id = db_insert_id();

            if($auto_create_parent)
            {
                reports::auto_create_parent_reports($reports_id);
            }
        }
        else
        {
            $reports_id = $reports_info['id'];
        }
        
        return $reports_id;
    }
}
