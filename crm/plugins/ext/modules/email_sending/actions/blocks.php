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


switch ($app_module_action)
{
    case 'save':

        $sql_data = array(
            'name' => $_POST['name'],
            'description' => $_POST['description'],                        
        );

        if (isset($_GET['id']))
        {
            db_perform('app_ext_email_rules_blocks', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_email_rules_blocks', $sql_data);
        }

        redirect_to('ext/email_sending/blocks', 'entities_id=' . _get::int('entities_id'));

        break;
    case 'delete':

        if (isset($_GET['id']))
        {
            db_delete_row('app_ext_email_rules_blocks', $_GET['id']);
        }

        redirect_to('ext/email_sending/blocks', 'entities_id=' . _get::int('entities_id'));
        break;
}