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
$reports_query = db_query("select * from app_ext_resource_timeline where id='" . db_input(_GET('id')) . "'");
if (!$reports = db_fetch_array($reports_query))
{
    redirect_to('dashboard/page_not_found');
}

$app_title = app_set_title($reports['name']);

if (!resource_timeline::has_access($reports['users_groups']))
{
    redirect_to('dashboard/access_forbidden');
}

switch ($app_module_action)
{
    case 'get_resources':
        $resource_timeline = new resource_timeline($reports);
        echo $resource_timeline->get_resources();        
        app_exit();
        break;   
    case 'get_events':
        $resource_timeline = new resource_timeline($reports);
        echo $resource_timeline->get_events();                
        app_exit();
        break;
    case 'resize':
        
        $end = (in_array($_POST['view_name'],['resourceTimelineMonth','timelineMonth2','timelineMonth3']) ? strtotime('-1 day',get_date_timestamp($_POST['end'])) : get_date_timestamp($_POST['end']));

        $reports_entities_query = db_query("select ce.*, e.name from app_ext_resource_timeline_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "' and ce.id='" . _POST('reports_entities_id') . "'");
        if($reports_entities = db_fetch_array($reports_entities_query))
        {
            $item_info = db_find("app_entity_{$reports_entities['entities_id']}",_POST('id'));
            
            $sql_data = array('field_' . $reports_entities['end_date'] => $end);

            db_perform("app_entity_" . $reports_entities['entities_id'], $sql_data, 'update', "id='" . db_input(_POST('id')) . "'");
            
            $log = new track_changes($reports_entities['entities_id'], _POST('id'));
            $log->log_update($item_info);
        }

        app_exit();
        break;
        
    case 'drop':

        if(isset($_POST['end']))
        {                   
            $end = ((in_array($_POST['view_name'],['resourceTimelineMonth','timelineMonth2','timelineMonth3']) and date('Y-m-d',get_date_timestamp($_POST['end'])) != date('Y-m-d',get_date_timestamp($_POST['start']))) ? strtotime('-1 day',get_date_timestamp($_POST['end'])) : get_date_timestamp($_POST['end']));                       
        }
        else
        {
            $end = get_date_timestamp($_POST['start']);
        }

        
        $reports_entities_query = db_query("select ce.*, e.name from app_ext_resource_timeline_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "' and ce.id='" . _POST('reports_entities_id') . "'");
        if($reports_entities = db_fetch_array($reports_entities_query))
        {
            $item_info = db_find("app_entity_{$reports_entities['entities_id']}",_POST('id'));
            
            $sql_data = array(
                'field_' . $reports_entities['start_date'] => get_date_timestamp($_POST['start']),
                'field_' . $reports_entities['end_date'] => $end);

            db_perform("app_entity_" . $reports_entities['entities_id'], $sql_data, 'update', "id='" . _POST('id') . "'");
            
            $log = new track_changes($reports_entities['entities_id'], _POST('id'));
            $log->log_update($item_info);
        }

        app_exit();
        break;
}