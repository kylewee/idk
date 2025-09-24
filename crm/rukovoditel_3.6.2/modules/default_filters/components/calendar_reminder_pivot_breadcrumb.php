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


$pivot_calendar_report_info_query = db_query("select e.*,c.name from app_ext_pivot_calendars_entities e left join app_ext_pivot_calendars c on e.calendars_id =c.id where e.id=" . str_replace('calendar_reminder_pivot','',$app_redirect_to));
$pivot_calendar_report_info = db_fetch_array($pivot_calendar_report_info_query);

$breadcrumb = array();

$breadcrumb[] = '<li>' . link_to(TEXT_EXT_CALENDAR_REPORT,url_for('ext/pivot_calendars/reports')) . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . link_to($pivot_calendar_report_info['name'],url_for('ext/pivot_calendars/entities','calendars_id=' . $pivot_calendar_report_info['calendars_id'])) . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . $entity_info['name'] . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . TEXT_FILTERS . '</li>';

