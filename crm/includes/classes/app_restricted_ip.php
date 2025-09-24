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

class app_restricted_ip
{

    static function is_enabled()
    {
        if(CFG_RESTRICTED_BY_IP_ENABLE == true and strlen(CFG_ALLOWED_IP_LIST))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static function verify()
    {
        if(self::is_enabled())
        {
            if(!in_array($_SERVER['REMOTE_ADDR'], array_map('trim', explode(',', CFG_ALLOWED_IP_LIST))))
            {
                echo TEXT_ACCESS_FORBIDDEN;
                exit();
            }
        }
    }

}
