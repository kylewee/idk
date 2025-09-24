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

$reports_query = db_query("select * from app_ext_track_changes where is_active=1 and (find_in_set('" . $app_user['group_id']. "',users_groups) or find_in_set('" .  $app_user['id'] . "',assigned_to))");
while($reports = db_fetch_array($reports_query))
{
	foreach(explode(',',$reports['position']??'') as $position)
	{
		switch($position)
		{
			case 'in_menu':
				$app_plugin_menu['menu'][] = array('title'=>$reports['name'],'url'=>url_for('ext/track_changes/view','reports_id=' . $reports['id']),'class'=>$reports['menu_icon']);
				break;
			case 'in_reports_menu':
				$app_plugin_menu['reports'][] = array('title'=>$reports['name'],'url'=>url_for('ext/track_changes/view','reports_id=' . $reports['id']));
				break;			
		}		
	}	
}


