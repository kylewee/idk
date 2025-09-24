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

//check access
if($app_user['group_id']>0)
{
  redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{
  case 'save':
       $timer_cfg_query = db_query("select * from app_ext_timer_configuration where entities_id='" . db_input($_POST['entities_id']) . "'");
       if($timer_cfg = db_fetch_array($timer_cfg_query))
       {
         $sql_data = array('users_groups' => (is_array($_POST['users_groups']) ? implode(',',$_POST['users_groups']):''));
         
         db_perform('app_ext_timer_configuration',$sql_data,'update',"id='" . db_input($timer_cfg['id']) . "'");
       }
       else
       {
         $sql_data = array('entities_id'  => $_POST['entities_id'],
                           'users_groups' => (is_array($_POST['users_groups']) ? implode(',',$_POST['users_groups']):''));
                           
         db_perform('app_ext_timer_configuration',$sql_data);
       }
       
      exit();
    break;
}