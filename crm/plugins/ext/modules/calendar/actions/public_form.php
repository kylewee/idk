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

if(!calendar::user_has_public_full_access())
{
  exit();
}

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_ext_calendar_events',$_GET['id']);
  
  $obj['start_date'] = str_replace(' 00:00','',date('Y-m-d H:i',$obj['start_date']));  
  $obj['end_date'] =  str_replace(' 00:00','',date('Y-m-d H:i',$obj['end_date']));
  
  if($obj['repeat_end']>0)
  {
    $obj['repeat_end'] = date('Y-m-d',$obj['repeat_end']);
  }
  else
  {
    $obj['repeat_end'] = '';
  }
  
}
else
{
  $obj = db_show_columns('app_ext_calendar_events');
  
  $start_date_timestamp = strtotime($_GET['start']);
  $end_date_timestamp = strtotime($_GET['end']);
              
  if($_GET['view_name']=='dayGridMonth')
  {
    $obj['start_date'] = date('Y-m-d',$start_date_timestamp);
    $obj['end_date'] = date('Y-m-d',strtotime('-1 day',$end_date_timestamp));
  }  
  else
  { 
    $obj['start_date'] = date('Y-m-d H:i',$start_date_timestamp);
    $obj['end_date'] = date('Y-m-d H:i',$end_date_timestamp);       
  }  
    
  $obj['bg_color'] = '#3a87ad';
  $obj['repeat_interval'] = 1;
   
}