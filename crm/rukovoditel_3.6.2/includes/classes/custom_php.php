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

class custom_php
{        
    static function include()
    {             
        $exclude_code_id = false;
        
        //skip in custom_php page
        if(isset($_SERVER['REQUEST_URI']) and strstr($_SERVER['REQUEST_URI'],'module=custom_php/code') and isset($_POST['code_id']) and $_POST['code_id']>0)
        {
            $exclude_code_id = _POST('code_id');
        }
        
        $code_query = db_query("select * from app_custom_php where is_folder=0 and is_active=1 " . ($exclude_code_id ? " and id!=" . $exclude_code_id : "") );
        while($code = db_fetch($code_query))
        {
            if(strlen($code->code))
            {
                try
                {                                     
                    eval($code->code);
                }
                catch (Error $e)
                {
                    //skip code with erros
                } 
            }
        }
    }
        
    static function get_tree($parent_id = 0, $tree = array(), $level = 0)
    {
        $code_query = db_query("select * from app_custom_php where parent_id=" . $parent_id . " order by sort_order, name");

        while($code = db_fetch_array($code_query))
        {
            $code['level'] = $level;

            $tree[] = $code;

            $tree = self::get_tree($code['id'], $tree, $level + 1);
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
