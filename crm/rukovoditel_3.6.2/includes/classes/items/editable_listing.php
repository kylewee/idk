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

class editable_listing
{
    public $entity_id;
    public $item;
    public $field;
    public $report_id;
    public $page;
    public $listing_type;
    public $entity_cfg;
    public $fields_access_schema;
    public $enabled;
    
    function __construct($entity_id, $item, $field, $fields_access_schema, $report_id, $page, $listing_type)
    {
        $this->entity_id = $entity_id;
        $this->item = $item;
        $this->field = $field;
        $this->report_id = $report_id;
        $this->page = $page;
        $this->listing_type = $listing_type;
        
        $this->entity_cfg = new entities_cfg($this->entity_id);
        $this->fields_access_schema = $fields_access_schema;
        
        $this->enabled = $this->is_enabled();                   
    }
    
    function is_enabled()
    {
        global $editable_listing_access_rules,$editable_listing_access_assigned, $cfg_tree_table_editable_fields_in_listing;
        
        
        //check if option enabled  
        if($this->listing_type=='table' and $this->entity_cfg->get('editable_fields_in_listing')!=1) return false;
        
        if($this->listing_type=='tree_table' and $cfg_tree_table_editable_fields_in_listing!=1) return false;
        
        
        
        //check allowed types
        $excluded_field_types = [
            'fieldtype_js_formula',
        ];
        
        if(in_array($this->field['type'],array_merge(fields_types::get_reserved_types(),fields_types::get_types_excluded_in_form(),$excluded_field_types)))
        {
            return false;
        }
        
        //check access to field
        if(isset($this->fields_access_schema[$this->field['id']]))
        {
            return false;
        }
        
        //check access to item
        if(!isset($editable_listing_access_rules[$this->item['id']]))
        {
            $editable_listing_access_rules[$this->item['id']] = $access_rules = new access_rules($this->entity_id, $this->item);            
        }
        else
        {
            $access_rules = $editable_listing_access_rules[$this->item['id']];            
        }
                        
        if(!users::has_access('update', $access_rules -> get_access_schema()))
        {
            return false;
        }
        
        //check view only acccess for filed
        if(in_array($this->field['id'],explode(',',$access_rules->fields_view_only_access)))
        {
            return false;
        }
        
        //check access action with selected
        if(!isset($editable_listing_access_assigned[$this->item['id']]))
        {
            $editable_listing_access_assigned[$this->item['id']] = true;            
            
            if(users::has_users_access_name_to_entity('action_with_assigned', $this->entity_id))
            {
                if(!users::has_access_to_assigned_item($this->entity_id, $this->item['id']))
                {
                    $editable_listing_access_assigned[$this->item['id']] = false;
                }
            }                        
        }
        
        if(!$editable_listing_access_assigned[$this->item['id']])
        {
            return false;
        }
        
        return true;
    }
    
    function td_css_class()
    {
        if(!$this->enabled) return '';
        
        return ' editable-cell';
    }
    
    function td_params()
    {
        if(!$this->enabled) return '';
        
        return 'data_path="' . $this->entity_id . '-' . $this->item['id'] . '" data_field_id="' . $this->field['id'] . '" data_report_id="' . $this->report_id . '" data_page="' . $this->page . '"' ;
    }
}
