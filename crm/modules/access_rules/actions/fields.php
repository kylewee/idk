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

switch($app_module_action)
{
	case 'save':
				
		$sql_data = array(
			'entities_id'=>$_GET['entities_id'],
			'fields_id'=>$_POST['fields_id'],		
		);
	
		if(isset($_GET['id']))
		{
			$access_rules_fields_info = db_find('app_access_rules_fields',$_GET['id']);
			if($access_rules_fields_info['fields_id']!=$_POST['fields_id'])
			{
				db_delete_row('app_access_rules',$_GET['entities_id'],'entities_id');
			}
			
			db_perform('app_access_rules_fields',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_access_rules_fields',$sql_data);			
		}
	
		redirect_to('access_rules/fields','entities_id=' . $_GET['entities_id']);
		break;
	
	case 'delete':
		
		if(isset($_GET['id']))
		{
			db_delete_row('app_access_rules_fields',$_GET['id']);
			db_delete_row('app_access_rules',$_GET['entities_id'],'entities_id');			
		}
		
		redirect_to('access_rules/fields','entities_id=' . $_GET['entities_id']);
		break;

}