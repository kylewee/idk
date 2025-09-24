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

$pivot_calendar_info_query = db_query("select * from app_ext_pivot_calendars where id='" . _get::int('calendars_id') . "'");
if(!$pivot_calendar_info = db_fetch_array($pivot_calendar_info_query))
{
    redirect_to('ext/pivot_calendars/reports');
}

switch($app_module_action)
{
    case 'save':

        $sql_data = array(
            'calendars_id' => $pivot_calendar_info['id'],
            'entities_id' => $_POST['entities_id'],
            'heading_template' => $_POST['heading_template'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'use_background' => $_POST['use_background'],
            'fields_in_popup' => (isset($_POST['fields_in_popup']) ? implode(',', $_POST['fields_in_popup']) : ''),
            'bg_color' => $_POST['bg_color'],
            'reminder_status' => $_POST['reminder_status'],
            'reminder_type' => (isset($_POST['reminder_type']) ? implode(',', $_POST['reminder_type']) : ''),
            'reminder_minutes' => $_POST['reminder_minutes'],
            'reminder_item_heading' => $_POST['reminder_item_heading'],
        );


        if(isset($_GET['id']))
        {
            db_perform('app_ext_pivot_calendars_entities', $sql_data, 'update', "id='" . db_input(_get::int('id')) . "'");
            $calendars_entities_id = _get::int('id');
        }
        else
        {
            db_perform('app_ext_pivot_calendars_entities', $sql_data);
            $calendars_entities_id = db_insert_id();
        }

        //create default fitler
        $reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($_POST['entities_id']) . "' and reports_type='pivot_calendars" . $calendars_entities_id . "'");
        if(!$reports_info = db_fetch_array($reports_info_query))
        {
            $sql_data = array('name' => '',
                'entities_id' => $_POST['entities_id'],
                'reports_type' => 'pivot_calendars' . $calendars_entities_id,
                'in_menu' => 0,
                'in_dashboard' => 0,
                'listing_order_fields' => '',
                'created_by' => $app_logged_users_id,
            );

            db_perform('app_reports', $sql_data);
            $insert_id = db_insert_id();

            reports::auto_create_parent_reports($insert_id);
        }

        redirect_to('ext/pivot_calendars/entities', 'calendars_id=' . $pivot_calendar_info['id']);
        break;

    case 'delete':

        db_delete_row('app_ext_pivot_calendars_entities', _get::int('id'));

        $report_info_query = db_query("select * from app_reports where reports_type='pivot_calendars" . db_input($_GET['id']) . "'");
        if($report_info = db_fetch_array($report_info_query))
        {
            reports::delete_reports_by_id($report_info['id']);
        }

        redirect_to('ext/pivot_calendars/entities', 'calendars_id=' . $pivot_calendar_info['id']);
    case 'get_entities_fields':

        $entities_id = $_POST['entities_id'];

        $obj = array();

        if(isset($_POST['id']))
        {
            $obj = db_find('app_ext_pivot_calendars_entities', $_POST['id']);
        }
        else
        {
            $obj = db_show_columns('app_ext_pivot_calendars_entities');
        }

        $start_date_fields = array();
        $fields_query = db_query("select * from app_fields where type in ('fieldtype_input_date','fieldtype_input_date_extra','fieldtype_input_datetime','fieldtype_dynamic_date') and entities_id='" . db_input($entities_id) . "' order by sort_order, name");
        while($fields = db_fetch_array($fields_query))
        {
            $start_date_fields[$fields['id']] = ($fields['type'] == 'fieldtype_date_added' ? TEXT_FIELDTYPE_DATEADDED_TITLE : $fields['name']);
        }

        $html = '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_GANTT_START_DATE . '</label>
            <div class="col-md-9">
          	   ' . select_tag('start_date', $start_date_fields, $obj['start_date'], array('class' => 'form-control input-large required')) . '
            </div>
          </div>
        ';

        $end_date_fields = array();
        $fields_query = db_query("select * from app_fields where type in ('fieldtype_input_date','fieldtype_input_date_extra','fieldtype_input_datetime','fieldtype_dynamic_date') and entities_id='" . db_input($entities_id) . "' order by sort_order, name");
        while($fields = db_fetch_array($fields_query))
        {
            $end_date_fields[$fields['id']] = ($fields['type'] == 'fieldtype_date_added' ? TEXT_FIELDTYPE_DATEADDED_TITLE : $fields['name']);
        }

        $html .= '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_GANTT_END_DATE . '</label>
            <div class="col-md-9">
          	   ' . select_tag('end_date', $end_date_fields, $obj['end_date'], array('class' => 'form-control input-large required')) . '
            </div>
          </div>
        ';


        $html .= '
	         <div class="form-group">
	          	<label class="col-md-3 control-label" for="allowed_groups">' . tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_HEADING_TEMPLATE . fields::get_available_fields_helper($entities_id, 'heading_template') . '</label>
	            <div class="col-md-9">
	          	   ' . input_tag('heading_template', $obj['heading_template'], array('class' => 'form-control input-large')) . '
	          	   ' . tooltip_text(TEXT_HEADING_TEMPLATE_INFO) . '
	            </div>
	          </div>
	        ';



        $use_fields = array();
        $use_fields[''] = '';
        $fields_query = db_query("select * from app_fields where type in ('fieldtype_dropdown','fieldtype_color','fieldtype_radioboxes','fieldtype_stages') and entities_id='" . db_input($entities_id) . "' order by sort_order, name");
        while($fields = db_fetch_array($fields_query))
        {
            $use_fields[$fields['id']] = $fields['name'];
        }

        if(count($use_fields))
        {
            $html .= '
	         <div class="form-group">
	          	<label class="col-md-3 control-label" for="allowed_groups">' . tooltip_icon(TEXT_EXT_USE_BACKGROUND_INFO) . TEXT_EXT_USE_BACKGROUND . '</label>
	            <div class="col-md-9">
	          	   ' . select_tag('use_background', $use_fields, $obj['use_background'], array('class' => 'form-control input-large')) . '
	            </div>
	          </div>
	        ';
        }

        $html .= '
	         <div class="form-group">
	          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_FIELDS_IN_POPUP_TOOLTIP . '</label>
	            <div class="col-md-9">
	          	   ' . select_tag('fields_in_popup[]', fields::get_choices($entities_id), $obj['fields_in_popup'], array('class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple')) . '
	            </div>
	          </div>
	        ';




        echo $html;

        exit();
        break;
    case 'get_reminder_item_heading':
        $entities_id = $_POST['entities_id'];
        $obj = isset($_POST['id']) ? db_find('app_ext_pivot_calendars_entities', $_POST['id']) : db_show_columns('app_ext_pivot_calendars_entities');

        $html = '
            <div class="form-group">
            	<label class="col-md-3 control-label" for="name">' . tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_HEADING_TEMPLATE . fields::get_available_fields_helper($entities_id, 'reminder_item_heading') . '</label>
              <div class="col-md-9">
            	  ' . textarea_tag('reminder_item_heading', $obj['reminder_item_heading'], array('class' => 'form-control')) .
                tooltip_text(TEXT_HEADING_TEMPLATE_INFO . '<br>' . TEXT_EXT_HTML_ALLOWED) . '
              </div>
            </div>
            ';

        echo $html;

        app_exit();

        break;
}