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

class global_vars
{
    private $vars;
    
    function __construct()
    {
        global $app_user;
        
        $this->vars = [];
        
        $vars_query = db_query("select * from app_global_vars where is_folder=0");
        while($vars = db_fetch($vars_query))
        {
            define('VAR_' . $vars->name,$vars->value);
            
            $this->vars['VAR_' . $vars->name] = $vars->value;
        }
    }
    
    function apply_to_text($text)
    {
        global $app_user;
        
        $this->vars['[current_user_id]'] = $app_user['id']??0;
        $this->vars['[current_user_group_id]'] = $app_user['group_id']??0;
        
        foreach($this->vars as $name=>$value)
        {
            $text = str_replace($name,$value,$text);
        }
        
        return $text;
    }
    
    static function get_tree($parent_id = 0, $tree = array(), $level = 0)
    {
        $vars_query = db_query("select * from app_global_vars where parent_id=" . $parent_id . " order by sort_order, name");

        while($vars = db_fetch_array($vars_query))
        {
            $vars['level'] = $level;

            $tree[] = $vars;

            $tree = self::get_tree($vars['id'], $tree, $level + 1);
        }

        return $tree;
    }
    
    static function get_folder_choices()
    {
        $choices = array();
        $choices[''] = '';

        foreach(self::get_tree() as $v)
        {
            if($v['is_folder'])
            {
                $choices[$v['id']] = str_repeat(' - ', $v['level']) . $v['name'];
            }
        }

        return $choices;
    }       
                        
}
