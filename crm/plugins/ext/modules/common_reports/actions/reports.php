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


if(!app_session_is_registered('common_reports_filter'))
{
    $common_reports_filter = 0;
    app_session_register('common_reports_filter');
}

$app_title = app_set_title(TEXT_HEADING_REPORTS);

switch($app_module_action)
{
    case 'copy':
        $reports_id = _get::int('reports_id');
        $reports_info = db_find('app_reports', $reports_id);
        
        $new_reports_id = reports::copy($reports_id);
        
        $new_filters_panel_id = filters_panels::get_id_by_type($reports_info['entities_id'], 'common_report_filters_panel_' . $new_reports_id);
                
        //copy filter panel
        $filters_panel_id = filters_panels::get_id_by_type($reports_info['entities_id'], 'common_report_filters_panel_' . $reports_id);                
        $fields_query = db_query("select * from app_filters_panels_fields where panels_id={$filters_panel_id}");
        while($fields = db_fetch_array($fields_query))
        {
            unset($fields['id']);
            $fields['panels_id'] = $new_filters_panel_id;
            
            db_perform('app_filters_panels_fields', $fields);
        }
        
        redirect_to('ext/common_reports/reports');
        break;
    case 'set_reports_filter':
        $common_reports_filter = $_POST['reports_filter'];

        redirect_to('ext/common_reports/reports');
        break;
    case 'save':
        $sql_data = array(
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'entities_id' => $_POST['entities_id'],
            'menu_icon' => $_POST['menu_icon'],
            'icon_color' => db_prepare_input($_POST['icon_color']),
            'bg_color' => db_prepare_input($_POST['bg_color']),
            'dashboard_sort_order' => $_POST['sort_order'],
            'in_dashboard_counter' => (isset($_POST['in_dashboard_counter']) ? $_POST['in_dashboard_counter'] : 0),
            'in_dashboard_icon' => (isset($_POST['in_dashboard_icon']) ? $_POST['in_dashboard_icon'] : 0),
            'in_dashboard_counter_color' => $_POST['in_dashboard_counter_color'],
            'in_dashboard_counter_bg_color' => db_prepare_input($_POST['in_dashboard_counter_bg_color']),
            'in_dashboard_counter_fields' => (isset($_POST['in_dashboard_counter_fields']) ? implode(',', $_POST['in_dashboard_counter_fields']) : ''),
            'dashboard_counter_sum_by_field' => $_POST['dashboard_counter_sum_by_field']??'',
            'dashboard_counter_hide_count' => (isset($_POST['dashboard_counter_hide_count']) ? 1 : 0),
            'dashboard_counter_hide_zero_count' => (isset($_POST['dashboard_counter_hide_zero_count']) ? 1 : 0),
            'users_groups' => (isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''),
            'assigned_to' => (isset($_POST['assigned_to']) ? implode(',', $_POST['assigned_to']) : ''),
            'reports_type' => 'common',
            'in_menu' => (isset($_POST['in_menu']) ? $_POST['in_menu'] : 0),
            'in_dashboard' => (isset($_POST['in_dashboard']) ? $_POST['in_dashboard'] : 0),
            'in_header' => (isset($_POST['in_header']) ? $_POST['in_header'] : 0),
            'in_header_autoupdate' => (isset($_POST['in_header_autoupdate']) ? $_POST['in_header_autoupdate'] : 0),
            'displays_assigned_only' => (isset($_POST['displays_assigned_only']) ? $_POST['displays_assigned_only'] : 0),
            'created_by' => $app_logged_users_id,
            'notification_days' => (isset($_POST['notification_days']) ? implode(',', $_POST['notification_days']) : ''),
            'notification_time' => (isset($_POST['notification_time']) ? implode(',', $_POST['notification_time']) : ''),
            'fields_in_listing' => (isset($_POST['fields_in_listing']) ? implode(',', $_POST['fields_in_listing']) : ''),
            'listing_type' => (isset($_POST['listing_type']) ? $_POST['listing_type'] : ''),
            'rows_per_page' => (isset($_POST['rows_per_page']) ? $_POST['rows_per_page'] : ''),
        );

        if(isset($_GET['id']))
        {

            $report_info = db_find('app_reports', $_GET['id']);

            //check reprot entity and if it's changed remove report filters and parent reports
            if($report_info['entities_id'] != $_POST['entities_id'])
            {
                db_query("delete from app_reports_filters where reports_id='" . db_input($_GET['id']) . "'");

                //delete paretn reports
                reports::delete_parent_reports($_GET['id']);
                $sql_data['parent_id'] = 0;
            }

            db_perform('app_reports', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_reports', $sql_data);

            $insert_id = db_insert_id();

            reports::auto_create_parent_reports($insert_id);
        }

        redirect_to('ext/common_reports/reports');
        break;
    case 'delete':
        if(isset($_GET['id']))
        {

            $report_id = _GET('id');
            //delete paretn reports
            reports::delete_parent_reports($_GET['id']);

            db_query("delete from app_reports where id='" . db_input($_GET['id']) . "'");
            db_query("delete from app_reports_filters where reports_id='" . db_input($_GET['id']) . "'");

            reports::delete_reports_by_type('common_report_filters_panel_' . $report_id);
            filters_panels::delete_by_type('common_report_filters_panel_' . $report_id);

            $alerts->add(TEXT_WARN_DELETE_REPORT_SUCCESS, 'success');


            redirect_to('ext/common_reports/reports');
        }
        break;
}