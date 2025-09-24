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
if(!isset($app_fields_cache[$current_entity_id][_get::int('fields_id')]))
{
    redirect_to('dashboard/page_not_found');
}

$cfg = new fields_types_cfg($app_fields_cache[$current_entity_id][_get::int('fields_id')]['configuration']);

switch($app_module_action)
{
    case 'cancel_singature':

        $sql_data = [
            'field_' . _get::int('fields_id') => ''
        ];
        db_perform("app_entity_" . $current_entity_id, $sql_data, 'update', "id='" . $current_item_id . "'");

        db_query("delete from app_approved_items where entities_id='" . $current_entity_id . "' and items_id='" . $current_item_id . "' and fields_id='" . _get::int('fields_id') . "'");

        exit();
        break;
    case 'singature':

        $gotopage = '';
        if(isset($_POST['gotopage']))
        {
            $gotopage = '&gotopage[' . key($_POST['gotopage']) . ']=' . current($_POST['gotopage']);
        }
        elseif(isset($_GET['gotopage']))
        {
            $gotopage = '&gotopage[' . key($_GET['gotopage']) . ']=' . current($_GET['gotopage']);
        }

        $item_info = db_find("app_entity_" . $current_entity_id, $current_item_id);

        if(!strlen($item_info['field_' . _get::int('fields_id')]))
        {                        
//approve
            if(isset($_GET['use_signature']))
            {
                $signature_query = db_query("select * from app_approved_items where users_id=" . $app_user['id'] . " and id=" . _GET('use_signature'), false);
                if($signature = db_fetch_array($signature_query))
                {
                    $sql_data = [
                        'entities_id' => $current_entity_id,
                        'items_id' => $current_item_id,
                        'fields_id' => _get::int('fields_id'),
                        'users_id' => $app_user['id'],
                        'signature' => $signature['signature'],
                        'date_added' => time(),
                    ];

                    db_perform('app_approved_items', $sql_data);
                    
                    $fields_id = _get::int('fields_id');
                    
                    $item_query = db_query("select * from app_entity_{$current_entity_id} where id={$signature['items_id']}");
                    if($item = db_fetch_array($item_query))
                    {
                        $sql_data = [
                            'field_' . $fields_id => $item['field_' . $fields_id]
                        ];
                        db_perform("app_entity_" . $current_entity_id, $sql_data, 'update', "id='" . $current_item_id . "'");
                    }
                }
                
            }
            else
            {                            
                $sql_data = [
                    'entities_id' => $current_entity_id,
                    'items_id' => $current_item_id,
                    'fields_id' => _get::int('fields_id'),
                    'users_id' => $app_user['id'],
                    'signature' => (isset($_POST['signature']) ? $_POST['signature'] : ''),
                    'date_added' => time(),
                ];

                db_perform('app_approved_items', $sql_data);

                $sql_data = [
                    'field_' . _get::int('fields_id') => db_prepare_input($_POST['name'])
                ];
                db_perform("app_entity_" . $current_entity_id, $sql_data, 'update', "id='" . $current_item_id . "'");
            }

//add comment
            if($cfg->get('add_comment') == 1)
            {
                $sql_data = array(
                    'description' => (strlen($cfg->get('comment_text')) ? $cfg->get('comment_text') : TEXT_APPROVED),
                    'entities_id' => $current_entity_id,
                    'items_id' => $current_item_id,
                    'date_added' => time(),
                    'created_by' => $app_user['id'],
                );

                db_perform('app_comments', $sql_data);

                $comments_id = db_insert_id();

                //send notificaton
                app_send_new_comment_notification($comments_id, $current_item_id, $current_entity_id);

                //track changes
                if(is_ext_installed())
                {
                    $log = new track_changes($current_entity_id, $current_item_id);
                    $log->log_comment($comments_id, array());
                }
            }

//run process			
            if($cfg->get('run_process') > 0)
            {
                if(approved_items::is_all_approved($current_entity_id, $current_item_id, _get::int('fields_id')))
                {
                    redirect_to('items/processes', 'action=run&id=' . $cfg->get('run_process') . '&path=' . $app_path . '&redirect_to=' . $app_redirect_to . $gotopage);
                }
            }
        }




        switch($app_redirect_to)
        {
            case 'dashboard':
                redirect_to('dashboard/', substr($gotopage, 1));
                break;
            case 'items_info':
                redirect_to('items/info', 'path=' . $app_path);
                break;
            case 'items':
                redirect_to('items/items', 'path=' . substr($app_path, 0, -(strlen($current_item_id) + 1)) . $gotopage);
                break;
            default:
                if(strstr($app_redirect_to, 'kanban'))
                {
                    redirect_to('ext/kanban/view', 'id=' . str_replace('kanban', '', $app_redirect_to) . '&path=' . $app_path);
                }
                elseif(strstr($app_redirect_to, 'report_'))
                {
                    redirect_to('reports/view', 'reports_id=' . str_replace('report_', '', $app_redirect_to) . $gotopage);
                }
                else
                {
                    redirect_to('items/items', 'path=' . $app_path . $gotopage);
                }
                break;
        }

        break;
}