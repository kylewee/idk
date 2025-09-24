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
  case 'save':
      $sql_data = array(
      	'name'=>$_POST['name'],
      	'notes'=>$_POST['notes']
      );
                                                                              
      if(isset($_GET['id']))
      {        
        db_perform('app_global_lists',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");       
      }
      else
      {               
        db_perform('app_global_lists',$sql_data);
      }
      
      redirect_to('global_lists/lists');      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = global_lists::check_before_delete($_GET['id']);
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = global_lists::get_name_by_id($_GET['id']);
                    
          db_delete_row('app_global_lists',$_GET['id']);
          db_delete_row('app_global_lists_choices',$_GET['id'],'lists_id');
          
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
        redirect_to('global_lists/lists');  
      }
    break;    
}