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

//check access
if($app_user['group_id']>0)
{
  redirect_to('dashboard/access_forbidden');
}


if(isset($_GET['block_id']))
{
    $block_info_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id, f.configuration as field_configuration from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where b.id=" . _GET('block_id'));
    if(!$block_info = db_fetch_array($block_info_query))
    {
        redirect_to('ext/report_page/reports');
    }

    $report_page_query = db_query("select * from app_ext_report_page where id='" . $block_info['report_id'] . "'");
    if(!$report_page = db_fetch_array($report_page_query))
    { 
        redirect_to('ext/report_page/reports');
    }   
    
    
    if($block_info['field_id']>0)
    {
        $block_name = $app_entities_cache[$block_info['field_entity_id']]['name'] . ': ' . fields_types::get_option($block_info['field_type'], 'name', $block_info['field_name']);                
    }
    else
    {
        $block_name =  $block_info['name'];
    }
    
    $block_settings = new settings($block_info['settings']);
    
    $block_cfg_page = $block_info['block_type']=='table' ? 'blocks_mysql_table':'blocks_entity_table';    
}

if(isset($_GET['row_id']))
{
    $row_info_query = db_query("select b.* from app_ext_report_page_blocks b where b.id=" . _GET('row_id'),false);
    if(!$row_info = db_fetch_array($row_info_query))
    {
        redirect_to('ext/report_page/blocks','report_id=' . $report_page['id']);
    }            
}