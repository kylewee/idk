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
  case 'sort':
      if(isset($_POST['sort_items']))
      {
        $sort_order = 0;
        foreach(explode(',',$_POST['sort_items']) as $v)
        {
          db_query("update app_entities_menu set sort_order='" . $sort_order . "' where id='" . str_replace('item_','',$v). "'");
          
          $sort_order++;
        }
      }
      exit();
    break;
  case 'sort_items':
    	if(isset($_POST['sort_items']))
    	{
    		db_query("update app_entities_menu set entities_list='" . str_replace('item_','',$_POST['sort_items']) . "' where id='" . db_input($_GET['id']). "'",true);    	
    	}
    	exit();
    	break;
  case 'save':
  	  	
    $sql_data = array(
        'name' => db_prepare_input($_POST['name']),
        'icon' => db_prepare_input($_POST['icon']),
        'icon_color' => db_prepare_input($_POST['icon_color']),
        'bg_color' => db_prepare_input($_POST['bg_color']),
        'entities_list' => (isset($_POST['entities_list']) ? implode(',',$_POST['entities_list']) : ''),
        'reports_list' => (isset($_POST['reports_list']) ? implode(',',$_POST['reports_list']) : ''),
        'pages_list' => (isset($_POST['pages_list']) ? implode(',',$_POST['pages_list']) : ''),
        'sort_order'=>db_prepare_input($_POST['sort_order']),
        'type' => db_prepare_input($_POST['type']),
        'url' => db_prepare_input($_POST['url']),
        'users_groups' => (isset($_POST['users_groups']) ? implode(',',$_POST['users_groups']) : ''),
        'assigned_to' => (isset($_POST['assigned_to']) ? implode(',',$_POST['assigned_to']) : ''),
        'parent_id' => $_POST['parent_id'],
    );
    
    
    if(isset($_GET['id']))
    {        
      db_perform('app_entities_menu',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");       
    }
    else
    {               
      db_perform('app_entities_menu',$sql_data);                  
    }
        
    redirect_to('entities/menu');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {     
      	$obj = db_find('app_entities_menu',$_GET['id']);
                 
        db_delete_row('app_entities_menu',$_GET['id']); 
        
        db_query("update app_entities_menu set parent_id=0 where parent_id='" . _get::int('id') . "'");
                              
        $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');
                        
        redirect_to('entities/menu');  
      }
    break;   
}