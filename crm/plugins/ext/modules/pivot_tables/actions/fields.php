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
$reports_query = db_query("select * from app_ext_pivot_tables where id='" . db_input($_GET['id']) . "'");
if(!$reports = db_fetch_array($reports_query))
{
	redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{
	case 'save':
		if(isset($_POST['fields']))
		{
			db_query("delete from app_ext_pivot_tables_fields where reports_id='" . db_input($_GET['id']) . "'");
			
			foreach($_POST['fields'] as $entities_id=>$entities_fields)
			{
				foreach($entities_fields as $fields_id)
				{
					$sql_data = array(
							'reports_id'=>$_GET['id'],
							'entities_id'=>$entities_id,
							'fields_id'=>$fields_id,
							'fields_name' =>$_POST['fields_name'][$entities_id][$fields_id],							
							'cfg_date_format' =>(isset($_POST['fields_date_format'][$entities_id][$fields_id]) ? $_POST['fields_date_format'][$entities_id][$fields_id] : ''),
					);
					
					db_perform('app_ext_pivot_tables_fields',$sql_data);
				}
			}			
		}
		else
		{
			db_query("delete from app_ext_pivot_tables_fields where reports_id='" . db_input($_GET['id']) . "'");
		}
		
		$alerts->add(TEXT_DATA_SAVED);
							
		redirect_to('ext/pivot_tables/fields','id=' . $_GET['id']);

		break;

}
