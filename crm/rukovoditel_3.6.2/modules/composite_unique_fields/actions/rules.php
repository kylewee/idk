<?php

switch($app_module_action)
{
    case 'save':
        
        $sql_data = array(
            'entities_id' => $_GET['entities_id'],            
            'is_active'	=> (isset($_POST['is_active']) ? 1:0),
            'field_1' => $_POST['field_1'],
            'field_2' => $_POST['field_2'],   
            'is_unique_for_parent' => $_POST['is_unique_for_parent']??0,
            'message' => $_POST['message'],            
        );

        if(isset($_GET['id']))
        {                        
            db_perform('app_composite_unique_fields', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_composite_unique_fields', $sql_data);            
        }

        redirect_to('composite_unique_fields/rules', 'entities_id=' . $_GET['entities_id']);
        break;

    case 'delete':

        if(isset($_GET['id']))
        {
            $obj = db_find('app_composite_unique_fields',$_GET['id']);
                            
            db_delete_row('app_composite_unique_fields', $_GET['id']);

            $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS, ''), 'success');
        }

        redirect_to('composite_unique_fields/rules', 'entities_id=' . $_GET['entities_id']);
        break;            
}