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

//check if report exist
$reports_query = db_query("select * from app_ext_funnelchart where id='" . db_input((int)$_GET['id']) . "'");
if(!$reports = db_fetch_array($reports_query))
{
	redirect_to('dashboard/page_not_found');
}

if(!in_array($app_user['group_id'],explode(',',$reports['users_groups'])) and $app_user['group_id']>0)
{
	redirect_to('dashboard/access_forbidden');
}

app_set_title($reports['name']);


switch($app_module_action)
{
	case 'set_view_mode':
		$funnelchart_type[$reports['id']] = $_GET['view_mode'];

		redirect_to('ext/funnelchart/view','id=' . _get::int('id'). (isset($_GET['path']) ? '&path=' . $app_path:''));
		break;
}		