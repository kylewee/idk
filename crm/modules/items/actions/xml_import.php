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

if(!xml_import::has_users_access($current_entity_id,_get::int('templates_id')))
{
    redirect_to('dashboard/access_forbidden');
}

$template_info = db_find('app_ext_xml_import_templates',_get::int('templates_id'));

switch($app_module_action)
{
    case 'import':
        
        $xml_import_filename = 'xml_imort_' . _post::int('current_time') . '.xml';
        
        $xml_import = new xml_import($xml_import_filename,$template_info);        
        $xml_errors = $xml_import->has_xml_errors();
                        
        if(strlen($xml_errors))
        { 
            redirect_to('items/xml_import_preview','path=' . $app_path . '&templates_id=' . $template_info['id'] . '&current_time=' . _post::int('current_time'));
        }
        else
        {
            $msg = $xml_import->import_data();
            
            $alerts->add($msg,'success');
        }
        
        $xml_import->unlink_import_file();
       
        switch($_POST['redirect_to'])
        {
            case 'items/info':
                redirect_to('items/info','path=' . $app_path);
                break;
            case 'items/items':
                redirect_to('items/items','path=' . $app_path);
                break;
        }
        
        break;
}

