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

require(CFG_PATH_TO_PHPSPREADSHEET);

$template_info_query = db_query("select * from app_ext_import_templates where length(filepath)>0 and id='" . _GET('id'). "'");
if(!$template_info = db_fetch_array($template_info_query))
{
    reirect_to('ext/templates/import_templates');
}


switch($app_module_action)
{
    case 'preview':
        
        $xls_import = new xls_import('',$template_info);
    
        $xls_import->get_file_by_path();

        echo $xls_import->preview_data();

        $xls_import->unlink_import_file();
    
        exit();
        break;
    case 'import':
        
        $xls_import = new xls_import('',$template_info);
    
        $xls_import->get_file_by_path();

        $xls_import->import_data();

        $xls_import->unlink_import_file();
        
        switch($template_info['import_action'])
        {
            case 'import':
                $alerts->add(TEXT_COUNT_ITEMS_ADDED . ' ' . $xls_import->count_new_items, 'success');
                break;
            case 'update':
                $alerts->add(TEXT_COUNT_ITEMS_UPDATED . ' ' . $xls_import->count_updated_items, 'success');
                break;
            case 'update_import':
                $alerts->add(TEXT_COUNT_ITEMS_UPDATED . ' ' . $xls_import->count_updated_items . '. ' . TEXT_COUNT_ITEMS_ADDED . ' ' . $xls_import->count_new_items, 'success');
                break;
        }
        redirect_to('ext/templates/import_templates');
        
        break;
}
