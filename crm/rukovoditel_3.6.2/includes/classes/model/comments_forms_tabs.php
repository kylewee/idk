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

class comments_forms_tabs
{
	public static function get_name_by_id($id)
	{
		$obj = db_find('app_comments_forms_tabs',$id);

		return $obj['name'];
	}

	public static function check_before_delete($forms_tabs_id)
	{
		$msg = '';

		if(db_count('app_fields',$forms_tabs_id,'comments_forms_tabs_id')>0)
		{
			$msg = sprintf(TEXT_WARN_DELETE_FROM_TAB,forms_tabs::get_name_by_id($forms_tabs_id));
		}

		return $msg;
	}

	public static function get_last_sort_number($entities_id)
	{
		$v = db_fetch_array(db_query("select max(sort_order) as max_sort_order from app_comments_forms_tabs where entities_id = '" . db_input($entities_id) . "'"));

		return $v['max_sort_order'];
	}	 
}