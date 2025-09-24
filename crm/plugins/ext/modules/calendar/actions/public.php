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

if(!calendar::user_has_public_access())
{
  redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{
  case 'resize':
      $end = ($_POST['view_name']=='dayGridMonth' ? strtotime('-1 day',strtotime($_POST['end'])) : strtotime($_POST['end']));
                    
      $sql_data = array('end_date'=>$end);
                 
      db_perform('app_ext_calendar_events',$sql_data,'update',"id='" . db_input($_POST['id']) . "'");
                                
      exit();
    break;
  case 'drop':
  
      if(isset($_POST['end']))
      {
        $end = ($_POST['view_name']=='dayGridMonth' ? strtotime('-1 day',strtotime($_POST['end'])) : strtotime($_POST['end']));
        
        $sql_data = array('start_date'=>strtotime($_POST['start']),
                          'end_date'=>$end);
                          
        db_perform('app_ext_calendar_events',$sql_data,'update',"id='" . db_input($_POST['id']) . "'");
      }
      else
      {
        $sql_data = array('start_date'=>strtotime($_POST['start']),
                          'end_date'=>strtotime($_POST['start']));
                          
        db_perform('app_ext_calendar_events',$sql_data,'update',"id='" . db_input($_POST['id']) . "'");
      }
                                
      exit();
    break;  
  case 'delete':
      db_query("delete from app_ext_calendar_events where id='" . $_POST['id'] . "'");
      exit(); 
    break;
  case 'save':
  
      $start_date = get_date_timestamp($_POST['start_date']);
      $end_date = get_date_timestamp($_POST['end_date']);
      
      if($start_date>$end_date)
      {
        $end_date = $start_date; 
      }
      
      if($start_date==$end_date and strstr($_POST['end_date'],':'))
      {
        $end_date = strtotime("+30 minute",$end_date);
      }
      
      $repeat_interval = (int)$_POST['repeat_interval'];
      $repeat_interval = ($repeat_interval>0 ? $repeat_interval : 1);
                
      $sql_data = array('name'=>db_prepare_input($_POST['name']),                        
                        'description'=>db_prepare_input($_POST['description']),
                        'start_date'=>$start_date,                        
                        'end_date'=>$end_date,
                        'event_type'=>'public',
                        'users_id'=>$app_user['id'],
                        'bg_color' => $_POST['bg_color'],                                                                                                                    
                        'repeat_type' => $_POST['repeat_type'],
                        'repeat_interval' => $repeat_interval,
                        'repeat_days' => (isset($_POST['repeat_days']) ? implode(',',$_POST['repeat_days']) : ''),
                        'repeat_end' => (isset($_POST['repeat_end']) ? get_date_timestamp($_POST['repeat_end']) : ''),
                        'repeat_limit' => $_POST['repeat_limit'],
                        );
                                                                                    
      if(isset($_GET['id']))
      {                      
        db_perform('app_ext_calendar_events',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");              
      }
      else
      {                               
        db_perform('app_ext_calendar_events',$sql_data);                            
      }
      exit();
    break;
  case 'get_events':
        
      $list = array();        
            
      $has_public_full_access = calendar::user_has_public_full_access();
      
      $start = date('Y-m-d',strtotime($_POST['start']));
      $end = date('Y-m-d',strtotime($_POST['end']));
      
      foreach(calendar::get_events($start,$end,'public') as $events)
      {
        $start = date('Y-m-d H:i',$events['start_date']);
        $end = date('Y-m-d H:i',$events['end_date']);
                        
        if($events['is_public']==1 or !$has_public_full_access or $events['users_id']!=$app_user['id'])
        {
          $editable = false;
          $url = '';
        }
        else
        {
          $editable = true;
          $url = url_for('ext/calendar/public_form','id=' . $events['id']);
        } 
        
        if(strstr($end,' 00:00'))
        {
          $end = date('Y-m-d H:i',strtotime('+1 day',$events['end_date']));
        } 
        
        $created_by = '';
        
        if(isset($app_users_cache[$events['users_id']]))
        {
          $created_by = '<div class="calendar-created-by">' . TEXT_CREATED_BY . ': ' . $app_users_cache[$events['users_id']]['name'] . '</div>';
        }
         
        $list[] = array('id' => $events['id'],
                      'title' => addslashes($events['name']),
                      'description' => str_replace(array("\n\r","\n","\r"),'<br>',$events['description']) . $created_by,
                      'start' => str_replace(' 00:00','',$start),
                      'end' => str_replace(' 00:00','',$end),
                      'backgroundColor'=> $events['bg_color'],
                      'borderColor' => $events['bg_color'],
                      'editable'=>$editable,                   
                      'allDay'=>(strstr($start,'00:00') and strstr($end,'00:00')),
                      'url' => $url,                      
                      );      
      }
            
      echo json_encode($list);
          
      exit();
    break;
}