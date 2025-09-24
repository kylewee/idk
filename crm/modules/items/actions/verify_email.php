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

$access_rules = new access_rules($current_entity_id, $current_item_id);

if(!users::has_access('update',$access_rules->get_access_schema()) or $current_entity_id!=1)
{
    redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{    
    case 'verify':
        db_query("update app_entity_1 set is_email_verified=1 where id='" . db_input($current_item_id) . "'");
        exit();
        break;
}