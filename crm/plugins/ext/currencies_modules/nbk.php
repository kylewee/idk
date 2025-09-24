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

class nbk
{
	public $title;

	function __construct()
	{
		$this->title = TEXT_EXT_CURRENCY_MODULE_NBK_TITLE;
	}

	static function rate($from, $to)
	{
		$ch = curl_init('http://www.nationalbank.kz/rss/rates_all.xml?switch=kazakh');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
				
		$xml = simplexml_load_string($data);
		$json = json_encode($xml);
		$currencies = json_decode($json,TRUE);
		
		$from_value = false;		
		$to_value = false;
		$to_quant = false;
		
		
		if($from=='KZT') $from_value = 1;
		if($to=='KZT') $to_value = 1;
		
		foreach($currencies['channel']['item'] as $currency)
		{
			if($currency['title']==$to)
			{
				$to_value = $currency['description'];
				$to_quant = $currency['quant'];
			}
			
			if($currency['title']==$from) $from_value = $currency['description'];
		}

		

		if ($from_value and $to_value)
		{
			return ($from_value/($to_value/$to_quant));
		}
		else
		{
			return false;
		}
			
	}
}