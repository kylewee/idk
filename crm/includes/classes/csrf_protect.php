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

class csrf_protect
{
    static function add_token_to_url($url)
    {
        global $app_session_token;
                        
        if(strstr($url,'&action=') and app_session_is_registered('app_logged_users_id')
           and !strstr($url,'&action=attachments_preview') and !strstr($url,'&action=download_attachment'))
        {
            return '&token=' . urlencode($app_session_token);
        }
        else
        {
            return '';
        }
    }
    
    static function check()
    {
        global $app_session_token, $app_module_path;
        
        if($app_module_path!='users/login')
        {
            if(isset($_GET['action']) and !in_array($_GET['action'],['attachments_preview','download_attachment']) and app_session_is_registered('app_logged_users_id') and (!isset($_GET['token']) or urldecode($_GET['token'])!=$app_session_token))
            {
                redirect_to('dashboard/token_error');
            }
        }
    }
}
