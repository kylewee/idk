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

$report_page_query = db_query("select * from app_ext_report_page where id='" . _GET('id'). "' and (find_in_set({$app_user['group_id']},users_groups) or find_in_set({$app_user['id']},assigned_to)) and is_active=1 order by sort_order, name");
if(!$report_page = db_fetch_array($report_page_query))
{
    redirect_to_forbidden();
}

$app_title = app_set_title($report_page['name']);

if (!app_session_is_registered('report_page_filters')) 
{
  $report_page_filters = array();
  app_session_register('report_page_filters');    
} 

if(strlen($app_path))
{
    $path = items::parse_path($app_path);
    $current_entity_id = $path['entity_id'];
    $current_item_id = $path['item_id'];
    
    $current_path_array = explode('/', $app_path);
    $app_breadcrumb = items::get_breadcrumb($current_path_array);
}
else
{
    $current_entity_id = false;
    $current_item_id = false;
}

switch($app_module_action)
{
    case 'xls_export':
        
        require('includes/classes/items/items_export.php');
                
        $export_data = $_POST['export_data']??'';
        if(strlen($export_data))
        {
            if($export_data = json_decode($export_data,true))
            {
                $items_export = new items_export($report_page['name']);
                $items_export->xlsx_from_array($export_data); 
            }
        }
        exit();
        break;
    case 'load':
        
        //set filters
        report_page\report_filters::set_filters($report_page);
        
        $page = new report_page\report($report_page);
        
        //set item if exist
        if($current_item_id)
        {
            $page->set_item($current_entity_id,$current_item_id);
        }
        echo $page->get_html();
        
        exit();
        break;
}
  
