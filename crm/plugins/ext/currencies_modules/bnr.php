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

class bnr
{
	public $title;
  
	function __construct()
	{
		$this->title = 'BNR - Romanian National Bank';
	}
  
	static function rate($from, $to)
	{
	  $ch = curl_init('https://bnr.ro/nbrfxrates.xml');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		
		$xml = simplexml_load_string($data);
		$json = json_encode($xml);
		$currencies = json_decode($json,TRUE);
		
		$from_value = false;		
		$to_value = false;
		
		if($from=='RON') $from_value = 1;
		if($to=='RON') $to_value = 1;
		
    $nIndex = 0;
		foreach($xml->Body->Cube->Rate as $currency)
		{
      $multiplication = $xml->Body->Cube->Rate[$nIndex]->attributes()[1] != null ? $xml->Body->Cube->Rate[$nIndex]->attributes()[1] : 1;
      
			if($currency->attributes()[0] == $to) $to_value = (float)$xml->Body->Cube->Rate[$nIndex] / $multiplication;										
			if($currency->attributes()[0] == $from) $from_value = (float)$xml->Body->Cube->Rate[$nIndex] / $multiplication;
      $nIndex++;
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