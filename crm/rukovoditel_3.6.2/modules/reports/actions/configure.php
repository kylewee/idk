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

$reports_info_query = db_query("select * from app_reports where id='" . db_input(_get::int('reports_id')) . "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{
    $alerts->add(TEXT_REPORT_NOT_FOUND, 'error');
    redirect_to('dashboard/');
}

$fields_in_listing = array();

if(strlen($reports_info['fields_in_listing']) > 0)
{
    $fields_in_listing = explode(',', $reports_info['fields_in_listing']);
}
else
{
    $listing_types_query = db_query("select settings from app_listing_types where  type='" . $reports_info['listing_type'] . "' and entities_id='" . $reports_info['entities_id'] . "'");
    if($listing_types = db_fetch_array($listing_types_query))
    {
        $settings = new settings($listing_types['settings']);
    }
    else
    {
        $settings = new settings('');
    }

    
    if( is_array($settings->get('fields_in_listing')))
    {
        $fields_in_listing = $settings->get('fields_in_listing');
    }
    else
    {
        $fields_query = db_query("select f.* from app_fields f where f.listing_status=1 order by f.listing_sort_order, f.name");
        while($fields = db_fetch_array($fields_query))
        {
            $fields_in_listing[] = $fields['id'];
        }
    }
}

switch($app_module_action)
{
    case 'reset_cfg_to_defatul':
        db_query("update app_reports set fields_in_listing='' where id='" . db_input((int)$_GET['reports_id']) . "'");
        app_exit();        
        break;
    case 'set_listing_fields':

        if(strlen($_POST['fields_for_listing']) > 0)
        {
            $fields_for_listing = str_replace('form_fields_', '', $_POST['fields_for_listing']);
            db_query("update app_reports set fields_in_listing='" . db_input($fields_for_listing) . "' where id='" . db_input((int)$_GET['reports_id']) . "'");
        }
        else
        {
            db_query("update app_reports set fields_in_listing='' where id='" . db_input((int)$_GET['reports_id']) . "'");
        }
        exit();
        break;

    case 'set_rows_per_page':

        db_query("update app_reports set rows_per_page='" . db_input((int)$_POST['rows_per_page']) . "' where id='" . db_input((int)$_GET['reports_id']) . "'");
        exit();

        break;
}