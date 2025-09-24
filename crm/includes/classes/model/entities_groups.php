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

class entities_groups
{
    static function get_name_by_id($id)
    {
        if(!$id) return '';
        
        $info_query = db_query("select name from app_entities_groups where id={$id}");
        if($info = db_fetch_array($info_query))
        {
            return $info['name'];
        }
        else
        {
            return '';
        }
    }
    
    static function delete($id)
    {
        db_query("delete from app_entities_groups where id={$id}");
        db_query("update app_entities set group_id=0 where group_id={$id}");
    }
    
    static function get_choices()
    {
        $choices = [''=>''];
        $info_query = db_query("select id, name from app_entities_groups order by sort_order, name");
        while($info = db_fetch_array($info_query))
        {
            $choices[$info['id']]=$info['name'];
        }
        
        return $choices;
    }
}
