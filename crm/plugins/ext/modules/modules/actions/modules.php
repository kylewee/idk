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

if(!isset($_GET['type']))
{
	redirect_to('ext/modules/modules','type=payment');
}

$modules = new modules($_GET['type']);

switch($app_module_action)
{
	case 'save':
		if(isset($_GET['id']))
		{
			$sql_data = array(
					'is_active' => $_POST['is_active']??0,					
			);
			
			db_perform('app_ext_modules', $sql_data,'update',"id='" . _get::int('id') . "'");
			
			
			//reset configuration
			db_query("delete from app_ext_modules_cfg where modules_id='" ._get::int('id') . "'");
			
			$sql_data = array();
			
			foreach($_POST['cfg'] as $key=>$value)
			{
				$sql_data[] = array(
						'modules_id' => _get::int('id'),
						'cfg_key' => $key,
						'cfg_value' => (is_array($value) ? implode(',',$value): $value),
				);
			}
			
			db_batch_insert('app_ext_modules_cfg', $sql_data);
		}
		
		redirect_to('ext/modules/modules','type=' . $_GET['type']);
		break;
	case 'install':
		if(isset($_POST['module']))
		{
			$modules->install($_POST['module']);						
		}
		
		redirect_to('ext/modules/modules','type=' . $_GET['type']);
		break;
	case 'delete':
		if(isset($_GET['id']))
		{					
			$obj = db_find('app_ext_modules',$_GET['id']);
			$module = new $obj['module'];
			$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$module->title),'success');
			
			db_query("delete from app_ext_modules where id='" . db_input($_GET['id']) . "'");
			db_query("delete from app_ext_modules_cfg where modules_id='" . db_input($_GET['id']) . "'");
			
		}
		redirect_to('ext/modules/modules','type=' . $_GET['type']);
		break;
}