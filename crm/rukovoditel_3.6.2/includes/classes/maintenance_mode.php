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

class maintenance_mode
{
	static function login_message()
	{
		$html = '';
		
		if(CFG_MAINTENANCE_MODE==1)
		{
			$html = '
					<div class="alert alert-block alert-warning fade in">
						<h4>' . (strlen(CFG_MAINTENANCE_MESSAGE_HEADING)>0 ? CFG_MAINTENANCE_MESSAGE_HEADING : TEXT_MAINTENANCE_MESSAGE_HEADING) . '</h4>
						<p>' . (strlen(CFG_MAINTENANCE_MESSAGE_CONTENT)>0 ? CFG_MAINTENANCE_MESSAGE_CONTENT : TEXT_MAINTENANCE_MESSAGE_CONTENT). '</p>
					</div>
					';
		}
		
		return $html;
	}
	
	static function header_message()
	{
		$html = '';
		
		if(CFG_MAINTENANCE_MODE==1)
		{
			$html = '
					<span class="label label-warning">' . TEXT_MAINTENANCE_MODE . '</span>
					';
		}
		
		return $html;
	}
	
	static function check()
	{
		global $app_user, $app_module_path, $alerts;
					
		if(app_session_is_registered('app_logged_users_id') and $app_module_path!='users/login')
		{
			if(CFG_MAINTENANCE_MODE==1 and $app_user['group_id']!=0)
			{
				if(!in_array($app_user['id'],explode(',',CFG_MAINTENANCE_ALLOW_LOGIN_FOR_USERS)))
				{
					$alerts->add(TEXT_ACCESS_FORBIDDEN,'error');	
					redirect_to('users/login&action=logoff');
				}
			}
		}
	}
}