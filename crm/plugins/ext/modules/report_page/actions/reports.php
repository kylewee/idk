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

if (!app_session_is_registered('report_page_filter'))
{
    $report_page_filter = 0;
    app_session_register('report_page_filter');
}

switch ($app_module_action)
{
    case 'set_filter':
        $report_page_filter = $_POST['report_page_filter'];

        redirect_to('ext/report_page/reports');
        break;
    case 'save':

        $sql_data = array(
            'is_active' => (isset($_POST['is_active']) ? 1 : 0),
            'in_dashboard' => (isset($_POST['in_dashboard']) ? 1 : 0),
            'name' => $_POST['name'],
            'entities_id' => $_POST['entities_id'],
            'type' => $_POST['type'],    
            'use_editor' => $_POST['use_editor'],
            'button_title' => $_POST['button_title'],
            'button_position' => (isset($_POST['button_position']) ? implode(',', $_POST['button_position']) : ''),
            'button_color' => $_POST['button_color'],
            'button_icon' => $_POST['button_icon'],            
            
            'users_groups' => (isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''),
            'assigned_to' => (isset($_POST['assigned_to']) ? implode(',', $_POST['assigned_to']) : ''),
            'save_filename' => $_POST['save_filename'],
            'save_as' => (isset($_POST['save_as']) ? implode(',', $_POST['save_as']) : ''),                        
            'page_orientation' => $_POST['page_orientation'],            
            'sort_order' => $_POST['sort_order'],
            'settings' => json_encode($_POST['settings']),
            'css' => $_POST['css'],
            'icon' => $_POST['icon'],
            'icon_color' => $_POST['icon_color'],
        );

        if (isset($_GET['id']))
        {
            $page= db_find('app_ext_report_page', _GET('id'));
            if ($page['entities_id'] != _POST('entities_id'))
            {
                reports::delete_reports_by_type('report_page' . _GET('id'));

                //export_templates_blocks::delele_blocks_by_template_id(_GET('id'));
            }

            db_perform('app_ext_report_page', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");            
        } 
        else
        {
            db_perform('app_ext_report_page', $sql_data);            
        }

        redirect_to('ext/report_page/reports');
        break;
    case 'delete':        
        if (isset($_GET['id']))
        {
            $report_id = _GET('id');
            db_query("delete from app_ext_report_page where id='{$report_id}'");

            reports::delete_reports_by_type('report_page' . _GET('id'));

            $blocks_query = db_query("select * from app_ext_report_page_blocks where report_id={$report_id}");
            while($blocks = db_fetch_array($blocks_query))
            {
                reports::delete_reports_by_type('report_page_block' . $blocks['id']);
            }
            db_query("delete from app_ext_report_page_blocks where report_id='{$report_id}'");
            
            $alerts->add(TEXT_WARN_DELETE_REPORT_SUCCESS, 'success');

            redirect_to('ext/report_page/reports');
        }
        
        break;
    case 'sort':
        if (isset($_POST['reports']))
        {
            $sort_order = 0;
            foreach (explode(',', $_POST['reports']) as $v)
            {
                $sql_data = array('sort_order' => $sort_order);
                db_perform('app_ext_report_page', $sql_data, 'update', "id='" . db_input(str_replace('reports_', '', $v)) . "'");
                $sort_order++;
            }
        }
        exit();
        break;
        
    case 'sort_blocks':
        if (isset($_POST['blocks']))
        {
            $sort_order = 0;
            foreach (explode(',', $_POST['blocks']) as $v)
            {
                $sql_data = array('sort_order' => $sort_order);
                db_perform('app_ext_report_page_blocks', $sql_data, 'update', "id='" . db_input(str_replace('blocks_', '', $v)) . "'");
                $sort_order++;
            }
        }
        exit();
        break;    
        
    case 'copy':
        $report_id = _get::int('id');
        $report_query = db_query("select * from app_ext_report_page where id='" . $report_id . "'");
        if ($report = db_fetch_array($report_query))
        {
            $report_description = $report['description'];
            
            unset($report['id']);
            $report['name'] = $report['name'] . ' (' . TEXT_EXT_NAME_COPY . ')';
            db_perform('app_ext_report_page', $report);
            $new_report_id = db_insert_id();
            
            
            //copy reports
            $report_query = db_query("select * from app_reports where reports_type='report_page" . $report_id . "'");
            if($report = db_fetch_array($report_query))
            {
                reports::copy($report['id'],'report_page' . $new_report_id);                                        
            }
                        
            //copy blocks
            $id_to_replace = [];
            $blocks_query = db_query("select * from app_ext_report_page_blocks where report_id={$report_id}");
            while($blocks = db_fetch_array($blocks_query))
            {
                $block_id = $blocks['id'];
                
                unset($blocks['id']);
                $blocks['report_id'] = $new_report_id;
                db_perform('app_ext_report_page_blocks', $blocks);
                $new_block_id = db_insert_id();
                
                $id_to_replace[$block_id] = $new_block_id;
                
                //prepare description
                $report_description = str_replace('${' . $block_id . '}','${' . $new_block_id . '}',$report_description);
                
                //copy reports
                $report_query = db_query("select * from app_reports where reports_type='report_page_block" . $block_id . "'");
                if($report = db_fetch_array($report_query))
                {
                    reports::copy($report['id'],'report_page_block' . $new_block_id);                                        
                }
            }
            
            //set description
            db_query("update app_ext_report_page set description = '" . db_input($report_description) . "' where id={$new_report_id}");
            
            //prepare parent_id
            foreach($id_to_replace as $block_id=>$new_block_id)
            {
                db_query("update app_ext_report_page_blocks set parent_id={$new_block_id} where parent_id={$block_id} and report_id={$new_report_id}");
            }
             
        }
        
        redirect_to('ext/report_page/reports');
        break;
}
