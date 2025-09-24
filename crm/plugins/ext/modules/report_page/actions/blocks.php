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

$report_page_query = db_query("select * from app_ext_report_page where id='" . _GET('report_id') . "'");
if(!$report_page = db_fetch_array($report_page_query))
{    
    redirect_to('ext/report_page/reports');
}


switch($app_module_action)
{
    case 'save':
        $sql_data = [
            'report_id' => $report_page['id'],
            'block_type' => $_POST['block_type'],
            'name'=> $_POST['name']??'',            
            'parent_id' => 0,
            'field_id' => $_POST['field_id']??0, 
            'settings' => (isset($_POST['settings']) ? json_encode($_POST['settings']) : ''),
            'sort_order' => $_POST['sort_order'],
        ];
        
        //print_rr($_POST);
        //EXIT();
        
        if(isset($_GET['id']))
        {
            db_perform('app_ext_report_page_blocks',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_report_page_blocks',$sql_data);
        }
        
        redirect_to('ext/report_page/blocks','report_id=' . $report_page['id']);        
        break;
    case 'delete':
        if(isset($_GET['id']))
        {                        
            report_page\blocks::delete(_GET('id'));
                        
            redirect_to('ext/report_page/blocks','report_id=' . $report_page['id']);
        }
        break;
        
}