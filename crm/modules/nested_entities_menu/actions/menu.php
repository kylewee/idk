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
            'entities_id' => $_GET['entities_id'],
            'name' => $_POST['name'],
            'is_active'	=> (isset($_POST['is_active']) ? 1:0),
            'icon' => $_POST['icon'],
            'icon_color' => $_POST['icon_color'],
            'entities' => (isset($_POST['entities']) ? implode(',', $_POST['entities']) : ''),            
            'sort_order' => $_POST['sort_order'],
        );

        if(isset($_GET['id']))
        {
            db_perform('app_nested_entities_menu', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_nested_entities_menu', $sql_data);
        }

        redirect_to('nested_entities_menu/menu', 'entities_id=' . $_GET['entities_id']);
        break;

    case 'delete':

        if(isset($_GET['id']))
        {
            db_delete_row('app_nested_entities_menu', $_GET['id']);

            $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS, ''), 'success');
        }

        redirect_to('nested_entities_menu/menu', 'entities_id=' . $_GET['entities_id']);
        break;            
        
    case 'sort':
        $choices_sorted = $_POST['choices_sorted'];
        if(strlen($choices_sorted)>0)
        {      	      
            $choices_sorted = json_decode(stripslashes($choices_sorted),true);
                        
            foreach($choices_sorted as $sort_order=>$v)
            {
                db_query("update app_nested_entities_menu set sort_order={$sort_order} where id={$v['id']}");
            }
        }
        
        redirect_to('nested_entities_menu/menu', 'entities_id=' . $_GET['entities_id']);
        break;
}