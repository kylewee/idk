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

class configuration
{
	static function set($k,$value)
	{
		$cfq_query = db_query("select * from app_configuration where configuration_name='" . $k . "'");
		if(!$cfq = db_fetch_array($cfq_query))
		{
			db_perform('app_configuration',array('configuration_value'=>$value,'configuration_name'=>$k));
		}
		else
		{
			db_perform('app_configuration',array('configuration_value'=>$value),'update',"configuration_name='" . $k . "'");
		}
	}	
}