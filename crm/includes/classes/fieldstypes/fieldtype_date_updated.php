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

class fieldtype_date_updated
{
	public $options;

	function __construct()
	{
		$this->options = array('name'=>TEXT_FIELDTYPE_DATE_UPDATED_TITLE, 'title'=>TEXT_FIELDTYPE_DATE_UPDATED_TITLE);
	}

	function output($options)
	{
		return ($options['value']>0 ? format_date_time($options['value']) : '');
	}

	function reports_query($options)
	{
		$filters = $options['filters'];
		$sql_query = $options['sql_query'];

		$sql = reports::prepare_dates_sql_filters($filters,$options['prefix']);

		if(count($sql)>0)
		{
			$sql_query[] =  implode(' and ', $sql);
		}

		return $sql_query;
	}
}