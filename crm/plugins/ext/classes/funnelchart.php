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

class funnelchart
{
    public static function get_choices_by_entity($entities_id, $add_empty = false)
    {
        $listing_sql_query = '';
        $listing_sql_query_join = '';

        //check view assigned only access
        $listing_sql_query = items::add_access_query($entities_id, $listing_sql_query);

        //include access to parent records
        $listing_sql_query .= items::add_access_query_for_parent_entities($entities_id);

        $listing_sql_query .= items::add_listing_order_query_by_entity_id($entities_id);

        //build query
        $listing_sql = "select e.* from app_entity_" . $entities_id . " e " . $listing_sql_query_join . "where e.id>0 " . $listing_sql_query;
        $items_query = db_query($listing_sql);

        $choices = array();

        if($add_empty)
        {
            $choices[''] = '';
        }

        while($item = db_fetch_array($items_query))
        {
            $path_info = items::get_path_info($entities_id, $item['id']);

            //print_r($path_info);

            $parent_name = '';
            if(strlen($path_info['parent_name']) > 0)
            {
                $parent_name = str_replace('<br>', ' / ', $path_info['parent_name']) . ' / ';
            }

            $choices[$item['id']] = $parent_name . items::get_heading_field($entities_id, $item['id']);
        }

        return $choices;
    }
    
    static function get_color_by_choice_id($id,$colors='')
    {
        if(!strlen($colors)) return '';
        
        if($colors = json_decode($colors,true))
        {               
            return isset($colors[$id]) ? $colors[$id] : '';        
        }
        else
        {
            return '';
        }
    }

}
