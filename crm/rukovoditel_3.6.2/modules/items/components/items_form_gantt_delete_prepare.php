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

if(strstr($app_redirect_to,'ganttreport'))
{
	$check_query = db_query("select id from app_entities where parent_id='" . $current_entity_id . "'");
	$check = db_fetch_array($check_query);
	
	if(users::has_access('delete') and !$check)
	{
		$extra_button = '<button id="gantt_delete_item_btn" type="button" class="btn btn-default" onclick="gantt_delete()"><i class="fa fa-trash-o"></i></button>';
	}
}