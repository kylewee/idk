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

if($app_module_action=='change_skin')
{
  $skin = $_GET['set_skin'];
    
  if(is_file('css/skins/' . $skin . '/' . $skin . '.css'))
  {
    db_query("update app_entity_1 set field_14='" . db_input($skin). "' where id='" . $app_logged_users_id . "'");
    
    setcookie('user_skin', $skin, time()+ (365 * 24 * 3600), $_SERVER['HTTP_HOST'], '', (is_ssl() ? 1 : 0));
        
    redirect_to('dashboard/');
  }
}

