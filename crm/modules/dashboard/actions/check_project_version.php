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
  
  $ch = curl_init();
  
  curl_setopt($ch, CURLOPT_URL, "https://www.rukovoditel.net/current_version/version.txt");  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 3);  
  $response = curl_exec($ch);
  curl_close($ch);
      
  if(strlen($response)>0 and strlen($response)<10)
  {  	  
  	$app_current_version = $response;
  }
  else
  {      
      $app_current_version = PROJECT_VERSION;
  }    
    
  
  
  