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

if (!app_session_is_registered('app_selected_notification_items'))
{
	$app_selected_notification_items = array();
	app_session_register('app_selected_notification_items');
}

switch($app_module_action)
{
	case 'delete_selected':
		if(count($app_selected_notification_items))
		{
			db_query("delete from app_users_notifications where users_id='" . $app_user['id'] . "' and id in (" . implode(',', $app_selected_notification_items) . ")");
		}
		else
		{
			$alerts->add(TEXT_PLEASE_SELECT_ITEMS,'warning');
		}
		
		$app_selected_notification_items = array();
		
		redirect_to('users/notifications');
		
		break;
	case 'select':
		if(isset($_POST['checked']))
		{
			$app_selected_notification_items[] = $_POST['id'];
		}
		else
		{
			$key = array_search($_POST['id'], $app_selected_notification_items);
			if($key!==false)
			{
				unset($app_selected_notification_items[$key]);
			}
		}

		$app_selected_notification_items =  array_unique($app_selected_notification_items);
		
		exit();
		break;
	case 'select_all':
		
		$app_selected_notification_items = array();
		
		if(isset($_POST['checked']))
		{
			$itmes_query = db_query("select * from app_users_notifications where users_id='" . $app_user['id'] . "'");
			while($itmes = db_fetch_array($itmes_query))
			{
				$app_selected_notification_items[] = $itmes['id'];
			}
		}
		
		exit();
		break;
}		