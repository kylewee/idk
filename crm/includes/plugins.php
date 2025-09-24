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

//to include menus from plugins
  $app_plugin_menu = array();
  
//include available plugins  
  if(defined('AVAILABLE_PLUGINS'))
  {  	  	
    foreach(explode(',',AVAILABLE_PLUGINS) as $plugin)
    {            
      //include language file                  
      if(is_file($v = 'plugins/' . $plugin .'/languages/' . ((isset($app_user['language']) and strlen($app_user['language'])) ? $app_user['language'] : CFG_APP_LANGUAGE)))
      {          
        require($v);
      }
      elseif(is_file($v = 'plugins/' . $plugin .'/languages/english.php'))
      {
          require($v);
      }
          
                   
      //include plugin
      if(is_file('plugins/' . $plugin .'/application_top.php'))
      {
        require('plugins/' . $plugin .'/application_top.php');        
      }      
    }
  }
  	