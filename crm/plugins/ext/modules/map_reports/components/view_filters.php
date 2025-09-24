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

$panels_id = filters_panels::get_id_by_type($reports['entities_id'], 'map_reports' . $reports['id'], 0);
$count_panel_fields = filters_panels::count_fields_by_panel_id($panels_id);

if(reports::count_filters_by_reports_type($reports['entities_id'], 'default_map_reports' . $reports['id']) or $count_panel_fields)
{
    $fiters_reports_id = reports::get_reports_id_by_type($reports['entities_id'], 'default_map_reports' . $reports['id']);
}
else
{
    //create default entity report for logged user
    $reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($reports['entities_id']). "' and reports_type='map_reports" . $reports['id']. "' and created_by='" . $app_logged_users_id . "'");
    if(!$reports_info = db_fetch_array($reports_info_query))
    {
            $sql_data = array('name'=>'',
                            'entities_id'=>$reports['entities_id'],
                            'reports_type'=>'map_reports' . $reports['id'],
                            'in_menu'=>0,
                            'in_dashboard'=>0,
                            'listing_order_fields'=>'',
                            'created_by'=>$app_logged_users_id,
            );

            db_perform('app_reports',$sql_data);
            $fiters_reports_id = db_insert_id();

            reports::auto_create_parent_reports($fiters_reports_id);
    }
    else
    {
            $fiters_reports_id = $reports_info['id'];
    }


    if($app_module_path=='ext/map_reports/view')
    {
            $filters_preivew = new filters_preivew($fiters_reports_id);
            $filters_preivew->redirect_to = 'map_reports' . $reports['id'];
            $filters_preivew->has_listing_configuration = false;

            if(isset($_GET['path']))
            {
                    $filters_preivew->path = $_GET['path'];
                    $filters_preivew->include_paretn_filters = false;
            }

            echo $filters_preivew->render();

    }
}

if($count_panel_fields)
{
    $panel_fiters_reports_id = reports::auto_create_report_by_type($reports['entities_id'], 'panel_map_reports' . $reports['id'], true);
    $filters_panels = new filters_panels($reports['entities_id'],$panel_fiters_reports_id,'',0);
    $filters_panels->set_type('map_reports' . $reports['id']);
    $filters_panels->set_items_listing_funciton_name('load_map_report' . $reports['id']);
    echo '
        <div class="map_reports' . $reports['id'] . '">' . $filters_panels->render_horizontal() . '</div>        
        ';
}
else
{
    $panel_fiters_reports_id = 0;
}

