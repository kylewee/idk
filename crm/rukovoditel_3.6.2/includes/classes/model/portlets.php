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

class portlets
{
    public $is_collapsed, $name;
    
    function __construct($name, $default_status = false)
    {
        global $app_user;
        
        $this->name = $name;
        
        $check_query = db_query("select id, is_collapsed from app_portlets where name='" . db_input($this->name) . "' and users_id='" . $app_user['id'] . "'");
        if($check = db_fetch_array($check_query))
        {
            if($check['is_collapsed']==1)
            {
                $this->is_collapsed =  true;
            }
            else
            {
                $this->is_collapsed =  false;
            }
        }
        else
        {
            $this->is_collapsed = $default_status;
        }
    }  
    
    function render_body()
    {
        $html = ' data_portlet_id="' . $this->name .  '"';
        
        if($this->is_collapsed)
        {
            $html .= 'style="display:none"';
        }
        
        return $html;
    }
    
    function button_css()
    {
        return $this->is_collapsed ? 'expand':'collapse';
    }
    
    static function set_status($name, $status)
    {
        global $app_user;
        
        if(!strlen($name)) return false;
            
        $check_query = db_query("select id from app_portlets where name='" . db_input_protect($name) . "' and users_id='" . $app_user['id'] . "'");
        if($check = db_fetch_array($check_query))
        {             
            db_perform('app_portlets',['is_collapsed'=>$status],'update',"id='" . $check['id'] . "'");
        }
        else
        {
            db_perform('app_portlets', [
                'name'=>db_input_protect($name),
                'users_id'=>$app_user['id'],
                'is_collapsed'=>$status,
            ]);
        }
    }
    
    static function delete_by_user_id($user_id)
    {
        db_query("delete from app_portlets where users_id={$user_id}");
    }
    
}
