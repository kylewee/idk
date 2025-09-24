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

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_ext_import_templates',$_GET['id']);
}
else
{
	$obj = db_show_columns('app_ext_import_templates');

	if($import_templates_filter>0)
	{
		$obj['entities_id'] = $import_templates_filter;
	}

	$obj['is_active'] = 1;
        $obj['start_import_line']=1;
        $obj['text_delimiter'] = ',';
}