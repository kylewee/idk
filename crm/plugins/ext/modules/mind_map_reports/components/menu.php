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

$reports_query = db_query("select * from app_ext_mind_map order by name");
while($reports = db_fetch_array($reports_query))
{
	if(mind_map_reports::has_access($reports['users_groups']))
	{
		$check_query = db_query("select id from app_entities_menu where find_in_set('mind_map" . $reports['id']. "',reports_list)");
		if(!$check = db_fetch_array($check_query))
		{
			if($reports['in_menu'])
			{
				$app_plugin_menu['menu'][] = array('title'=>$reports['name'],'url'=>url_for('ext/mind_map_reports/view','id=' . $reports['id']),'class'=>'fa-sitemap');
			}
			elseif($app_entities_cache[$reports['entities_id']]['parent_id']==0)
			{
				$app_plugin_menu['reports'][] = array('title'=>$reports['name'],'url'=>url_for('ext/mind_map_reports/view','id=' . $reports['id']));
			}
		}
	}
}


if(isset($_GET['path']))
{
	$entities_list = items::get_sub_entities_list_by_path($_GET['path']);

	if(count($entities_list))
	{

		$reports_query = db_query("select mm.* from app_ext_mind_map mm, app_entities e where e.id=mm.entities_id and  e.id in (" . implode(',',$entities_list) . ")  order by mm.name");
		
		while($reports = db_fetch_array($reports_query))
		{
			if(mind_map_reports::has_access($reports['users_groups']))
			{
				$path = app_get_path_to_report($reports['entities_id']);
				
				$app_plugin_menu['items_menu_reports'][] = array('title'=>$reports['name'],'url'=>url_for('ext/mind_map_reports/view','id=' . $reports['id'] . '&path=' . $path));
			}
		}
	}
}
