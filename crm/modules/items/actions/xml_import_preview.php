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

if(strlen($_FILES['filename']['name'])>0)
{
    $xml_import_filename = 'xml_imort_' . _post::int('current_time') . '.xml';
    if(!move_uploaded_file($_FILES['filename']['tmp_name'], DIR_FS_TMP . $xml_import_filename))
    { 
        exit();
    }    
    
    
    xml_import::prepare_file_content($xml_import_filename);              
}
else
{
    $xml_import_filename = 'xml_imort_' . _get::int('current_time') . '.xml';    
}