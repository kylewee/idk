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

$calendar_reports_id = str_replace('calendarreport','',$app_redirect_to);
$calendar_reports_query = db_query("select * from app_ext_calendar where id='" . db_input($calendar_reports_id) . "'");
if($calendar_reports = db_fetch_array($calendar_reports_query))
{
  $start_date_timestamp = strtotime($_GET['start']);
  $end_date_timestamp = strtotime($_GET['end']);
              
  if($_GET['view_name']=='dayGridMonth')  
  {
    $obj['field_' . $calendar_reports['start_date']] = $start_date_timestamp;
    $obj['field_' . $calendar_reports['end_date']] = strtotime('-1 day',$end_date_timestamp);
  }  
  else
  { 
    $obj['field_' . $calendar_reports['start_date']] = $start_date_timestamp;
    $obj['field_' . $calendar_reports['end_date']] = $end_date_timestamp;       
  }    
}