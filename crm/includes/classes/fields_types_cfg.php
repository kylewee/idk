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

class fields_types_cfg
{
  public $cfg;
  
  function __construct($configuration)
  {
    if(isset($configuration) and strlen($configuration)>0)
    {
      $this->cfg = json_decode($configuration,true);
    }
    else
    {
      $this->cfg = array();
    }
  }
  
  function has($key)
  {
    if(isset($this->cfg[$key]))
    {
      return true;
    }
    else
    {
      return false;
    }
  }
  
  function get($key, $defautl = '')
  {
    if(isset($this->cfg[$key]))
    {
      return $this->cfg[$key];
    }
    else
    {
      return $defautl;
    }
  }
}


class fields_types_options_cfg
{
	public $cfg;

	function __construct($options)
	{
		$this->cfg = $options;
	}

	function has($key)
	{
		if(isset($this->cfg[$key]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function get($key, $defautl = '')
	{
		if(isset($this->cfg[$key]))
		{
			return $this->cfg[$key];
		}
		else
		{
			return $defautl;
		}
	}
}