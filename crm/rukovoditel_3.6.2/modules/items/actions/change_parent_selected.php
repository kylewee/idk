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

switch($app_module_action)
{
    case 'change_parent':
        $parent_id  = isset($_POST['parent_id']) ? _POST('parent_id') : 0;
        
        $reports_id = _POST('reports_id');
        
        if(!isset($app_selected_items[$reports_id])) $app_selected_items[$reports_id] = array();
               
        if(count($app_selected_items[$reports_id]) and !in_array($parent_id,$app_selected_items[$reports_id]))
        {            
            db_query("update app_entity_{$current_entity_id} set parent_id={$parent_id} where id in (" . implode(',',$app_selected_items[$reports_id]). ")");
            
            db_query("update app_entity_{$current_entity_id} set parent_id=0 where id={$parent_id} and parent_id in (" . implode(',',$app_selected_items[$reports_id]). ")");
        }
                
        redirect_to('items/items','path=' . $app_path);
        break;
}

