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

if(!isset($_GET['path']))
{
	redirect_to('dashboard/');
}

$path_info = items::parse_path($_GET['path']);

$current_path = $_GET['path'];
$current_entity_id = $path_info['entity_id'];
$current_item_id = $path_info['item_id'];
$current_path_array = $path_info['path_array'];

$access_schema = users::get_entities_access_schema($current_entity_id,$app_user['group_id']);

if(!users::has_access('repeat',$access_schema))
{
	redirect_to('dashboard/access_forbidden');
}

//checking view access
if(!users::has_access('view',$access_schema) and  !users::has_access('view_assigned',$access_schema))
{
	redirect_to('dashboard/access_forbidden');
}

//check assigned access
if(users::has_access('view_assigned',$access_schema) and $app_user['group_id']>0 and $current_item_id>0)
{
	if(!users::has_access_to_assigned_item($current_entity_id,$current_item_id))
	{
		redirect_to('dashboard/access_forbidden');
	}
}