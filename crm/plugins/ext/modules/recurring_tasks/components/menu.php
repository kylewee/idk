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

if($app_module_path == 'items/info')
{
	$access_rules = new access_rules($current_entity_id, $current_item_id);

	if(users::has_access('repeat',$access_rules->get_access_schema()))
	{
		$app_plugin_menu['more_actions'][] = array('title'=>'<i class="fa fa-calendar-check-o"></i> ' . TEXT_EXT_REPEAT,'url'=>url_for('ext/recurring_tasks/repeat','path=' . $_GET['path']));
	}	
}

$tasks_query = db_query("select * from app_ext_recurring_tasks where created_by ='" . $app_user['id'] . "' limit 1 ");
if($tasks = db_fetch_array($tasks_query))
{
	$app_plugin_menu['account_menu'][] = array('title'=>TEXT_EXT_MY_RECURRING_TASKS, 'url'=>url_for('ext/recurring_tasks/my_recurring_tasks'), 'class'=>'fa-calendar-check-o');
}
	