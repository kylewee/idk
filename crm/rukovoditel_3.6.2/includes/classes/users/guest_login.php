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

class guest_login
{
    static function is_enabled()
    {
        return (CFG_ENABLE_GUEST_LOGIN==1 and CFG_GUEST_LOGIN_USER>0) ? true:false;
    }
    
    static function is_guest()
    {
        global $app_user, $app_previously_logged_user;
        
        return (CFG_ENABLE_GUEST_LOGIN==1 and CFG_GUEST_LOGIN_USER==$app_user['id'] and $app_previously_logged_user==0) ? true:false;
    }
}
