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

class import_templates
{
	static function get_choices($entities_id)
	{
		global $app_user;
		
		$choices = [];
		$choices[] = '';
		$templates_query = db_query("select * from app_ext_import_templates where entities_id=" . (int)$entities_id . " and find_in_set(" . $app_user['group_id'] . ",users_groups) order by sort_order, name");
		while($templates = db_fetch_array($templates_query))
		{
			$choices[$templates['id']] = $templates['name'];
		}
		
		return $choices;
	}	
}