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

class holidays
{
	static function render_js_holidays()
	{
		$html = 'var holidays = new Array();' . "\n";
		$holidays_query = db_query("select * from app_holidays where year(start_date) in (" . date('Y') . "," . (date('Y')-1) . "," . (date('Y')+1). ") order by start_date desc");
		while($holidays = db_fetch_array($holidays_query))
		{
				for($i=get_date_timestamp($holidays['start_date']); $i<=get_date_timestamp($holidays['end_date']); $i+=86400)
				{
					$html .= 'holidays["' .  date('Y-m-d',$i) . '"] = "' . htmlspecialchars($holidays['name']) . '";'  . "\n";
				}
		}
		
		return $html;
	}
	
	static function get_year_choices()
	{
		$choices = [];
		$holidays_query = db_query("select min(year(start_date)) as min_year, max(year(end_date)) max_year from app_holidays");
		$holidays = db_fetch_array($holidays_query);
		
		if($holidays['min_year']>0)
		{
			for($i=$holidays['min_year'];$i<=($holidays['max_year']+1);$i++)
			{
				$choices[$i] = $i;
			}				
		}	
		
		return $choices;
	}
}