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

class tcmb
{
	public $title;

	function __construct()
	{
		$this->title = 'TCMB - Türkiye Cumhuriyet Merkez Bankası';
	}

	static function rate($from, $to)
	{
		$kur = simplexml_load_file("https://www.tcmb.gov.tr/kurlar/today.xml");
		
		$from_value = false;		
		$to_value = false;	
		
		if($from=='TRY') $from_value = 1;
		if($to=='TRY') $to_value = 1;
		
		foreach ($kur -> Currency as $cur) 
		{
			if ($cur["Kod"] == $from) {
				$from_value = $cur -> ForexBuying;
			}

			if ($cur["Kod"] == $to) {
				$to_value  = $cur -> ForexSelling;
			}
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