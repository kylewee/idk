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

$reports_query = db_query("select * from app_ext_item_pivot_tables where id='" . _get::int('reports_id') . "'");
if(!$reports = db_fetch_array($reports_query))
{
	redirect_to('ext/item_pivot_tables/reports');
}

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_ext_item_pivot_tables_calcs',$_GET['id']);
}
else
{
	$obj = db_show_columns('app_ext_item_pivot_tables_calcs');
}
