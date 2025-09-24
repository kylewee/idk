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

if(!app_session_is_registered('holidays_filter'))
{
    $holidays_filter = date('Y');
    app_session_register('holidays_filter');
}

switch($app_module_action)
{
    case 'set_holidays_filter':
        $holidays_filter = $_POST['holidays_filter'];

        redirect_to('holidays/holidays');
        break;
    case 'save':
        $sql_data = array(
            'name' => $_POST['name'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
        );

        if(isset($_GET['id']))
        {
            db_perform('app_holidays', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_holidays', $sql_data);
        }

        redirect_to('holidays/holidays');
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            db_delete_row('app_holidays', _get::int('id'));

            redirect_to('holidays/holidays');
        }
        break;
}