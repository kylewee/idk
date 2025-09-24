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
//check if report exist
$reports_query = db_query("select * from app_ext_kanban where is_active=1 and id='" . db_input((int) $_GET['id']) . "' and (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to))");
if(!$reports = db_fetch_array($reports_query))
{
    redirect_to('dashboard/page_not_found');
}

if(!isset_field($reports['entities_id'],$reports['group_by_field']))
{
    die(sprintf(TEXT_FIELD_DOES_NOT_EXIST_ERROR, $reports['group_by_field'], entities::get_name_by_id($reports['entities_id'])));
}

app_set_title($reports['name']);


$app_path = (strlen($app_path) ? $app_path : $reports['entities_id']);

$listing_highlight = new listing_highlight($reports['entities_id']);

//get report entity access schema
$current_access_schema = $access_schema = users::get_entities_access_schema($reports['entities_id'], $app_user['group_id']);

$is_kanban_sotrtable = false;

if (users::has_access('update', $access_schema) and $app_fields_cache[$reports['entities_id']][$reports['group_by_field']]['type'] != 'fieldtype_autostatus')
{
    $is_kanban_sotrtable = true;
    
    //set off access editable kanban if group_by_field has view/hidden access
    $fields_access_schema = users::get_fields_access_schema($reports['entities_id'], $app_user['group_id']);    
    if(isset($fields_access_schema[$reports['group_by_field']]))
    {
        $is_kanban_sotrtable = false;
    }              
}

$panels_id = filters_panels::get_id_by_type($reports['entities_id'], 'kanban_reports' . $reports['id'], 0);
$count_panel_fields = filters_panels::count_fields_by_panel_id($panels_id);


//create default entity report for logged user
$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($reports['entities_id']) . "' and reports_type='kanban" . $reports['id'] . "' and created_by='" . $app_logged_users_id . "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{
    $sql_data = array('name' => '',
        'entities_id' => $reports['entities_id'],
        'reports_type' => 'kanban' . $reports['id'],
        'in_menu' => 0,
        'in_dashboard' => 0,
        'listing_order_fields' => '',
        'created_by' => $app_logged_users_id,
    );

    db_perform('app_reports', $sql_data);
    $fiters_reports_id = db_insert_id();

    $reports_info = db_find('app_reports', $fiters_reports_id);
}
else
{
    $fiters_reports_id = $reports_info['id'];
}


switch($app_module_action)
{
    case 'kanban':
        require(component_path('ext/kanban/kanban'));
        app_exit();
        break;
    case 'kanban_items':
        $items_html = kanban::get_items_html([
            'choices_id' => (int)$_GET['choice_id'],
            'reports' => $reports,  
            'fiters_reports_id' => $fiters_reports_id,
            'listing_highlight' => $listing_highlight,
            'is_kanban_sotrtable' => $is_kanban_sotrtable,
        ]);
        echo $items_html;
        
        app_exit();
        break;
    case 'sort':

        //get report entity access schema
        $access_schema = users::get_entities_access_schema($reports['entities_id'], $app_user['group_id']);

        $choices_id = _post::int('choices_id');
        $item_id = _post::int('item_id');

        $field_info = db_find('app_fields', $reports['group_by_field']);
        $field_cfg = new fields_types_cfg($field_info['configuration']);

        $cfg = new fields_types_cfg($field_info['configuration']);

        //use global lists if exsit
        if($cfg->get('use_global_list') > 0)
        {
            $kanban_choices = global_lists::get_choices($cfg->get('use_global_list'), false);
        }
        else
        {
            $kanban_choices = fields_choices::get_choices($field_info['id'], false);
        }
        
        
        $kanban_info_choices = array();
        $output = array();

        $item_info = db_find("app_entity_" . $reports['entities_id'], $item_id);
        if(isset($item_info['field_' . $reports['group_by_field']]))
        {
            //get previous choices ID
            $previous_choices_id = $item_info['field_' . $reports['group_by_field']];

            //update item
            db_query("update app_entity_" . $reports['entities_id'] . " set field_" . $reports['group_by_field'] . " = " . $choices_id . " where id='" . $item_id . "'");

            //autoupdate all field types
            fields_types::update_items_fields($reports['entities_id'], $item_id);

            $app_send_to = users::get_assigned_users_by_item($reports['entities_id'], $item_id);

            //sms
            $modules = new modules('sms');
            $sms = new sms($reports['entities_id'], $item_id);
            $sms->send_to = $app_send_to;
            $sms->send_edit_msg($item_info);

            //email rules
            $email_rules = new email_rules($reports['entities_id'], $item_id);
            $email_rules->send_edit_msg($item_info);
            
            //run actions after item update
            $processes = new processes($reports['entities_id']);
            $processes->run_after_update($item_id);

            //send notification
            if($field_cfg->get('notify_when_changed') == 1)
            {
                $app_changed_fields = array();

                $app_changed_fields[] = array(
                    'name' => $field_info['name'],
                    'value' => $kanban_choices[$choices_id],
                    'fields_id' => $field_info['id'],
                    'fields_value' => $choices_id,
                );

                //autocreate comment
                comments::add_comment_notify_when_fields_changed($reports['entities_id'], $item_id, $app_changed_fields);

                /**
                 * Start email notification code
                 * */
                //include sender in notification
                if(CFG_EMAIL_COPY_SENDER == 1)
                {
                    $app_send_to[] = $app_user['id'];
                }


                //Send notification if there are assigned users and there are changed fields or new assigned users
                if(count($app_send_to) > 0 and count($app_changed_fields) > 0)
                {
                    $breadcrumb = items::get_breadcrumb_by_item_id($reports['entities_id'], $item_info['id']);
                    $item_name = $breadcrumb['text'];

                    $entity_cfg = new entities_cfg($reports['entities_id']);

                    //prepare subject for update itme					
                    $subject = (strlen($entity_cfg->get('email_subject_updated_item')) > 0 ? $entity_cfg->get('email_subject_updated_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_UPDATED_ITEM . ' ' . $item_name);

                    //add changed field values in subject
                    $extra_subject = array();
                    foreach($app_changed_fields as $v)
                    {
                        $extra_subject[] = $v['name'] . ': ' . $v['value'];
                    }

                    $subject .= ' [' . implode(' | ', $extra_subject) . ']';

                    $path_info = items::get_path_info($reports['entities_id'], $item_id);

                    //default email heading
                    $heading = users::use_email_pattern_style('<div><a href="' . url_for('items/info', 'path=' . $path_info['full_path'], true) . '"><h3>' . $subject . '</h3></a></div>', 'email_heading_content');

                    //if only users fields changed then send notification to new assigned users
                    if(count($app_changed_fields) == 0 and count($app_send_to_new_assigned) > 0)
                    {
                        $app_send_to = $app_send_to_new_assigned;
                    }

                    //start sending email
                    foreach(array_unique($app_send_to) as $send_to)
                    {
                        //prepare body
                        if($entity_cfg->get('item_page_details_columns', '2') == 1)
                        {
                            $body = users::use_email_pattern('single_column', array('email_single_column' => items::render_info_box($reports['entities_id'], $item_id, $send_to, false)));
                        }
                        else
                        {
                            $body = users::use_email_pattern('single', array('email_body_content' => items::render_content_box($reports['entities_id'], $item_id, $send_to), 'email_sidebar_content' => items::render_info_box($reports['entities_id'], $item_id, $send_to)));
                        }

                        if(users_cfg::get_value_by_users_id($send_to, 'disable_notification') != 1)
                        {
                            users::send_to(array($send_to), $subject, $heading . $body);
                        }

                        //add users notification
                        users_notifications::add($subject, 'updated_item', $send_to, $reports_info['entities_id'], $item_id);
                    }
                }
                /**
                 * End email notification code
                 * */
            }


            //calculate totals
            $kanban_info_choices[$choices_id]['count'] = 0;
            $kanban_info_choices[$previous_choices_id]['count'] = 0;

            if(strlen($reports['sum_by_field']))
            {
                foreach(explode(',', $reports['sum_by_field']) as $k)
                {
                    $kanban_info_choices[$choices_id][$k] = 0;
                    $kanban_info_choices[$previous_choices_id][$k] = 0;
                }
            }

            //current choice totals
            $items_query_sql = kanban::get_items_query($reports['group_by_field'] . ':' . $choices_id, $reports, $fiters_reports_id);
            $items_query = db_query($items_query_sql);
            while($items = db_fetch_array($items_query))
            {
                $kanban_info_choices[$choices_id]['count']++;

                //prepare sum by field
                if(strlen($reports['sum_by_field']))
                {
                    foreach(explode(',', $reports['sum_by_field']) as $k)
                    {
                        if(strlen($items['field_' . $k]))
                            $kanban_info_choices[$choices_id][$k] += $items['field_' . $k];
                    }
                }
            }

            $sum_html = '';
            if(strlen($reports['sum_by_field']))
            {
                $sum_html = '<table class="kanban-heading-sum">';
                foreach(explode(',', $reports['sum_by_field']) as $id)
                {
                    $sum_html .= '
  					<tr>
  						<td>' . $app_fields_cache[$reports['entities_id']][$id]['name'] . ':&nbsp;</td>
  						<th>' . fieldtype_input_numeric::number_format($kanban_info_choices[$choices_id][$id], $app_fields_cache[$reports['entities_id']][$id]['configuration']) . '</th>
  					</tr>';
                }
                $sum_html .= '</table>';
            }

            $add_button = '';
            if(users::has_access('create', $access_schema) and $app_fields_cache[$reports['entities_id']][$reports['group_by_field']]['type'] != 'fieldtype_autostatus')
            {
                $add_button = '<a class="btn btn-default btn-xs purple kanban-add-button" href="#" onClick="open_dialog(\'' . url_for('items/form', 'path=' . $app_path . '&redirect_to=kanban' . $reports['id'] . '&fields[' . $reports['group_by_field'] . ']=' . $choices_id) . '\')"><i class="fa fa-plus" aria-hidden="true"></i></a>';
            }
            
            $choices_name = $kanban_choices[$choices_id];
            
            //add icon to name
            $icon = $cfg->get('use_global_list') > 0 ? $app_global_choices_cache[$choices_id]['icon'] : $app_choices_cache[$choices_id]['icon'];
            if(strlen($icon))
            {
                $choices_name = app_render_icon($icon) . ' ' . $choices_name;
            }

            $html = '
					<div class="heading">' . $add_button . $choices_name . ' (' . $kanban_info_choices[$choices_id]['count'] . ')</div>
  				<div>' . $sum_html . '</div>
					';

            $output[$choices_id] = trim($html);

            //preivous choice totals
            $items_query_sql = kanban::get_items_query($reports['group_by_field'] . ':' . $previous_choices_id, $reports, $fiters_reports_id);
            $items_query = db_query($items_query_sql);
            while($items = db_fetch_array($items_query))
            {
                $kanban_info_choices[$previous_choices_id]['count']++;

                //prepare sum by field
                if(strlen($reports['sum_by_field']))
                {
                    foreach(explode(',', $reports['sum_by_field']) as $k)
                    {
                        if(strlen($items['field_' . $k]))
                            $kanban_info_choices[$previous_choices_id][$k] += $items['field_' . $k];
                    }
                }
            }

            $sum_html = '';
            if(strlen($reports['sum_by_field']))
            {
                $sum_html = '<table class="kanban-heading-sum">';
                foreach(explode(',', $reports['sum_by_field']) as $id)
                {
                    $sum_html .= '
  					<tr>
  						<td>' . $app_fields_cache[$reports['entities_id']][$id]['name'] . ':&nbsp;</td>
  						<th>' . fieldtype_input_numeric::number_format($kanban_info_choices[$previous_choices_id][$id], $app_fields_cache[$reports['entities_id']][$id]['configuration']) . '</th>
  					</tr>';
                }
                $sum_html .= '</table>';
            }

            $add_button = '';
            if(users::has_access('create', $access_schema) and $app_fields_cache[$reports['entities_id']][$reports['group_by_field']]['type'] != 'fieldtype_autostatus')
            {
                $add_button = '<a class="btn btn-default btn-xs purple kanban-add-button" href="#" onClick="open_dialog(\'' . url_for('items/form', 'path=' . $app_path . '&redirect_to=kanban' . $reports['id'] . '&fields[' . $reports['group_by_field'] . ']=' . $previous_choices_id) . '\')"><i class="fa fa-plus" aria-hidden="true"></i></a>';
            }
            
            $choices_name = $kanban_choices[$previous_choices_id];
            
            //add icon to name
            $icon = $cfg->get('use_global_list') > 0 ? $app_global_choices_cache[$previous_choices_id]['icon'] : $app_choices_cache[$previous_choices_id]['icon'];
            if(strlen($icon))
            {
                $choices_name = app_render_icon($icon) . ' ' . $choices_name;
            }

            $html = '
					<div class="heading">' . $add_button . $choices_name . ' (' . $kanban_info_choices[$previous_choices_id]['count'] . ')</div>
  				<div>' . $sum_html . '</div>
					';

            $output[$previous_choices_id] = trim($html);

            echo json_encode($output);
        }

        exit();
        break;
}
