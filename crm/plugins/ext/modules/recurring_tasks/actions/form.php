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

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_ext_recurring_tasks',$_GET['id']);
	
	$obj['repeat_start'] = ($obj['repeat_start']>0 ? date('Y-m-d',$obj['repeat_start']) : '');
	$obj['repeat_end'] = ($obj['repeat_end']>0 ? date('Y-m-d',$obj['repeat_end']) : '');
}
else
{
	$obj = db_show_columns('app_ext_recurring_tasks');

	$obj['is_active'] = 1;
	$obj['repeat_interval'] = 1;
	$obj['repeat_limit'] = 0;
		
	//get default repeat_start date
	$path_info = items::parse_path($_GET['path']);
		
	$current_entity_id = $path_info['entity_id'];
	$current_item_id = $path_info['item_id'];
	
	$item_info = db_find('app_entity_' . $current_entity_id,$current_item_id);
		
	$obj['repeat_start'] = date('Y-m-d',$item_info['date_added']);				
}