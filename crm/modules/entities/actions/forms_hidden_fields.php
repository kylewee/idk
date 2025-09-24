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

switch($app_module_action)
{
    case 'save':
        $cfg = new entities_cfg($_GET['entities_id']);
        
        if(isset($_POST['hidden_form_fields']))
        {
            $cfg->set('hidden_form_fields',implode(',',$_POST['hidden_form_fields']));
        }
        else
        {
            $cfg->set('hidden_form_fields','');
        }
        
        redirect_to('entities/forms','entities_id=' . $_GET['entities_id']);
        break;
}

