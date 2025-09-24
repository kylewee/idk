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
?>
<?php

$is_plugin_dashboard = false;
if(defined('AVAILABLE_PLUGINS'))
{      
  foreach(explode(',',AVAILABLE_PLUGINS) as $plugin)
  {     
            
    //include plugin dashboard
    if(is_file('plugins/' . $plugin .'/includes/dashboard.php'))
    {
      require('plugins/' . $plugin .'/includes/dashboard.php');
      $is_plugin_dashboard = true;
    }      
  }  
}


if(!$is_plugin_dashboard)
{
    require(component_path('dashboard/dashboard_default'));
}


