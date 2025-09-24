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

$log_type = $_GET['type']??'';

if(!in_array($log_type,['http','mysql','php','email']))
{
    redirect_to('logs/settings');
}

switch($app_module_action)
{
    case 'listing':            
        
        require(component_path('logs/' . $log_type . '_listing'));
        app_exit();
        
        break;
    case 'reset':
        db_query("delete from app_logs where log_type='{$log_type}'");
        
        redirect_to('logs/view','type=' . $log_type);
        break;
}

