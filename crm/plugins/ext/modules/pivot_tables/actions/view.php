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
$pivot_tables_query = db_query("select * from app_ext_pivot_tables where id='" . _GET('id') . "'");
if(!$pivot_tables = db_fetch_array($pivot_tables_query))
{
    redirect_to('dashboard/page_not_found');
}

app_set_title($pivot_tables['name']);

$pivot_table = new pivot_tables($pivot_tables);

if(!$pivot_table->has_access())
{
    redirect_to('dashboard/access_forbidden');
}

$fiters_reports_id = $pivot_table->get_fiters_reports_id();

switch($app_module_action)
{
    case 'set_report':
        
        if(!$pivot_table->has_access('full')) exit();
        
        $use_user_id = ($app_user['group_id']==0 ? 0 : $app_user['id']);
        
        $settings_query = db_query("select * from  app_ext_pivot_tables_settings where  reports_id='" . $pivot_table->id . "' and users_id='" . $use_user_id . "'");
        if($settings = db_fetch_array($settings_query))
        {
            db_query("update app_ext_pivot_tables_settings set settings='" . db_input($_POST['settings']) . "' where id='" . $settings['id'] . "'"); 
        }
        else
        {
            $sql_data = array(                
                'reports_id'=>$pivot_table->id,
                'users_id'=>$use_user_id,
                'settings'=>$_POST['settings'],               
            );
            
            db_perform('app_ext_pivot_tables_settings',$sql_data);
        }
                
        exit();
        break;
    case 'get_csv':
        
        header("Content-type: text/csv");
        
        //get fields
        $reports_fields_info = $pivot_table->get_fields_by_entity($pivot_table->entities_id);
        $reports_fields = $reports_fields_info['reports_fields'];
        $reports_fields_names = $reports_fields_info['reports_fields_names'];
        $reports_fields_dates_format = $reports_fields_info['reports_fields_dates_format'];
                
        //get parent entities
        $parrent_entities = entities::get_parents($pivot_table->entities_id);
        
        $output_array = array();
        $listing_fields = array();
        $listing_fields_id = array();
        $parent_entities_listing_fields = array();
        $parent_entities_fields_dates_format = array();
        
        //adding fields
        if(count($reports_fields))
        {
            $fields_query = db_query("select * from app_fields where id in (" . implode(',',$reports_fields). ") order by id");
            while($fields = db_fetch_array($fields_query))
            {
                $listing_fields[] = $fields;
                $listing_fields_id[] = $fields['id'];
                $name = (isset($reports_fields_names[$fields['id']]) ? $reports_fields_names[$fields['id']] : fields_types::get_option($fields['type'],'name',$fields['name']));
                $output_array[] = pivot_tables::css_prepare($name);
            }
        }
        
        //added parent entities fields
        if(count($parrent_entities)>0)
        {
            foreach($parrent_entities as $entities_id)
            {
                $reports_fields_info = $pivot_table->get_fields_by_entity($entities_id);
                $reports_fields = $reports_fields_info['reports_fields'];
                $reports_fields_names = $reports_fields_info['reports_fields_names'];
                
                //prepare fields dates format
                $parent_entities_fields_dates_format = $parent_entities_fields_dates_format+$reports_fields_info['reports_fields_dates_format'];
                
                if(count($reports_fields))
                {
                    $fields_query = db_query("select f.*, e.name as entity_name from app_fields f left join app_entities e on f.entities_id=e.id where f.id in (" . implode(',',$reports_fields). ") order by f.id");
                    while($fields = db_fetch_array($fields_query))
                    {
                        $parent_entities_listing_fields[$entities_id][] = $fields;
                        
                        $name = (isset($reports_fields_names[$fields['id']]) ? $reports_fields_names[$fields['id']] : $fields['entity_name'] . ': ' . fields_types::get_option($fields['type'],'name',$fields['name']));
                        $output_array[] =  pivot_tables::css_prepare($name);
                    }
                }
            }
        }
        
        //output heading
        echo pivot_tables::array_to_csv($output_array);
        
        //build items listing
        $listing_sql_query = '';
        $listing_sql_query_select = '';
        $listing_sql_query_having = '';
        $sql_query_having = array();
        
        $output_parent_array = [];
        
        //prepare forumulas query
        $listing_sql_query_select = fieldtype_formula::prepare_query_select($pivot_table->entities_id, $listing_sql_query_select,false,['fields_in_query'=>implode(',',$listing_fields_id)]);
        
        //prepare filters
        $listing_sql_query = reports::add_filters_query($fiters_reports_id,$listing_sql_query);
        
        if($pivot_tables['filters_panel']=='default')
        {
            $fiters_reports_id = reports::auto_create_report_by_type($pivot_tables['entities_id'],'pivot_tables_filters' . $pivot_tables['id'],true);
            $listing_sql_query = reports::add_filters_query($fiters_reports_id,$listing_sql_query);
        }
        elseif($pivot_tables['filters_panel']=='quick_filters')
        {
            $fiters_reports_id = reports::auto_create_report_by_type($pivot_tables['entities_id'],'pivot_tables' . $pivot_tables['id'],true);
            $listing_sql_query = reports::add_filters_query($fiters_reports_id,$listing_sql_query);
        }
        
        //prepare having query for formula fields
        if(isset($sql_query_having[$pivot_table->entities_id]))
        {
            $listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[$pivot_table->entities_id]);
        }
        
        //check view assigned only access
        $listing_sql_query = items::add_access_query($pivot_table->entities_id,$listing_sql_query);
        
        //include access to parent records
        $listing_sql_query .= items::add_access_query_for_parent_entities($pivot_table->entities_id);
        
        $items_sql_query = "select * {$listing_sql_query_select} from app_entity_" . $pivot_table->entities_id . " e where id>0 " . $listing_sql_query . $listing_sql_query_having;
        $items_query = db_query($items_sql_query,false);        
        while($item = db_fetch_array($items_query))
        {
            $output_array = array();
            
            foreach($listing_fields as $field)
            {
                $value = items::prepare_field_value_by_type($field, $item);
                
                //use custom date format for for dates
                if(in_array($field['type'],array('fieldtype_date_added','fieldtype_input_date','fieldtype_input_date_extra','fieldtype_input_datetime','fieldtype_dynamic_date')) and isset($reports_fields_dates_format[$field['id']]) and (int)$value>0)
                {
                    $output_array[] = pivot_tables::css_prepare(i18n_date($reports_fields_dates_format[$field['id']],$value));
                }
                else
                {
                    $output_options = array(
                        'class'=>$field['type'],
                        'value'=>$value,
                        'field'=>$field,
                        'item'=>$item,
                        'is_export'=>true,
                        'reports_id'=> $fiters_reports_id,
                        'path'=> '',
                        'path_info' => '');
                    
                    $output_value = trim(fields_types::output($output_options));
                    
                    switch($field['type'])
                    {
                        case 'fieldtype_formula':
                        case 'fieldtype_input_numeric':
                        case 'fieldtype_input_numeric_comments':
                            $output_array[] = !strlen($output_value) ? 0 : $output_value;
                            break;
                        default:
                            $output_array[] = pivot_tables::css_prepare($output_value);
                            break;
                    }
                    
                    
                }
            }
            
            //prepare parent output if exist
            if(count($parent_entities_listing_fields)>0)
            {
                if(!isset($output_parent_array[$item['parent_item_id']]))
                {                                
                    $output_parent_array[$item['parent_item_id']] = pivot_tables::prepare_csv_output_for_parent_entities([],$parent_entities_listing_fields,$parrent_entities,$item['parent_item_id'],$parent_entities_fields_dates_format);
                }
                
                $output_array = array_merge($output_array,$output_parent_array[$item['parent_item_id']]);
            }
            
            //output items
            echo pivot_tables::array_to_csv($output_array);
        }
        
        app_exit();
        break;
}