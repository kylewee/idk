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

if(strlen(CFG_API_KEY) and CFG_API_KEY==$_GET['key'])
{

	$sql_data = [
			'type' => 'phone',
			'date_added' => db_prepare_input((int)$_GET['date_added']),		
			'direction' => db_prepare_input($_GET['direction']),
			'phone' => db_prepare_input(preg_replace('/\D/', '', $_GET['phone'])),
			'duration' => db_prepare_input((int)$_GET['duration']),			
	];
	
	db_perform('app_ext_call_history', $sql_data);

}

exit();