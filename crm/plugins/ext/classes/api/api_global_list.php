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

class api_global_list extends api
{
    private $list_id;
    
    function get_lists()
    {
        $choices = [];
        
        $lists_query = db_fetch_all('app_global_lists','','name');
        while($v = db_fetch_array($lists_query))
        {
           $choices[] = $v;  
        }
                
        self::response_success($choices);
    }
    
    
    function get_list_choices()
    {
        $this->list_id = (int)self::_post('list_id');
        
        self::response_success($this->get_choices());
    }
    
    function get_choices($parent_id = 0, $tree = array(), $level = 0)
    {        
        $choices_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input($this->list_id) . "' and parent_id='" . db_input($parent_id) . "' and is_active=1 order by sort_order, name",false);
        
        while($v = db_fetch_array($choices_query))
        {            
            $tree[] = array_merge($v, array('level' => $level));

            $tree = $this->get_choices($v['id'], $tree, $level + 1);
        }
        
        return $tree;
    }
}
