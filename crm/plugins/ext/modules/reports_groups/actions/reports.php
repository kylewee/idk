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

if(!users::has_reports_access())
{
    redirect_to('dashboard/access_forbidden');
}

$app_title = app_set_title(TEXT_REPORTS_GROUPS);

switch($app_module_action)
{
    case 'save':
        $sql_data = array(
            'name' => db_prepare_input($_POST['name']),
            'menu_icon' => $_POST['menu_icon'],
            'icon_color' => db_prepare_input($_POST['icon_color']),
            'bg_color' => db_prepare_input($_POST['bg_color']),
            'in_menu' => (isset($_POST['in_menu']) ? $_POST['in_menu'] : 0),
            'in_dashboard' => (isset($_POST['in_dashboard']) ? $_POST['in_dashboard'] : 0),
            'sort_order' => $_POST['sort_order'],
            'created_by' => $app_user['id'],
            'is_common' => 1,
            'users_groups' => (isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''),
            'assigned_to' => (isset($_POST['assigned_to']) ? implode(',', $_POST['assigned_to']) : ''),
        );

        if(isset($_GET['id']))
        {
            db_perform('app_reports_groups', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_reports_groups', $sql_data);
        }

        redirect_to('ext/reports_groups/reports');
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            db_delete_row('app_reports_groups', $_GET['id']);

            redirect_to('ext/reports_groups/reports');
        }
        break;
}		
