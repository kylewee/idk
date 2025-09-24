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

$app_title = app_set_title(TEXT_USERS_ALERTS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array(
    	'is_active'	=> (isset($_POST['is_active']) ? 1:0),
    	'type'	=> $_POST['type'],
    	'sections_id'	=> (isset($_POST['sections_id']) ? $_POST['sections_id']:0),    	
    	'color'	=> $_POST['color'],
    	'name'	=> $_POST['name'],
    	'icon'	=> $_POST['icon'],
    	'description'	=> $_POST['description'],    	    	
    	'users_groups' => (isset($_POST['users_groups']) ? implode(',',$_POST['users_groups']):''),
    	'users_fields' => (isset($_POST['users_fields']) ? implode(',',$_POST['users_fields']):''),
    	'created_by' => $app_user['id'],
    	'sort_order' => $_POST['sort_order'],
    	
    );
        
    if(isset($_GET['id']))
    {                  
      db_perform('app_dashboard_pages',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");       
    }
    else
    {                     
      db_perform('app_dashboard_pages',$sql_data);                             
    }
        
    redirect_to('dashboard_configure/index');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        db_query("delete from app_dashboard_pages where id='" . _get::int('id') . "'");        
                     
        redirect_to('dashboard_configure/index');  
      }
    break; 

}