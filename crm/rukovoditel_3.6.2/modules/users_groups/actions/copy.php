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
    case 'copy':
        
        $copy_from_group_id = _GET('id');
        
        $sql_data = array(
            'name' => $_POST['name'],
            'sort_order' => $_POST['sort_order'],
            'is_default' => 0,
            'is_ldap_default' => 0,
            'ldap_filter' =>'',
            'notes' => $_POST['notes'],
        );
        
        db_perform('app_access_groups', $sql_data);
        $copy_to_group_id = db_insert_id();
                        
        foreach(entities::get_tree() as $v)
        {
            $entities_id = $v['id'];
            
            $access_schema = '';

            //check if access exit
            $acess_info_query = db_query("select access_schema from app_entities_access where entities_id='" . $entities_id . "' and access_groups_id='" . $copy_from_group_id . "'");
            if($acess_info = db_fetch_array($acess_info_query))
            {
                $access_schema = $acess_info['access_schema'];
            }

            $sql_data = array('access_schema' => $access_schema);           
            //insert new access
            $sql_data['entities_id'] = $entities_id;
            $sql_data['access_groups_id'] = $copy_to_group_id;
            db_perform('app_entities_access', $sql_data);
            

            
            //insert new fields access
            $sql_data = array();
            $fields_access_query = db_query("select * from app_fields_access where entities_id='" . db_input($entities_id) . "' and access_groups_id='" . db_input($copy_from_group_id) . "'");
            while($fields_access = db_fetch_array($fields_access_query))
            {
                $sql_data[] = array(
                    'access_schema' => $fields_access['access_schema'],
                    'entities_id' => $entities_id,
                    'access_groups_id' => $copy_to_group_id,
                    'fields_id' => $fields_access['fields_id'],
                );
            }

            if(count($sql_data))
            {
                db_batch_insert('app_fields_access', $sql_data);
            }

            //copy comments access

            $access_schema = '';

            //check if comments access exist
            $acess_info_query = db_query("select access_schema from app_comments_access where entities_id='" . db_input($entities_id) . "' and access_groups_id='" . $copy_from_group_id . "'");
            if($acess_info = db_fetch_array($acess_info_query))
            {
                $access_schema = $acess_info['access_schema'];
                
                $sql_data = array('access_schema' => str_replace('_', ',', $access_schema));           
                $sql_data['entities_id'] = $entities_id;
                $sql_data['access_groups_id'] = $copy_to_group_id;
                db_perform('app_comments_access', $sql_data);
            }

            
            
        }

        $alerts->add(TEXT_ACCESS_UPDATED, 'success');

        redirect_to('users_groups/pivot_access_table', 'id=' . $copy_to_group_id);
        
        break;
}