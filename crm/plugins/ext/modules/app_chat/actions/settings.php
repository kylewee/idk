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

$app_users_cfg->set('app_chat_active_dialog','');

switch($app_module_action)
{
	case 'save_sending_settings':	
		$app_users_cfg->set('chat_sending_settings',db_prepare_input($_POST['chat_sending_settings']));
		$app_users_cfg->set('chat_sound_notification',db_prepare_input($_POST['chat_sound_notification']));
		$app_users_cfg->set('chat_instant_notification',db_prepare_input($_POST['chat_instant_notification']));		
		break;
}