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

$reports_query = db_query("select * from app_ext_item_pivot_tables where id='" . $_GET['reports_id'] . "' and find_in_set(" . $app_user['group_id']. ",allowed_groups)");
if($reports = db_fetch_array($reports_query))
{	
	$item_pivot_tables = new item_pivot_tables($current_entity_id,'');
	$item_pivot_tables->reports = $reports;
	$item_pivot_tables->number_of_rows_per_page = $reports['rows_per_page'];
	$item_pivot_tables->current_page_number = (isset($_GET['page']) ? _get::int('page') : 1);
	echo $item_pivot_tables->render_report();
}