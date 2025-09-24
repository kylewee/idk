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

class entities_cfg
{
  public $cfg;
  
  public $entities_id;
  
  function __construct($entities_id)
  {
    
    $this->entities_id = $entities_id;
    
    $info_query = db_fetch_all('app_entities_configuration',"entities_id='" . db_input($this->entities_id). "'");
    while($info = db_fetch_array($info_query))
    {      
      $this->cfg[$info['configuration_name']] = $info['configuration_value'];
    }        
  }
  
  function get($key,$default = '')
  {        
    if(isset($this->cfg[$key]))
    {
      return $this->cfg[$key];
    }
    else
    {
      return $default;
    }
  }
  
  function set($key,$value)
  {
    global $app_user;
    
    if(strlen($key)>0)
    {
    	$value = (is_array($value) ? implode(',',$value) : $value);
    	
      $cfq_query = db_query("select * from app_entities_configuration where configuration_name='" . db_input($key) . "' and entities_id='" . db_input($this->entities_id) . "'");
      if(!$cfq = db_fetch_array($cfq_query))
      {
        db_perform('app_entities_configuration',array('configuration_value'=>$value,'configuration_name'=>$key,'entities_id'=>$this->entities_id));
      }
      else
      {
        db_perform('app_entities_configuration',array('configuration_value'=>$value),'update',"configuration_name='" . db_input($key) . "' and entities_id='" . db_input($this->entities_id) . "'");
      }
    } 
  }
}
