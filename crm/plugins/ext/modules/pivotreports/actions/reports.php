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

//check access
if($app_user['group_id']>0)
{
	redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{
	case 'save':

		$sql_data = array('name'=>db_prepare_input($_POST['name']),
				'entities_id'=>$_POST['entities_id'],
				'cfg_numer_format'=>$_POST['cfg_numer_format'],				
				'allowed_groups'=>(isset($_POST['allowed_groups']) ? implode(',',$_POST['allowed_groups']):''),
				'allow_edit'=>(isset($_POST['allow_edit']) ? $_POST['allow_edit']:0),
				'sort_order'=>$_POST['sort_order'],				
		);


		if(isset($_GET['id']))
		{
			//check if entity changed
			$pivotreports = db_find('app_ext_pivotreports',$_GET['id']);
			if($pivotreports['entities_id']!=$_POST['entities_id'])
			{
				db_delete_row('app_ext_pivotreports_fields',$_GET['id'],'pivotreports_id');
				$sql_data['reports_settings'] = '';
			}
			
			db_perform('app_ext_pivotreports',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
		}
		else
		{
			db_perform('app_ext_pivotreports',$sql_data);
		}

		redirect_to('ext/pivotreports/reports');

		break;
	case 'delete':
		$obj = db_find('app_ext_pivotreports',$_GET['id']);

		db_delete_row('app_ext_pivotreports',$_GET['id']);
		db_delete_row('app_ext_pivotreports_fields',$_GET['id'],'pivotreports_id');
		db_delete_row('app_ext_pivotreports_settings',$_GET['id'],'reports_id');

		$report_info_query = db_query("select * from app_reports where reports_type='pivotreports" . db_input($_GET['id']). "'");
		if($report_info = db_fetch_array($report_info_query))
		{
			reports::delete_reports_by_id($report_info['id']);
		}

		$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');

		redirect_to('ext/pivotreports/reports');
		break;
}
