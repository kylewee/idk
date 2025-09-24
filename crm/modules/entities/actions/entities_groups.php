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
        $sql_data = array(
            'name' => $_POST['name'],
            'sort_order' => $_POST['sort_order']);

        if(isset($_GET['id']))
        {
            db_perform('app_entities_groups', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            if(isset($_POST['parent_id']))
            {
                $sql_data['parent_id'] = $_POST['parent_id'];
            }

            db_perform('app_entities_groups', $sql_data);
            $id = db_insert_id();
        }

        redirect_to('entities/entities_groups');
        break;
    case 'delete':
        if(isset($_GET['id']))
        {

            $name = entities_groups::get_name_by_id(_GET('id'));

            entities_groups::delete(_GET('id'));

            $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS, $name), 'success');


            redirect_to('entities/entities_groups');
        }
        break;
    case 'sort':
        $choices_sorted = $_POST['choices_sorted'];

        if(strlen($choices_sorted) > 0)
        {
            $choices_sorted = json_decode(stripslashes($choices_sorted), true);            

            $sort_order = 0;
            foreach($choices_sorted as $v)
            {
                db_query("update app_entities_groups set sort_order={$sort_order} where id={$v['id']}");
                $sort_order++;
            }
        }

        redirect_to('entities/entities_groups');
        break;
}
