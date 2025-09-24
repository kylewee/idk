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

class api_entity_fields extends api
{
    public $entity_id, $field_id;
    
    function __construct()
    {
        global $app_entities_cache;
        
        $this->entity_id = (int)self::_post('entity_id');
        
        if(!isset_entity($this->entity_id))
        {
            api::response_error('Entity #' . $this->entity_id . ' does not exist');
        }        
    }
    
    function get_fields()
    {                                
        $choices = [];
        $fields_query = fields::get_query($this->entity_id);
        while($v = db_fetch_array($fields_query))
        {            
            $choices[$v['id']] = [
                'id' => $v['id'],
                'type' => $v['type'], 
                'name'=>fields_types::get_option($v['type'], 'name', $v['name']),
                'short_name' => $v['short_name'],
                'is_heading'=> $v['is_heading'],
                'tooltip'=> $v['tooltip'],
                'is_required' => $v['is_required'], 
                'required_message' => $v['required_message'],                 
                'configuration'=>$v['configuration'],
            ];
        }
        
        self::response_success($choices);
    }
    
    function get_field_choices()
    {
        global $app_fields_cache;
        
        $this->field_id = (int)self::_post('field_id');
        
        if(!isset_field($this->entity_id, $this->field_id))
        {
            api::response_error('Field #' . $this->field_id . ' does not exist in Entity #' . $this->entity_id);
        }  
        
        $cfg = new fields_types_cfg($app_fields_cache[$this->entity_id][$this->field_id]['configuration']);
        
        if($cfg->get('use_global_list')>0)
        {
            self::response_success($this->get_global_choices($cfg->get('use_global_list')));
        }
        else
        {
            self::response_success($this->get_choices());
        }
    }
    
    function get_choices($parent_id = 0, $tree = array(), $level = 0)
    {
        $choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($this->field_id) . "' and parent_id='" . db_input($parent_id) . "' and is_active=1 order by sort_order, name");
        
        while($v = db_fetch_array($choices_query))
        {            
            $tree[] = array_merge($v, array('level' => $level));

            $tree = $this->get_choices($v['id'], $tree, $level + 1);
        }

        return $tree;
    }
    
    function get_global_choices($list_id, $parent_id = 0, $tree = array(), $level = 0)
    {        
        $choices_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input($list_id) . "' and parent_id='" . db_input($parent_id) . "' and is_active=1 order by sort_order, name");
        
        while($v = db_fetch_array($choices_query))
        {            
            $tree[] = array_merge($v, array('level' => $level));

            $tree = $this->get_global_choices($list_id, $v['id'], $tree, $level + 1);
        }

        return $tree;
    }
}
