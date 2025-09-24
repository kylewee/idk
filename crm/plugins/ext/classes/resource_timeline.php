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

class resource_timeline
{
    public $reports;
    public $entities_id;
    public $id;
    
    function __construct($reports)
    {
        $this->reports = $reports; 
        $this->entities_id = $reports['entities_id'];
        $this->id = $reports['id'];
    }
    
    function get_events()
    {
        global $sql_query_having, $app_user, $app_fields_cache, $app_global_choices_cache, $app_choices_cache;
        
        $events = [];
        
        foreach(json_decode($this->get_resources(),true) as $resource)
        {
            $entities_query = db_query("select * from app_ext_resource_timeline_entities where calendars_id={$this->id}");
            while($entities = db_fetch_array($entities_query))
            {
                $fields_access_schema = users::get_fields_access_schema($entities['entities_id'], $app_user['group_id']);
        
                $listing_sql_query ='';
                $listing_sql_query_having = '';
                $sql_query_having = array();

                $resources = [];
                
                $fiters_reports_id = default_filters::get_reports_id($entities['entities_id'], 'resource_timeline_entities' . $entities['id']);

                $listing_sql_query = reports::add_filters_query($fiters_reports_id, $listing_sql_query);

                $listing_sql_query = items::add_access_query($entities['entities_id'],$listing_sql_query);
                
                //extra filters
                if($fiters_panel_reports_id = reports::get_reports_id_by_type($entities['entities_id'],'resource_timeline_entity_filters_panel_' . $this->id . '_' . $entities['entities_id'],true))
                {
                   $listing_sql_query = reports::add_filters_query($fiters_panel_reports_id,$listing_sql_query); 
                }

                //prepare having query for formula fields
                if(isset($sql_query_having[$entities['entities_id']]))
                {
                    $listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[$entities['entities_id']]);
                }
                
                //start date
                if($app_fields_cache[$entities['entities_id']][$entities['start_date']]['type']!='fieldtype_dynamic_date')
                {
                  $listing_sql_query .= " and e.field_" . $entities['start_date'] . ">0";
                }

                //end date
                if($app_fields_cache[$entities['entities_id']][$entities['end_date']]['type']!='fieldtype_dynamic_date')
                {
                  $listing_sql_query .= " and e.field_" . $entities['end_date'] . ">0";
                } 
                
                //related entity
                if($entities['related_entity_field_id']>0)
                {
                    if($app_fields_cache[$entities['entities_id']][$entities['related_entity_field_id']]['type']=='fieldtype_created_by')
                    {
                        $listing_sql_query .= " and e.created_by='" . $resource['id'] . "'";
                    }
                    else
                    {
                        $listing_sql_query .= " and (select count(*) from app_entity_" . $entities['entities_id'] . "_values as cv where cv.items_id=e.id and cv.fields_id='" . $entities['related_entity_field_id'] . "' and cv.value = " . $resource['id'] . ")>0";
                    }
                }
                else
                {
                    $listing_sql_query .= " and e.parent_item_id='" . $resource['id'] . "'";
                }

                //add having query
                $listing_sql_query .= $listing_sql_query_having;

                $items_query = db_query("select e.* " . $this->get_formula_sql($entities['entities_id'], $fiters_reports_id,$fiters_panel_reports_id,$entities['heading_template'],$entities['fields_in_popup']) . " from app_entity_" . $entities['entities_id'] . " e where e.id>0 " . $listing_sql_query,false);
                while($items = db_fetch_array($items_query))
                {
                    if(strlen($entities['heading_template']) > 0)
                    {
                        $options = array(
                            'custom_pattern' => $entities['heading_template'],
                            'item' => $items);

                        $options['field']['configuration'] = '';
                        $options['field']['entities_id'] = $entities['entities_id'];

                        $fieldtype_text_pattern = new fieldtype_text_pattern();
                        $title = $fieldtype_text_pattern->output($options);
                    }
                    else
                    {
                        $title = items::get_heading_field($entities['entities_id'], $items['id']);
                    }
                    
                    $start = date('Y-m-d H:i',$items['field_' . $entities['start_date']]);
                    $end = date('Y-m-d H:i',$items['field_' . $entities['end_date']]);
                    
                    if(strstr($end, ' 00:00'))
                    {
                        $end = date('Y-m-d H:i', strtotime('+1 day', $items['field_' . $entities['end_date']]));
                    }

                    //color
                    $color = (strlen($entities['bg_color']) ? $entities['bg_color'] : '#3a87ad');
                    if($entities['use_background'] > 0)
                    {
                        if(isset($items['field_' . $entities['use_background']]))
                        {
                            $value_id = $items['field_' . $entities['use_background']];

                            $cfg = new fields_types_cfg($app_fields_cache[$entities['entities_id']][$entities['use_background']]['configuration']);

                            if($cfg->get('use_global_list') > 0)
                            {
                                $choices_cache = $app_global_choices_cache;
                            }
                            else
                            {
                                $choices_cache = $app_choices_cache;
                            }

                            if(isset($choices_cache[$value_id]))
                            {
                                if(strlen($choices_cache[$value_id]['bg_color']) > 0)
                                {
                                    $color = $choices_cache[$value_id]['bg_color'] ;                                    
                                }
                            }
                        }
                    }

                    //description popup
                    //prepare description
                    $description = '';

                    if(strlen($entities['fields_in_popup']))
                    {
                        $path_info = items::get_path_info($entities['entities_id'],$items['id']);
                        $description .= '<table class="popover-table">';

                        foreach(explode(',', $entities['fields_in_popup']) as $fields_id)
                        {
                            $field_query = db_query("select * from app_fields where id='" . $fields_id . "'");
                            if($field = db_fetch_array($field_query))
                            {
                                //prepare field value
                                $value = items::prepare_field_value_by_type($field, $items);

                                $output_options = array('class' => $field['type'],
                                    'value' => $value,
                                    'field' => $field,
                                    'item' => $items,
                                    'path_info'=>$path_info,
                                    'is_export' => true,
                                    'path' => '');

                                $value = trim(strip_tags(fields_types::output($output_options)));

                                if(strlen($value) > 255)
                                    $value = mb_substr($value, 0, 255) . '...';

                                if(strlen($value))
                                {
                                    $description .= '
                                            <tr>
                                                    <th>' . ($field['short_name'] ? $field['short_name'] : fields_types::get_option($field['type'], 'name', $field['name'])) . '</th>
                                                    <td>' . $value . '</td>
                                            </tr>';
                                }
                            }
                        }
                        $description .= '</table>';

                        $description = str_replace(["\n", "\r", "\r\n", "\t"], '', $description);
                    }
            
                    $events[] = [
                        'resourceId'=>$resource['id'],
                        'extendedProps'=>[
                           'item_id' => $items['id'],
                            'entities_id' => $entities['entities_id'],
                            'reports_entities_id'=>$entities['id'], 
                        ],                        
                        'title'=>$title,
                        'description' => $description,
                        'start' => str_replace(' 00:00','',$start),
                        'end' => str_replace(' 00:00','',$end),
                        'url' => url_for('items/info','path=' . $entities['entities_id'] . '-' . $items['id']),                        
                        'backgroundColor'=> $color,
                        'borderColor' => $color,
                        'editable'=>$this->is_event_editable($entities),
                        'durationEditable'=>$this->is_event_duration_editable($entities),
                    ];
                }
            }
        }
        
        return json_encode($events);
    }
    
    function is_event_editable($entities)
    {
        global $app_fields_cache;
        
        $start_date_field = $app_fields_cache[$entities['entities_id']][$entities['start_date']];
        $end_date_field = $app_fields_cache[$entities['entities_id']][$entities['end_date']];
        
        if($start_date_field['type']=='fieldtype_dynamic_date' or $end_date_field['type']=='fieldtype_dynamic_date')
        {
            return false;
        }
        elseif(self::has_access($this->reports['users_groups'],'full'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function is_event_duration_editable($entities)
    {               
        if($entities['start_date']==$entities['end_date'])
        {
            return false;
        }
        elseif(self::has_access($this->reports['users_groups'],'full'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function get_formula_sql($entities_id, $fiters_reports_id,$fiters_panel_reports_id, $heading_template, $fields_in_popup)
    {
        $fields_in_query = [];
        
        $filters_query = db_query("select fields_id from app_reports_filters where reports_id = {$fiters_reports_id}" . ($fiters_panel_reports_id ? " or reports_id={$fiters_panel_reports_id}":'') );
        while($filters = db_fetch_array($filters_query))
        {
            $fields_in_query[] = $filters['fields_id'];
        }                
        
        if(strstr($heading_template,'['))
        {
            if(preg_match_all('/\[(\d+)\]/', $heading_template, $output_array))
            {
                foreach($output_array[1] as $id)
                {
                    $fields_in_query[] = $id;
                }
            }
        }
        
        if(strlen($fields_in_popup))
        {            
            foreach(explode(',',$fields_in_popup) as $id)
            {
                $fields_in_query[] = $id;
            }            
        }
                
        //print_rr($fields_in_query);
        //exit();
        
        return fieldtype_formula::prepare_query_select($entities_id,'',false,['fields_in_query'=>implode(',',$fields_in_query)]);
    }
    
    function get_resources()
    {
        global $sql_query_having, $app_user;
        
        $fields_access_schema = users::get_fields_access_schema($this->entities_id, $app_user['group_id']);
        
        $listing_sql_query ='';
        $listing_sql_query_having = '';
        $sql_query_having = array();
                        
        $resources = [];
        
        $fiters_reports_id = default_filters::get_reports_id($this->entities_id, 'resource_timeline' . $this->id);
        
        $listing_sql_query = reports::add_filters_query($fiters_reports_id,$listing_sql_query);
			 		
	$listing_sql_query = items::add_access_query($this->entities_id,$listing_sql_query);
        
        if($fiters_panel_reports_id = reports::get_reports_id_by_type($this->entities_id,'resource_timeline_filters_panel_' . $this->id,true))
        {
           $listing_sql_query = reports::add_filters_query($fiters_panel_reports_id,$listing_sql_query); 
        }
        
        //prepare having query for formula fields
        if(isset($sql_query_having[$this->entities_id]))
        {
            $listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[$this->entities_id]);
        }
        
        //add having query
	$listing_sql_query .= $listing_sql_query_having;
                                        
        $items_query = db_query("select e.* " . $this->get_formula_sql($this->entities_id, $fiters_reports_id,$fiters_panel_reports_id,$this->reports['heading_template'],$this->reports['fields_in_popup']) . " from app_entity_" . $this->entities_id . " e  where e.id>0 " . $listing_sql_query,false);
        while($items = db_fetch_array($items_query))
        {
            if(strlen($this->reports['heading_template']) > 0)
            {
                $options = array(
                    'custom_pattern' => $this->reports['heading_template'],
                    'item' => $items);

                $options['field']['configuration'] = '';
                $options['field']['entities_id'] = $this->reports['entities_id'];

                $fieldtype_text_pattern = new fieldtype_text_pattern();
                $title = $fieldtype_text_pattern->output($options);
            }
            else
            {
                $title = items::get_heading_field($this->reports['entities_id'], $items['id']);
            }

            $resource = [
                'id' =>$items['id'],
                'title' => $title,
            ];
            
            $resource_fields = self::get_report_fields($this->reports);
            
            if($this->reports['group_by_field']>0)
            {
                $field_query = db_query("select * from app_fields where id={$this->reports['group_by_field']}");
                if($field = db_fetch_array($field_query))
                {
                    $resource_fields[] = $field; 
                }

            }
            
            foreach($resource_fields as $field)
            {                                
                $options = array(
                    'custom_pattern' => '[' . $field['id'] . ']',
                    'item' => $items,
                    'field'=>['configuration'=>'','entities_id'=>$this->reports['entities_id']]);
                
                $fieldtype_text_pattern = new fieldtype_text_pattern();
                $value = $fieldtype_text_pattern->output($options);
                                
                $resource['field_' . $field['id']] = $value;
            }
            
            //prepare popup
            $html = '';
            if(strlen($this->reports['fields_in_popup']))
            {
                $fields_in_popup = fields::get_items_fields_data_by_id($items, $this->reports['fields_in_popup'], $this->entities_id, $fields_access_schema);
                if(count($fields_in_popup))
                {
                    $html = '<table class="popover-table">';
                    
                    foreach($fields_in_popup as $v)
                    {
                        $html .= '
                            <tr>
                                <th>' . $v['name'] . '</th>
                                <td>' . $v['value'] . '</td>
                            </tr>';
                    }
                    
                    $html .= '</table>';
                }                
            }
            
        $resource['extendedProps'] = ['popup'=>$html];
                    
            $resources[] = $resource;
        }
        
        return json_encode($resources);
    }
    
    static function get_resources_group_field($reports)
    {
        $value = '';
        if($reports['group_by_field']>0)
        {
            $value = 'field_' . $reports['group_by_field'];
        }
        
        return $value;
    }
    
    static function get_resources_order($reports)
    {
        $order = [];
        
        if($reports['group_by_field']>0)
        {
            $order[] = 'field_' . $reports['group_by_field'];
        }
        
        $order[] = 'title';
        
        return implode(',',$order);
    }
    
    static function get_resources_columns($reports)
    {
        $columns = [];
        
        $columns[] = '
                {
                   headerContent: "' . entities::get_name_by_id($reports['entities_id']). '",
                   field: "title"
                }
            ';
        
        $column_width = (strlen($reports['column_width']) ? explode(',',$reports['column_width']):[]);
        
        foreach(self::get_report_fields($reports) as $k=>$field)
        {            
            $columns[] = '
                {
                   headerContent: "' . fields::get_name($field) . '",
                   field: "field_' . $field['id'] . '",
                   width: "' . (isset($column_width[$k]) ? $column_width[$k] : '') . '",
                }
            ';
        }
        
        return '[' . implode(',',$columns). ']';
        
    }
    
    static function get_report_fields($reports,$type='fields_in_listing')
    {
        global $app_fields_cache, $app_user;
        
        $fields_in_listing  = [];
        
        $fields_access_schema = users::get_fields_access_schema($reports['entities_id'], $app_user['group_id']);
        
        if(strlen($reports[$type]))
        {
            $fields_query = db_query("select id,name,type from app_fields where id in ({$reports[$type]}) and entities_id={$reports['entities_id']} order by field(id,{$reports[$type]})",false);
            while($fields = db_fetch_array($fields_query))
            {
                if (isset($fields_access_schema[$fields['id']]) and $fields_access_schema[$fields['id']] == 'hide') continue;
                    
                $fields_in_listing[] = $fields;
            }           
        }
        
        
        
        //print_rr($fields);
        
        return $fields_in_listing;
    }
    
    static function get_area_width($reports)
    {
        return (strlen($reports['listing_width']) ? $reports['listing_width'] : '30%');
    }
    
    static function get_height()
    {
        global $app_module_path;
        
        if($app_module_path=='ext/resource_timeline/view')
        {
            return 'get_resource_timeline_height()';
        }
        else
        {
            return 450;
        }
    }
    
    static public function get_default_view_choices()
    {
        $choices = [];        
        $choices['timelineYear'] = TEXT_EXT_YEAR;
        $choices['timelineYear2'] = TEXT_EXT_YEAR . '(2)';
        $choices['timelineMonth'] = TEXT_EXT_MONTH;
        $choices['timelineMonth2'] = TEXT_EXT_MONTH . ' (2)';
        $choices['timelineMonth3'] = TEXT_EXT_MONTH . ' (3)';
        $choices['timelineWeek'] = TEXT_EXT_WEEK;
        $choices['timelineWeek6'] = TEXT_EXT_WEEK . '(4)';
        $choices['timelineDay'] = TEXT_EXT_DAY;
        $choices['agendaDay'] = TEXT_EXT_DAY . ' (' . TEXT_EXT_VERTICAL_VIEW . ')';
        
        
        return $choices;
    }
    
    static function get_default_view($reports)
    {
        return (strlen($reports['default_view']) ? self::getInitialView($reports['default_view']) :'');
    }
    
    static function getInitialView($mode)
    {
        switch($mode)
        {
            case 'timelineDay':
                $mode = 'resourceTimelineDay';
                break;
            case 'timelineWeek':
                $mode = 'resourceTimelineWeek';
                break;   
            case 'timelineMonth':
                $mode = 'resourceTimelineMonth';
                break;
            case 'timelineYear':
                $mode = 'timelineYear1';
                break;
            case 'timelineWeek6':
                $mode = 'timelineWeek6';
                break;
            case 'agendaDay':
                $mode = 'resourceTimeGridDay';
                break;
            default:
                $mode = 'timelineMonth';
                break;
        }
        return $mode;
    }
    
    static function get_view_modes($reports)
    {
        $modes = (strlen($reports['view_modes']) ? explode(',',$reports['view_modes']) :[]);
        
        if(count($modes))
        {
            $modes = array_map(function($v){
              switch($v)
              {                  
                  case 'timelineDay':
                      return 'resourceTimelineDay';
                      break;
                  case 'timelineWeek':
                      return 'resourceTimelineWeek';
                      break;
                  case 'timelineMonth':
                      return 'resourceTimelineMonth';
                      break;                  
                  case 'timelineYear':
                      return 'timelineYear1';
                      break;
                  case 'agendaDay':
                    return 'resourceTimeGridDay';
                    break;
                  default:
                      return $v;
                      break;
              }
            },$modes);
        }
        
        return implode(',',$modes);
    }
    
    static function get_slot_duration($reports)
    {
        return (strlen($reports['time_slot_duration']) ? $reports['time_slot_duration'] :'00:30:00');
    }
    
    static function get_calendar_id_by_calendar_entity($id)
    {
        $info_query = db_query("select calendars_id from app_ext_resource_timeline_entities where id='" . $id . "'");
        $info = db_fetch_array($info_query);

        return $info['calendars_id'];
    }

    static function get_add_url($reports)
    {
        global $app_entities_cache;
        
        $count_entities_with_access = 0;
        $use_entities_id = 0;
        $use_timeline_entities_id = 0;
        
        $entities_query = db_query("select ce.*, e.name from app_ext_resource_timeline_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "'");
        while($entities = db_fetch_array($entities_query))
        {
            if(users::has_users_access_name_to_entity('create',$entities['entities_id']))
            {
                $count_entities_with_access++;
                $use_entities_id = $entities['entities_id'];
                $use_timeline_entities_id = $entities['id'];
            } 
        }                
        
        if($count_entities_with_access==1)
        {   
            $reports_info_query = db_query("select * from app_reports where entities_id='" . $use_entities_id . "' and reports_type='resource_timeline_entities" . $use_timeline_entities_id . "'");
            $reports_info = db_fetch_array($reports_info_query);
            
            if($app_entities_cache[$use_entities_id]['parent_id']>0)
            {
                $params = '';
                if($app_entities_cache[$use_entities_id]['parent_id']==$reports['entities_id'])
                {
                    $params .= "&parent_item_id={$reports['entities_id']}-resource_id";
                }
                
                $add_url = url_for("reports/prepare_add_item","redirect_to=resource_timeline" . $use_timeline_entities_id . "&reports_id=" . $reports_info['id'] . $params);
            }
            else
            {
                $add_url = url_for("items/form","redirect_to=resource_timeline" . $use_timeline_entities_id . "&path=" . $use_entities_id);
            }

        }
        else
        {
            $add_url = url_for('ext/resource_timeline/add_item','id=' . $reports['id']);
        }
        
        return $add_url;
    }
    
    static function is_selectable($reports)
    {
        global $app_user, $app_fields_cache;
        
        //check if fieldtype_created_by used
        $entities_query = db_query("select ce.*, e.name from app_ext_resource_timeline_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "'");
        while($entities = db_fetch_array($entities_query))
        {
            if($entities['related_entity_field_id']>0 and $app_fields_cache[$entities['entities_id']][$entities['related_entity_field_id']]['type']=='fieldtype_created_by')
            {
                return 0;
            }
        }
                          
        //check if admin
        if ($app_user['group_id'] == 0)
            return true;
      
        //check if there access to entities
        $entities_query = db_query("select ce.*, e.name from app_ext_resource_timeline_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "'");
        while($entities = db_fetch_array($entities_query))
        {            
            if(users::has_users_access_name_to_entity('create',$entities['entities_id']))
            {
                return true;
            }
        }
        
        return 0;
    }

    static function has_access($users_groups, $access = false)
    {
        global $app_user;

        if ($app_user['group_id'] == 0)
            return true;

        if (strlen($users_groups))
        {
            $users_groups = json_decode($users_groups, true);

            if (!$access)
            {
                if (isset($users_groups[$app_user['group_id']]))
                {
                    return (strlen($users_groups[$app_user['group_id']]) ? true : false);
                }
            }
            else
            {
                if (isset($users_groups[$app_user['group_id']]))
                {
                    return ($users_groups[$app_user['group_id']] == $access ? true : false);
                }
            }
        }

        return false;
    }
    
    static public function render_legend($reports)
    {
        $html = '';

        if($reports['display_legend'] == 1)
        {
            $html .= '<ul class="list-inline">';

            $items_query = db_query("select ce.*, e.name from app_ext_resource_timeline_entities ce, app_entities e where length(ce.bg_color)>0 and e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "' order by e.name");
            while($items = db_fetch_array($items_query))
            {
                $html .= '<li style="color: ' . $items['bg_color'] . '"><i class="fa fa-square" aria-hidden="true"></i> ' . $items['name'] . '</li>';
            }

            $html .= '</ul>';
        }

        return $html;
    }
    
    static function render_filters_panel($reports)
    {
        $filters_panel_type = 'resource_timeline_filters_panel_' . $reports['id'];
        $filters_panel_id = filters_panels::get_id_by_type($reports['entities_id'], $filters_panel_type);
        
        $count_query = db_query("select count(*) as total from app_filters_panels_fields where panels_id='{$filters_panel_id}'");
        $count = db_fetch_array($count_query);
        
        if($count['total']==0)
        {
            return '';
        }
        
        $fiters_reports_id = reports::auto_create_report_by_type($reports['entities_id'],$filters_panel_type,true);
                
        $filters_panels = new filters_panels($reports['entities_id'],$fiters_reports_id,'',0);
        $filters_panels->set_type($filters_panel_type);
        $filters_panels->set_items_listing_funciton_name('refetch_resource_timeline');
        return '
            <div class="' . $filters_panel_type . '">' . $filters_panels->render_horizontal() . '</div>
            <script>
                function refetch_resource_timeline()
                {                    
                    resource_timeline' . $reports['id'] . '.refetchResources()   
                    resource_timeline' . $reports['id'] . '.refetchEvents()   
                }
            </script>
            ';
    }
    
    static function render_entity_filters_panel($reports)
    {
        $html = '';
        
        $timeline_entities_query = db_query("select * from app_ext_resource_timeline_entities where calendars_id={$reports['id']}");
        while($timeline_entities = db_fetch_array($timeline_entities_query))
        {
            $filters_panel_type = 'resource_timeline_entity_filters_panel_' . $reports['id'] . '_' . $timeline_entities['entities_id'];
            $filters_panel_id = filters_panels::get_id_by_type($timeline_entities['entities_id'], $filters_panel_type);

            $count_query = db_query("select count(*) as total from app_filters_panels_fields where panels_id='{$filters_panel_id}'");
            $count = db_fetch_array($count_query);

                if($count['total']>0)
                {                 
                    $fiters_reports_id = reports::auto_create_report_by_type($timeline_entities['entities_id'],$filters_panel_type,true);

                    $filters_panels = new filters_panels($timeline_entities['entities_id'],$fiters_reports_id,'',0);
                    $filters_panels->set_type($filters_panel_type);
                    $filters_panels->set_items_listing_funciton_name('refetch_resource_timeline_entity' . $timeline_entities['entities_id']);
                    $html .= '
                        <div class="' . $filters_panel_type . '">' . $filters_panels->render_horizontal() . '</div>
                        <script>
                            function refetch_resource_timeline_entity' . $timeline_entities['entities_id'] . '()
                            {                                                               
                                resource_timeline' . $reports['id'] . '.refetchEvents() 
                            }
                        </script>
                        ';
                }
        }
        
        return $html;
    }

}
