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

	if(!strlen(CFG_LOGIN_DIGITAL_SIGNATURE_MODULE))
	{
		redirect_to('users/login');
	}
	
	$module_info_query = db_query("select * from app_ext_modules where id='" . (int)CFG_LOGIN_DIGITAL_SIGNATURE_MODULE . "' and type='digital_signature' and is_active=1");
	if($module_info = db_fetch_array($module_info_query))
	{
		modules::include_module($module_info,'digital_signature');
		
		$module = new $module_info['module'];
	}
	else
	{
		redirect_to('users/login');
	}
	
	if(app_session_is_registered('app_logged_users_id'))
	{
		$app_module_action = 'logoff';
	}
	
	$app_layout = 'login_layout.php';
	
	//$module->send($module_info['id'],$send_to,$text);
	
	switch($app_module_action)
	{
		case 'login':
			$module->login($module_info['id']);
			break;
	}