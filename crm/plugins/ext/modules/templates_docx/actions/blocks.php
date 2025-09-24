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
            'block_type' => 'parent',
            'parent_id' => 0,
            'fields_id' => _POST('fields_id'),
            'notes' => $_POST['notes']??'',
            'extra_type' => $_POST['extra_type']??'',
            'settings' => (isset($_POST['settings']) ? json_encode($_POST['settings']) : ''),
            'sort_order' => $_POST['sort_order'],
        ];
        
        //print_rr($_POST);
        //EXIT();
        
        if(isset($_GET['id']))
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data);
        }
        
        redirect_to('ext/templates_docx/blocks','templates_id=' . $template_info['id']);        
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            export_templates_blocks::delele_block(_GET('id'));
                        
            redirect_to('ext/templates_docx/blocks','templates_id=' . $template_info['id']);
        }
        break;
}