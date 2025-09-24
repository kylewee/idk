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

switch ($app_module_action)
{
    case 'save':
        
        //check min/max dates
  	$min_time = $_POST['min_time'];
  	$max_time = $_POST['max_time'];
  	
  	if((int)$min_time>(int)$max_time)
  	{
  		$max_time = '';
  	}
  	
  	if(!strstr($min_time,':00') and !strstr($min_time,':30') and strlen($min_time))
  	{
  		$min_time = explode(':',$min_time);
  		$min_time = $min_time[0] . ':00';
  	}
  	
  	if(!strstr($max_time,':00') and !strstr($max_time,':30') and strlen($max_time))
  	{
  		$max_time = explode(':',$max_time);
  		$max_time = $max_time[0] . ':00';
  	}
        
        $sql_data = array(
            'name' => $_POST['name'],
            'entities_id' => $_POST['entities_id'],
            'heading_template' => $_POST['heading_template'],  
            'column_width' => $_POST['column_width'],   
            'listing_width' => $_POST['listing_width'],            
            'group_by_field' => $_POST['group_by_field'],
            'fields_in_listing' => (isset($_POST['fields_in_listing']) ? implode(',',$_POST['fields_in_listing']) : ''),            
            'fields_in_popup' => (isset($_POST['fields_in_popup']) ? implode(',',$_POST['fields_in_popup']) : ''),            
            'default_view' => $_POST['default_view'],
            'view_modes' => (isset($_POST['view_modes']) ? implode(',',$_POST['view_modes']) : ''),
            'time_slot_duration'=>$_POST['time_slot_duration'],
            'in_menu' => (isset($_POST['in_menu']) ? $_POST['in_menu'] : 0),
            'display_legend' => (isset($_POST['display_legend']) ? $_POST['display_legend'] : 0),            
            'users_groups' => (isset($_POST['access']) ? json_encode($_POST['access']) : ''),
            'sort_order' => $_POST['sort_order'],
            'min_time'=>$min_time,
            'max_time'=>$max_time,
        );

        if (isset($_GET['id']))
        {
            $calendar_id = _GET('id');
            
            $obj = db_find('app_ext_resource_timeline', $calendar_id);
            
            if($obj['entities_id']!=$_POST['entities_id'])
            {
                reports::delete_reports_by_type('resource_timeline' . $calendar_id);
                reports::auto_create_report_by_type($_POST['entities_id'], 'resource_timeline' . $calendar_id);
                
                $entities_query = db_query("select id from app_ext_resource_timeline_entities where calendars_id='" . $calendar_id . "'");
                while ($entities = db_fetch_array($entities_query))
                {
                    reports::delete_reports_by_type('resource_timeline_entities' . $entities['id']);            
                }
                
                db_delete_row('app_ext_resource_timeline_entities', $calendar_id, 'calendars_id');                                
            }

            db_perform('app_ext_resource_timeline', $sql_data, 'update', "id='" . db_input($calendar_id) . "'");
        }
        else
        {
            db_perform('app_ext_resource_timeline', $sql_data);
            $calendar_id = db_insert_id();   
            
            reports::auto_create_report_by_type($_POST['entities_id'], 'resource_timeline' . $calendar_id);
        }
                

        redirect_to('ext/resource_timeline/reports');
        break;

    case 'delete':
        $calendar_id = _get::int('id');

        $obj = db_find('app_ext_resource_timeline', $calendar_id);

        db_delete_row('app_ext_resource_timeline', $calendar_id);
        
        reports::delete_reports_by_type('resource_timeline' . $calendar_id);
        reports::delete_reports_by_type('resource_timeline_filters_panel_' . $calendar_id);
        filters_panels::delete_by_type('resource_timeline_filters_panel_' . $calendar_id);
                
        $entities_query = db_query("select id, entities_id from app_ext_resource_timeline_entities where calendars_id='" . $calendar_id . "'");
        while ($entities = db_fetch_array($entities_query))
        {
            reports::delete_reports_by_type('resource_timeline_entities' . $entities['id']);

            //delete filtes panels
            $filters_panel_type = 'resource_timeline_entity_filters_panel_' . $calendar_id . '_' . $entities['entities_id'];        
            filters_panels::delete_by_type($filters_panel_type);
            reports::delete_reports_by_type($filters_panel_type);            
        }

        db_delete_row('app_ext_resource_timeline_entities', $calendar_id, 'calendars_id');

        $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS, $obj['name']), 'success');

        redirect_to('ext/resource_timeline/reports');
        break;
        
    case 'get_entities_fields':

        $entities_id = $_POST['entities_id'];

        $obj = array();

        if (isset($_POST['id']))
        {
            $obj = db_find('app_ext_resource_timeline', $_POST['id']);
        }
        else
        {
            $obj = db_show_columns('app_ext_resource_timeline');
        }
        
        $html = '
	         <div class="form-group">
	          	<label class="col-md-3 control-label" for="allowed_groups">' . tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_HEADING_TEMPLATE . fields::get_available_fields_helper($entities_id, 'heading_template') . '</label>
	            <div class="col-md-9">
	          	   ' . input_tag('heading_template', $obj['heading_template'], array('class' => 'form-control input-large')) . '
	          	   ' . tooltip_text(TEXT_HEADING_TEMPLATE_INFO) . '
	            </div>
	          </div>
	        ';  

        $fields_in_listing = array();
        $fields_query = fields::get_query($entities_id,"and (is_heading=0 or is_heading is null) and type not in ('fieldtype_action')") ;
        while ($fields = db_fetch_array($fields_query))
        {
            $fields_in_listing[$fields['id']] = fields::get_name($fields);
        }
        
        $html .= '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_FIELDS_IN_POPUP . '</label>
            <div class="col-md-9">
          	   ' . select_tag('fields_in_popup[]', $fields_in_listing, $obj['fields_in_popup'], array('class' => 'form-control input-xlarge chosen-select chosen-sortable','chosen_order'=>$obj['fields_in_popup'],'multiple'=>'multiple')) . '
            </div>
          </div>
        ';

        $html .= '
         <h3 class="form-section">' . TEXT_LIST . '</h3>  
             
          <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . tooltip_icon(TEXT_EXTNER_VALUE_IN_PERCENT_OR_PIXELS) . TEXT_WIDHT . '</label>
            <div class="col-md-9">
          	   ' . input_tag('listing_width', $obj['listing_width'], array('class' => 'form-control input-small')) . '                    
            </div>
          </div>
          
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_FIELDS_IN_LISTING . '</label>
            <div class="col-md-9">
          	   ' . select_tag('fields_in_listing[]', $fields_in_listing, $obj['fields_in_listing'], array('class' => 'form-control input-xlarge chosen-select chosen-sortable','chosen_order'=>$obj['fields_in_listing'],'multiple'=>'multiple')) . '
            </div>
          </div>
        ';
        
   
        
        $html .= '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_COLUMN_WIDHT . '</label>
            <div class="col-md-9">
          	   ' . input_tag('column_width', $obj['column_width'], array('class' => 'form-control input-large')) . '
                    ' . tooltip_text(TEXT_EXT_ENTER_COLUMN_WIDHT_IN_PP_BY_COMMA . '<br>' . TEXT_EXAMPLE . ': 30%,50,80'). '
            </div>
          </div>
                      
            <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_GROUP_BY_FIELD . '</label>
                <div class="col-md-9">
                       ' . select_tag('group_by_field', [0=>'']+$fields_in_listing, $obj['group_by_field'], array('class' => 'form-control input-xlarge chosen-select','chosen_order'=>$obj['fields_in_listing'])) . '
                </div>
            </div>
        ';                        
        
        echo $html;
        app_exit();
        
        break;
}