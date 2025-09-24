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

class kanban
{

    static function get_items_html($data = [])
    {
        global $app_path, $app_entities_cache;
        
        $choices_id = $data['choices_id'];
        $reports = $data['reports'];
        $fiters_reports_id = $data['fiters_reports_id'];
        $listing_highlight = $data['listing_highlight'];
        $is_kanban_sotrtable = $data['is_kanban_sotrtable'];
        
        $parent_entity_id = $app_entities_cache[$reports['entities_id']]['parent_id'];
        $is_top_kanban = ($parent_entity_id and !strstr($app_path,'/')) ? true:false;
        //$kanban_info_choices = $data['kanban_info_choices'];
        
        $items_html = '<ul id="kanban_choice_' . $choices_id . '" class="kanban-sortable">';

        $listing_sql = kanban::get_items_query($reports['group_by_field'] . ':' . $choices_id, $reports, $fiters_reports_id);
        $listing_split = new split_page($listing_sql,$choices_id,'query_num_rows',($reports['rows_per_page']>0 ? $reports['rows_per_page'] : 20));
        $listing_split->listing_funciton = 'load_kanban_report' . $reports['id']. '_items';
        $items_query = db_query($listing_split->sql_query);
        while($items = db_fetch_array($items_query))
        {            
            //prepare description
            $description = '';

            if(strlen($reports['fields_in_listing']))
            {
                $description .= '<table class="kanban-fields-in-listing">';

                foreach(explode(',', $reports['fields_in_listing']) as $fields_id)
                {
                    $field_query = db_query("select * from app_fields where id='" . $fields_id . "' order by field(id," . $reports['fields_in_listing'] . ")");
                    if($field = db_fetch_array($field_query))
                    {
                        //prepare field value
                        $value = items::prepare_field_value_by_type($field, $items);

                        $output_options = array('class' => $field['type'],
                            'value' => $value,
                            'field' => $field,
                            'item' => $items,
                            'is_listing' => true,
                            'redirect_to' => 'kanban' . $reports['id'],
                            'reports_id' => 0,
                            'path' => $app_path . '-' . $items['id']);

                        $value = trim(fields_types::output($output_options));

                        if(strlen($value) > 255 and in_array($field['type'], ['fieldtype_textarea_wysiwyg', 'fieldtype_textarea']))
                            $value = substr(strip_tags($value), 0, 255) . '...';

                        if(strlen($value))
                        {
                            $description .= '
		        			<tr class="kanban-field-' . $field['id'] . '">
		        				<td  class="kanban-field-name" valign="top" style="padding-right: 7px;">' . fields_types::get_option($field['type'], 'name', $field['name']) . '</td>
		        				<td valign="top">' . $value . '</td>
		        			</tr>';
                        }
                    }
                }
                $description .= '</table>';
            }

            //prepare title
            if(strlen($reports['heading_template']) > 0)
            {
                $fieldtype_text_pattern = new fieldtype_text_pattern();
                $title = $fieldtype_text_pattern->output_singe_text($reports['heading_template'], $reports['entities_id'], $items);
            }
            else
            {
                $title = items::get_heading_field($reports['entities_id'], $items['id'], $items);
            }

            //add proejct name to title
            $redirect_to = 'kanban';
            if($is_top_kanban)
            {
                $title = '<small>' . items::get_heading_field($parent_entity_id, $items['parent_item_id']) . '</small><br>' . $title;
                $redirect_to = 'kanban-top';
            }

            $action_buttons = '<div class="kanban-actions-buttons">';

            $access_rules = new access_rules($reports['entities_id'], $items);

            if(users::has_access('update', $access_rules->get_access_schema()))
            {
                $action_buttons .= '<a href="#"  onClick="open_dialog(\'' . url_for('items/form', 'id=' . $items['id'] . '&path=' . $app_path . '&redirect_to=' . $redirect_to . $reports['id']) . '\')"><i class="fa fa-edit"></i></a>';
            }

            if(users::has_access('delete', $access_rules->get_access_schema()))
            {
                $check = true;

                if(users::has_access('delete_creator', $access_rules->get_access_schema()) and $items['created_by'] != $app_user['id'])
                {
                    $check = false;
                }

                if($check)
                {
                    $action_buttons .= '<a href="#"  onClick="open_dialog(\'' . url_for('items/delete', 'id=' . $items['id'] . '&entity_id=' . $reports['entities_id'] . '&path=' . $app_path . '&redirect_to=' . $redirect_to . $reports['id']) . '\')"><i class="fa fa-trash-o"></i></a>';
                }
            }

            $action_buttons .= '</div>';

            //reset actions buttons if no access
            if(users::has_users_access_name_to_entity('action_with_assigned', $reports['entities_id']))
            {
                if(!users::has_access_to_assigned_item($reports['entities_id'], $items['id']))
                {
                    $action_buttons = '';
                }
            }

            $items_html .= '
	  		<li id="kanban_item_' . $items['id'] . '" class="kanban-item ' . $listing_highlight->apply($items) . '" ' . (!$is_kanban_sotrtable ? 'style="cursor:default"' : '') . '>
  				' . $action_buttons . '	
  				<a class="kanban-item-title" href="' . url_for('items/info', 'path=' . $app_path . '-' . $items['id']) . '" target="_blank">' . $title . '</a>
  				' . $description . '
	  		</li>	
  		';
        }
        
        if($listing_split->number_of_pages>1)
        {
            $items_html .= '
                <li class="li-pagination-kanban">
                    <center>' . $listing_split->display_links_sm() . '</center>                    
                </li>
                ';                                                               
        }

        $items_html .= '</ul>';
        
        

        return $items_html;
    }

    static function get_items_query($force_filter_by, $reports, $fiters_reports_id)
    {
        global $sql_query_having;

        $listing_sql_query = '';
        $listing_sql_query_select = '';
        $listing_sql_query_having = '';
        $listing_sql_query_join = '';
        $sql_query_having = array();

        //filter items by parent
        /* if($parent_entity_item_id>0)
          {
          $listing_sql_query .= " and e.parent_item_id='" . db_input($parent_entity_item_id) . "'";
          } */

        //prepare forumulas query
        $listing_sql_query_select = fieldtype_formula::prepare_query_select($reports['entities_id'], $listing_sql_query_select);

        //prepare filters
        if($reports['filters_panel']=='default')
        {
            $listing_sql_query = reports::add_filters_query($fiters_reports_id, $listing_sql_query);
        }
        elseif($reports['filters_panel']=='quick_filters')
        {
            $panel_fiters_reports_id = reports::auto_create_report_by_type($reports['entities_id'], 'panel_kanban_reports' . $reports['id'], true);
            $listing_sql_query = reports::add_filters_query($panel_fiters_reports_id, $listing_sql_query);
        }
        
        //default filters
        $default_fiters_reports_id = default_filters::get_reports_id($reports['entities_id'], 'default_kanban_reports' . $reports['id']);
        $listing_sql_query = reports::add_filters_query($default_fiters_reports_id, $listing_sql_query);

        //prepare having query for formula fields
        if(isset($sql_query_having[$reports['entities_id']]))
        {
            $listing_sql_query_having = reports::prepare_filters_having_query($sql_query_having[$reports['entities_id']]);
        }

        if(isset($_GET['path']))
        {
            $path_info = items::parse_path($_GET['path']);
            if($path_info['parent_entity_item_id'] > 0)
            {
                $listing_sql_query .= " and e.parent_item_id='" . $path_info['parent_entity_item_id'] . "'";
            }
        }

        
        $listing_sql_query .= reports::force_filter_by($force_filter_by);
        

        //check view assigned only access
        $listing_sql_query = items::add_access_query($reports['entities_id'], $listing_sql_query);

        $listing_sql_query .= items::add_access_query_for_parent_entities($reports['entities_id']);

        //add having query
        $listing_sql_query .= $listing_sql_query_having;

        //add order_query
        $order_info_query = db_query("select listing_order_fields from app_reports where id='" . $fiters_reports_id . "'");
        $order_info = db_fetch_array($order_info_query);
        if(strlen($order_info['listing_order_fields']) > 0)
        {
            $info = reports::add_order_query($order_info['listing_order_fields'], $reports['entities_id']);

            $listing_sql_query .= str_replace('order by','order by e.parent_item_id,',$info['listing_sql_query']);
            $listing_sql_query_join .= $info['listing_sql_query_join'];
        }
        else
        {
            $listing_sql_query .= " order by e.parent_item_id";
        }

        $items_sql_query = "select e.* {$listing_sql_query_select} from app_entity_" . $reports['entities_id'] . " e " . $listing_sql_query_join . " where e.id>0 " . $listing_sql_query;

        return $items_sql_query;
    }

}
