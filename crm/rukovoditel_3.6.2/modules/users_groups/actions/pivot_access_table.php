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

$users_groups_info_query = db_query("select * from app_access_groups where id='" . _get::int('id') . "'");
if(!$users_groups_info = db_fetch_array($users_groups_info_query))
{
    redirect_to('users_groups/users_groups');
}

switch($app_module_action)
{
    case 'set_access':
        if(isset($_POST['view_access']) and strlen($_POST['view_access']))
        {
            
            $view_access = strlen($_POST['view_access']) ? array($_POST['view_access']) : [];
            $access = isset($_POST['access']) ? $_POST['access'] : [];                                    
            
            $access_schema = array_merge($view_access,$access);

            $access_schema = access_groups::prepare_entities_access_schema($access_schema);

            $sql_data = array('access_schema' => implode(',', $access_schema));
            
            //print_rr($sql_data);

            $acess_info_query = db_query("select id, access_schema from app_entities_access where entities_id='" . db_input($_GET['entities_id']) . "' and access_groups_id='" . $users_groups_info['id'] . "'");
            if($acess_info = db_fetch_array($acess_info_query))
            {
                db_perform('app_entities_access', $sql_data, 'update', "entities_id='" . db_input($_GET['entities_id']) . "' and access_groups_id='" . $users_groups_info['id'] . "'");
                
                //reset duplicates
		db_query("delete from app_entities_access where entities_id='" . db_input($_GET['entities_id']) . "' and access_groups_id='" . $users_groups_info['id']. "' and id!='" . $acess_info['id'] . "'");
            }
            else
            {
                $sql_data['entities_id'] = $_GET['entities_id'];
                $sql_data['access_groups_id'] = $users_groups_info['id'];
                db_perform('app_entities_access', $sql_data);
            }
        }
        else
        {
            $sql_data = array('access_schema' => '');
            db_perform('app_entities_access', $sql_data, 'update', "entities_id='" . db_input($_GET['entities_id']) . "' and access_groups_id='" . $users_groups_info['id'] . "'");
        }

        if(isset($_POST['comments_access']))
        {
            $access = $_POST['comments_access'];

            $sql_data = array('access_schema' => str_replace('_', ',', $access));

            $acess_info_query = db_query("select access_schema from app_comments_access where entities_id='" . db_input($_GET['entities_id']) . "' and access_groups_id='" . $users_groups_info['id'] . "'");
            if($acess_info = db_fetch_array($acess_info_query))
            {
                db_perform('app_comments_access', $sql_data, 'update', "entities_id='" . db_input($_GET['entities_id']) . "' and access_groups_id='" . $users_groups_info['id'] . "'");
            }
            else
            {
                $sql_data['entities_id'] = $_GET['entities_id'];
                $sql_data['access_groups_id'] = $users_groups_info['id'];
                db_perform('app_comments_access', $sql_data);
            }
        }

        exit();
        break;
}