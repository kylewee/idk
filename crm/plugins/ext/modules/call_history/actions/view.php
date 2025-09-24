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
    case 'listing':
        if(is_mobile())
        {
            require(component_path('ext/call_history/listing_mobile'));
        }
        else
        {
            require(component_path('ext/call_history/listing'));
        }
        exit();
        break;
        
    case 'set_star':
        $id = _POST('id');
        $is_star = _POST('is_star');
        db_query("update app_ext_call_history set is_star={$is_star} where id={$id}");
        exit();
        break;
    
    case 'delete':
        $id = _POST('id');                
        db_delete_row('app_ext_call_history', $id);
        exit();
        break;
}

