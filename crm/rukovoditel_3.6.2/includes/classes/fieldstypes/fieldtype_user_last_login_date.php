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

class fieldtype_user_last_login_date
{
	public $options;
	
	function __construct()
	{
		$this->options = array('name' => TEXT_FIELDTYPE_USER_LAST_LOGIN_DATE,'title' => TEXT_FIELDTYPE_USER_LAST_LOGIN_DATE);
	}
		
	function output($options)
	{
		global $app_user;
		
		if(strlen($options['value'])>0 and $options['value']!=0)
		{
			if($app_user['group_id']==0)
			{
				return '<a href="' . url_for('tools/users_login_log','users_id=' . $options['item']['id']). '" target="_new">' . format_date_time($options['value']) . '</a>';
			}
			else
			{
				return format_date_time($options['value']);
			}
		}
		else
		{
			return '';
		}
	}
}