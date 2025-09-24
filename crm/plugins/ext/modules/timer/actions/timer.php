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

switch($app_module_action)
{
  case 'create_timer':
        $timer_query = db_query("select * from app_ext_timer where entities_id='" . db_input($_POST['entities_id']) . "' and items_id='" . db_input($_POST['items_id']) . "' and users_id='" . db_input($app_user['id']) . "'");
        if(!$timer = db_fetch_array($timer_query))
        {
          $sql_data = array('seconds'=>0,
                            'entities_id'=>$_POST['entities_id'],
                            'items_id'=>$_POST['items_id'],
                            'users_id'=>$app_user['id'],                                                                                                   
                            );
                      
          db_perform('app_ext_timer',$sql_data);
        }
        
        echo timer::render_header_dropdown_menu();
        
      exit();
    break;
  case 'set_timer':
        $timer_query = db_query("select * from app_ext_timer where entities_id='" . db_input($_POST['entities_id']) . "' and items_id='" . db_input($_POST['items_id']) . "' and users_id='" . db_input($app_user['id']) . "'");
        if(!$timer = db_fetch_array($timer_query))
        {
          $sql_data = array('seconds'=>$_POST['seconds'],
                            'entities_id'=>$_POST['entities_id'],
                            'items_id'=>$_POST['items_id'],
                            'users_id'=>$app_user['id'],                                                                                                   
                            );
                      
          db_perform('app_ext_timer',$sql_data);
        }
        else
        {
          db_query("update app_ext_timer set seconds='" . db_input($_POST['seconds']) . "' where id='" . db_input($timer['id']) . "'");
        }
                        
      exit();
    break;
  case 'delete_timer':
        $timer_query = db_query("select * from app_ext_timer where entities_id='" . db_input($_POST['entities_id']) . "' and items_id='" . db_input($_POST['items_id']) . "' and users_id='" . db_input($app_user['id']) . "'");
        if($timer = db_fetch_array($timer_query))
        {
          db_delete_row('app_ext_timer',$timer['id']);
        }
        
        echo timer::render_header_dropdown_menu();
  
      exit();
    break;
}