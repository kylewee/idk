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

$app_process_info_query = db_query("select * from app_ext_processes where id='" . _get::int('id'). "' and (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to) or assigned_to_all=1) and is_active=1");
if(!$app_process_info = db_fetch_array($app_process_info_query))
{
	redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{
	case 'confirmation':
		
		$module_query = db_query("select * from app_ext_modules where id='" .  _get::int('module_id') . "' and is_active=1");
		if($module = db_fetch_array($module_query))
		{
			$modules = new modules('payment');
			
			$payment_module = new $module['module'];
			
			echo $payment_module->confirmation($module['id'],_get::int('id'));
		}
		
		exit();
		break;
}