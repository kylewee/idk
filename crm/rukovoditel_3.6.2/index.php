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

require('includes/application_top.php');

//include available plugins
require('includes/plugins.php');

//include overall action for whole module        
if(is_file($path = $app_plugin_path . 'modules/' . $app_module . '/module_top.php'))
{
    require($path);
}

//include plugins menu  
require('includes/plugins_menu.php');

//include module action      
if(is_file($path = $app_plugin_path . 'modules/' . $app_module . '/actions/' . $app_action . '.php'))
{
    require($path);
}

if(IS_AJAX)
{
    if(is_file($path = $app_plugin_path . 'modules/' . $app_module . '/views/' . $app_action . '.php'))
    {
        require($path);
    }
}
else
{
    if(substr($app_layout, 0, 8) === 'plugins/')
    {
        require($app_layout);
    }
    else
    {
        require('template/' . $app_layout);
    }
}

require('includes/application_bottom.php');
