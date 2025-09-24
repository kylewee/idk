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

if(CFG_INCOMING_CALL_ENTITY==0) exit();
if(!strlen(CFG_INCOMING_CALL_FIELD)) exit();

$entity_info_query = db_query("select id from app_entities where id='" . (int)CFG_INCOMING_CALL_ENTITY . "'");
if($entity_info = db_fetch_array($entity_info_query))
{	
	$available_fields = [];
	
	$fields_query = db_query("select id from app_fields where id in (" . CFG_INCOMING_CALL_FIELD . ") and entities_id='" . $entity_info['id'] . "'");
	while($fields = db_fetch_array($fields_query))
	{
		$available_fields[] = $fields['id'];
	}
	
	if(count($available_fields))
	{
		$phone = preg_replace('/\D/', '', $_GET['phone']);
		$where_sql = [];
		foreach($available_fields as $field_id)
		{
			$where_sql[] = "rukovoditel_regex_replace('[^0-9]','',e.field_" . $field_id . ") like '%" . db_input($phone) . "%'";
		}
		
		$item_info_query = db_query("select e.id from app_entity_" . $entity_info['id'] . " e where " . implode(' or ',$where_sql) . " limit 1",false);
		if($item_info = db_fetch_array($item_info_query))
		{
			$alerts->add(TEXT_EXT_INCOMING_CALL . '. ' . TEXT_PHONE . ': <b>' . $phone . '</b>','success');
			redirect_to('items/info','path=' . $entity_info['id'] . '-' . $item_info['id']);
		}
		else
		{
			$alerts->add(sprintf(TEXT_EXT_INCOMING_CALL . '. ' . TEXT_EXT_RECORD_WITH_PHONE_NOT_FOUND, $phone),'warning');
			$parents = entities::get_parents($entity_info['id']);
			
			if(count($parents))
			{
				redirect_to('items/items','path=' . $parents[array_key_last($parents)]);
			}
			else
			{
				redirect_to('items/items','path=' . $entity_info['id']);
			}
			
		}
		
	}
}

exit();