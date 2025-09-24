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
 * add gantt reports to menu
 */
if($app_user['group_id'] > 0)
{
    $reports_query = db_query("select g.* from app_ext_ganttchart g, app_entities e, app_ext_ganttchart_access ga where g.is_active=1 and e.id=g.entities_id and g.id=ga.ganttchart_id and ga.access_groups_id='" . db_input($app_user['group_id']) . "' order by name");
}
else
{
    $reports_query = db_query("select g.* from app_ext_ganttchart g, app_entities e where g.is_active=1 and e.id=g.entities_id order by g.name");
}

while($reports = db_fetch_array($reports_query))
{
    $check_query = db_query("select id from app_entities_menu where find_in_set('ganttreport" . $reports['id'] . "',reports_list)");
    if(!$check = db_fetch_array($check_query))
    {
        $app_plugin_menu['reports'][] = array('title' => $reports['name'], 'url' => url_for('ext/ganttchart/dhtmlx', 'id=' . $reports['id']));
    }
}


/**
 * add gantt reports to items menu
 */
if(isset($_GET['path']))
{
    $entities_list = items::get_sub_entities_list_by_path($_GET['path']);

    if(count($entities_list) > 0)
    {
        if($app_user['group_id'] > 0)
        {
            $reports_query = db_query("select g.* from app_ext_ganttchart g, app_entities e, app_ext_ganttchart_access ga where g.is_active=1 and e.id=g.entities_id and e.id in (" . implode(',', $entities_list) . ") and g.id=ga.ganttchart_id and ga.access_groups_id='" . db_input($app_user['group_id']) . "' order by name");
        }
        else
        {
            $reports_query = db_query("select g.* from app_ext_ganttchart g, app_entities e where g.is_active=1 and e.id=g.entities_id and e.id in (" . implode(',', $entities_list) . ") order by g.name");
        }

        while($reports = db_fetch_array($reports_query))
        {
            $path = app_get_path_to_report($reports['entities_id']);

            $app_plugin_menu['items_menu_reports'][] = array('title' => $reports['name'], 'url' => url_for('ext/ganttchart/dhtmlx', 'id=' . $reports['id'] . '&path=' . $path));
        }
    }
}