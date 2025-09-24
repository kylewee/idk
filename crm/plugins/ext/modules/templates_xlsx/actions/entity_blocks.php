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
        $sql_data = [
        'templates_id' => $template_info['id'],
        'block_type' => 'entity',
        'parent_id' => $parent_block['id'],
        'fields_id' => _POST('fields_id'),
        'settings' => (isset($_POST['settings']) ? json_encode($_POST['settings']) : ''),
        'sort_order' => $_POST['sort_order'],
        ];
        
        if(isset($_GET['id']))
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data);
        }
        
        redirect_to('ext/templates_xlsx/entity_blocks','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']);
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            export_templates_blocks::delele_block(_GET('id'));
            
            redirect_to('ext/templates_xlsx/entity_blocks','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']);
        }
        break;
}