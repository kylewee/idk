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
  
      $sql_data = array(
      	'name'=>$_POST['name'],                              	      				      								
        'users_groups'=>(isset($_POST['access']) ? json_encode($_POST['access']):''),         
        'in_menu'=>(isset($_POST['in_menu']) ? $_POST['in_menu']:0),
        'users_groups'=>(isset($_POST['users_groups']) ? implode(',',$_POST['users_groups']):''),
        'is_public_access' => $_POST['is_public_access'] ?? 0,
        'zoom'=>$_POST['zoom'],
        'latlng'=>trim(preg_replace('/ +/',',',$_POST['latlng'])),
        'display_legend'=>$_POST['display_legend'],
        'display_sidebar'=>$_POST['display_sidebar'],
        'sidebar_width'=>$_POST['sidebar_width']
      );
                                                                                    
      if(isset($_GET['id']))
      {        
        db_perform('app_ext_pivot_map_reports',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");       
      }
      else
      {                               
        db_perform('app_ext_pivot_map_reports',$sql_data);                    
      }
                                          
      redirect_to('ext/pivot_map_reports/reports');
      
    break;
  case 'delete':
      $obj = db_find('app_ext_pivot_map_reports',$_GET['id']);
      
      db_delete_row('app_ext_pivot_map_reports',$_GET['id']);
      
      $entities_query = db_query("select id from app_ext_pivot_map_reports_entities where reports_id='" . $_GET['id'] . "'");
      while($entities = db_fetch_array($entities_query))
      {
          reports::delete_reports_by_type('pivot_map' . $entities['id']);
      }
      
      db_delete_row('app_ext_pivot_map_reports_entities',$_GET['id'],'reports_id');
                                     
      $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');
      
      redirect_to('ext/pivot_map_reports/reports');
    break;      
   
}