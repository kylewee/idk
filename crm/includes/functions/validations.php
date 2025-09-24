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
  
  function app_validate_email($email) 
  {
    $email = trim($email);

    if ( strlen($email) > 255 ) 
    {
      $valid_address = false;
    } 
    elseif ( function_exists('filter_var') && defined('FILTER_VALIDATE_EMAIL') ) 
    {
      $valid_address = (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    } 
    else 
    {
      if ( substr_count( $email, '@' ) > 1 ) 
      {
        $valid_address = false;
      }

      if ( preg_match("/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i", $email) ) 
      {
        $valid_address = true;
      } 
      else 
      {
        $valid_address = false;
      }
    }

    return $valid_address;
  }
