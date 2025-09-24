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

$app_title = app_set_title(TEXT_MENU_DASHBOARD);

if(!strlen($app_module_action))
{
    //autoreset session table if default _sess_gc function not working
    app_session_table_reset();
}

switch($app_module_action)
{
    case 'save':
        if(isset($_POST['hidden_common_reports']))
        {
            $app_users_cfg->set('hidden_common_reports', implode(',', $_POST['hidden_common_reports']));
        }
        else
        {
            $app_users_cfg->set('hidden_common_reports', '');
        }

        redirect_to('dashboard/');
        break;
    case 'keep_session':
                        
        app_exit();
        break;
    case 'who_is_online':
        who_is_online::set();
        
        app_exit();
        break;
    
    case 'set_last_user_action':
        last_user_action::set();
        app_exit();
        break;
    case 'check_inaction_users':
        last_user_action::remove_inaction_users();
        
        //remove any blocked form for inaction users
        blocked_forms::remove_inaction_users();
        
        if(!last_user_action::has())
        {
            echo 'INACTION';
        }
        
        app_exit();
        break;
    
    case 'blocked_forms_unset':
        blocked_forms::unset(_GET('entity_id'), _GET('item_id'));
        
        app_exit();
        break;
    
    case 'ajax_counter_render':
        $reports_counter = new reports_counter;
        $reports_counter->common_filter_reports_id = (int)$_GET['common_filter_reports_id'];
        $path_info = items::parse_path($app_path);
        if($path_info['parent_entity_item_id'])
        {
            $reports_counter->parent_item_id = $path_info['parent_entity_item_id'];
        }
        echo $reports_counter->ajax_render( (int)$_GET['reports_id'] );
        exit();
        break;
    
    case 'sort_reports':

        if(isset($_POST['reports_on_dashboard']))
        {
            $sort_order = 0;
            foreach(explode(',', $_POST['reports_on_dashboard']) as $v)
            {
                $sql_data = array('in_dashboard' => 1, 'dashboard_sort_order' => $sort_order);
                db_perform('app_reports', $sql_data, 'update', "id='" . db_input(str_replace('report_', '', $v)) . "' and created_by='" . db_input($app_user['id']) . "'");
                $sort_order++;
            }
        }

        if(isset($_POST['reports_excluded_from_dashboard']))
        {
            foreach(explode(',', $_POST['reports_excluded_from_dashboard']) as $v)
            {
                $sql_data = array('in_dashboard' => 0, 'dashboard_sort_order' => 0);
                db_perform('app_reports', $sql_data, 'update', "id='" . db_input(str_replace('report_', '', $v)) . "' and created_by='" . db_input($app_user['id']) . "'");
            }
        }

        app_exit();
        break;

    case 'sort_reports_countr':

        if(isset($_POST['reports_counter_on_dashboard']))
        {
            $sort_order = 0;
            foreach(explode(',', $_POST['reports_counter_on_dashboard']) as $v)
            {
                $sql_data = array('in_dashboard_counter' => 1, 'dashboard_counter_sort_order' => $sort_order);
                db_perform('app_reports', $sql_data, 'update', "id='" . db_input(str_replace('report_', '', $v)) . "' and created_by='" . db_input($app_user['id']) . "'");
                $sort_order++;
            }
        }

        if(isset($_POST['reports_counter_excluded_from_dashboard']))
        {
            foreach(explode(',', $_POST['reports_counter_excluded_from_dashboard']) as $v)
            {
                $sql_data = array('in_dashboard_counter' => 0, 'dashboard_counter_sort_order' => 0);
                db_perform('app_reports', $sql_data, 'update', "id='" . db_input(str_replace('report_', '', $v)) . "' and created_by='" . db_input($app_user['id']) . "'");
            }
        }

        app_exit();
        break;

    case 'sort_reports_header':

        if(isset($_POST['reports_in_header']))
        {
            $sort_order = 0;
            foreach(explode(',', $_POST['reports_in_header']) as $v)
            {
                $sql_data = array('in_header' => 1, 'header_sort_order' => $sort_order);
                db_perform('app_reports', $sql_data, 'update', "id='" . db_input(str_replace('report_', '', $v)) . "' and created_by='" . db_input($app_user['id']) . "'");
                $sort_order++;
            }
        }

        if(isset($_POST['reports_excluded_in_header']))
        {
            foreach(explode(',', $_POST['reports_excluded_in_header']) as $v)
            {
                $sql_data = array('in_header' => 0, 'header_sort_order' => 0);
                db_perform('app_reports', $sql_data, 'update', "id='" . db_input(str_replace('report_', '', $v)) . "' and created_by='" . db_input($app_user['id']) . "'");
            }
        }

        app_exit();
        break;

    case 'update_hot_reports':

        $reports_info_query = db_query("select * from app_reports where id='" . db_input($_GET['reports_id']) . "'");
        if(!$reports_info = db_fetch_array($reports_info_query))
        {
            app_exit();
        }

        //check report access
        if($reports_info['reports_type'] == 'common')
        {
            //check access for common report
            if($app_user['group_id']>0)
            {
                $check_query = db_query("select r.* from app_reports r, app_entities e, app_entities_access ea  where r.id = '" . $reports_info['id'] . "' and  r.entities_id = e.id and e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($app_user['group_id']) . "' and (find_in_set(" . $app_user['group_id'] . ",r.users_groups) or find_in_set(" . $app_user['id'] . ",r.assigned_to)) and r.reports_type = 'common' order by r.dashboard_sort_order, r.name");
            }
            else
            {
                $check_query = db_query("select r.* from app_reports r, app_entities e  where r.id = '" . $reports_info['id'] . "' and  r.entities_id = e.id and (find_in_set(" . $app_user['group_id'] . ",r.users_groups) or find_in_set(" . $app_user['id'] . ",r.assigned_to)) and r.reports_type = 'common' order by r.dashboard_sort_order, r.name");
            }
            
            if(!$check = db_fetch_array($check_query))
            {
                app_exit();
            }
        }
        elseif($app_logged_users_id != $reports_info['created_by'])
        {
            app_exit();
        }

        $hot_reports = new hot_reports();
        echo $hot_reports->render_dropdown($_GET['reports_id']);

        db_dev_log();

        app_exit();
        break;
    case 'update_favorites_header_dropdown':
        echo favorites::render_header_dropdown();
        app_exit();
        break;
    case 'update_user_notifications_report':
        echo users_notifications::render_dropdown();
        
        attachments_viewer::reset_tmp_files();
        
        if(is_ext_installed())
        {            
            $app_calendar_reminder->init();
        }
        
        app_exit();
        break;
    case 'set_users_alers_viewed':

        $sql_data = array(
            'users_id' => $app_user['id'],
            'alerts_id' => _post::int('id'),
        );

        db_perform('app_users_alerts_viewed', $sql_data);

        app_exit();
        break;
    
    case 'set_filter_status':
        
        db_query("update app_reports_filters set is_active=" . _POST('is_active'). " where id=" . _POST('filter_id'));
        
        app_exit();
        break;
    
    case 'calendar_reminder_confirm':        
        $app_calendar_reminder->reset();
        exit();
        break;
    
    case 'calendar_remind_later':
        $app_calendar_reminder->remind_later();
        exit();
        break;
} 