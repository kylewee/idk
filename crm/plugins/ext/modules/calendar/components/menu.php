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
/**
 * add personal calendar to user menu
 */
if(calendar::user_has_personal_access())
{
    $app_plugin_menu['account_menu'][] = array('title' => TEXT_EXT_MY_СALENDAR, 'url' => url_for('ext/calendar/personal'), 'class' => 'fa-calendar');
}


/**
 * add personal calendar to main menu
 */
if(calendar::user_has_public_access())
{
    $events = calendar::get_events(date('Y-m-d'), date('Y-m-d'), 'public');

    if(($events_count = count($events)) > 0)
    {
        $app_plugin_menu['menu'][] = array('title' => TEXT_EXT_СALENDAR, 'url' => url_for('ext/calendar/public'), 'class' => 'fa-calendar', 'badge' => 'badge-info', 'badge_content' => $events_count);
    }
    else
    {
        $app_plugin_menu['menu'][] = array('title' => TEXT_EXT_СALENDAR, 'url' => url_for('ext/calendar/public'), 'class' => 'fa-calendar');
    }
}


/**
 * add calendar reports to main menu
 */
if($app_user['group_id'] > 0)
{
    $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e, app_ext_calendar_access ca where e.id=c.entities_id and (e.parent_id=0 or c.in_menu=1) and c.id=ca.calendar_id and ca.access_groups_id='" . db_input($app_user['group_id']) . "' order by c.name");
}
else
{
    $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e where e.id=c.entities_id and (e.parent_id=0 or c.in_menu=1) order by c.name");
}

while($reports = db_fetch_array($reports_query))
{
    $check_query = db_query("select id from app_entities_menu where find_in_set('calendarreport" . $reports['id'] . "',reports_list)");
    if(!$check = db_fetch_array($check_query))
    {
        $app_plugin_menu['reports'][] = array('title' => $reports['name'], 'url' => url_for('ext/calendar/report', 'id=' . $reports['id']));
    }
}

/**
 * add calendar reports to items menu
 */
if(isset($_GET['path']))
{
    $entities_list = items::get_sub_entities_list_by_path($_GET['path']);

    if(count($entities_list))
    {
        if($app_user['group_id'] > 0)
        {
            $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e, app_ext_calendar_access ca where e.id=c.entities_id and e.id in (" . implode(',', $entities_list) . ")  and c.id=ca.calendar_id and ca.access_groups_id='" . db_input($app_user['group_id']) . "' order by c.name");
        }
        else
        {
            $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e where e.id=c.entities_id and  e.id in (" . implode(',', $entities_list) . ")  order by c.name");
        }

        while($reports = db_fetch_array($reports_query))
        {
            $path = app_get_path_to_report($reports['entities_id']);

            $app_plugin_menu['items_menu_reports'][] = array('title' => $reports['name'], 'url' => url_for('ext/calendar/report', 'id=' . $reports['id'] . '&path=' . $path));
        }
    }
}