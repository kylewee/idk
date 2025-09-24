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
?>
<?php

app_reset_selected_items();

//tabs
echo reports_groups::render_dashboard_tabs();

//dashboard pages
$page = new dashboard_pages;
echo $page->render_info_blocks();
echo $page->render_info_pages();

$has_reports_on_dashboard = $page->has_pages;

//counters
$reports_counter = new reports_counter;
$html = $reports_counter->render();
if(strlen($html))
{
    echo $html;

    $has_reports_on_dashboard = true;
}

//include sections
require(component_path('dashboard/sections'));

$reports_query = db_query("select * from app_reports where created_by='" . db_input($app_logged_users_id) . "' and in_dashboard in (1,2) and reports_type in ('standard') order by dashboard_sort_order, name");
while($reports = db_fetch_array($reports_query))
{
    if(!users::has_access_to_entity($reports['entities_id'],'reports'))
    {
        continue;
    }

    $check_query = db_query("select id from app_reports_sections where (report_left='standard{$reports['id']}' or report_right='standard{$reports['id']}') and reports_groups_id=0 and created_by='" . $app_user['id'] . "'");
    if($check = db_fetch_array($check_query))
    {
        echo '
			<div class="row">
                            <div class="col-md-12"><h3 class="page-title"><a href="' . url_for('reports/view', 'reports_id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3></div>
                        </div>
			<div class="alert alert-warning">' . TEXT_REPORT_ALREADY_ASSIGNED . '</div>';

        $has_reports_on_dashboard = true;
    }
    else
    {
        require(component_path('dashboard/render_standard_reports'));
    }
}

if(is_ext_installed())
{
    require(component_path('dashboard/report_page'));
}

//include common reports
require(component_path('dashboard/common_reports'));

//display default dashboard msg
if(!$has_reports_on_dashboard and $app_user['group_id'] == 0)
{
    echo TEXT_DASHBOARD_DEFAULT_ADMIN_MSG;
}
elseif(!$has_reports_on_dashboard)
{
    echo TEXT_DASHBOARD_DEFAULT_MSG;
}

require(component_path('items/load_items_listing.js'));

