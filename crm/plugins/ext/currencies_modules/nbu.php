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

class nbu
{
	public $title;

	function __construct()
	{
		$this->title = TEXT_EXT_CURRENCY_MODULE_NBU_TITLE;
	}

	static function rate($from, $to)
	{	                    
		$ch = curl_init('https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		$currencies = json_decode($data, true);
						
		$from_value = false;
		$to_value = false;
		
		if($from=='UAH') $from_value = 1;
		if($to=='UAH') $to_value = 1;
				
		foreach($currencies as $currency)
		{			
			if($currency['cc']==$to) $to_value = $currency['rate']; 
			if($currency['cc']==$from) $from_value = $currency['rate'];
		}
											
		if ($from_value and $to_value)
		{
			return ($from_value/$to_value);
		}
		else
		{
			return false;
		}
			
	}
}