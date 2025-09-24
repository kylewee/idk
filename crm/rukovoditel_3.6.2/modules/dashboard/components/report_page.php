<?php

/* 
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */

$html = '';
$reports_query = db_query("select * from app_ext_report_page where entities_id=0 and in_dashboard=1  and is_active=1 and (find_in_set('" . $app_user['group_id'] . "',users_groups) or find_in_set('" . $app_user['id'] . "',assigned_to)) order by sort_order");
while($reports = db_fetch_array($reports_query))
{
    $check_query = db_query("select id from app_reports_sections where (report_left='report_page{$reports['id']}' or report_right='report_page{$reports['id']}') and created_by='" . $app_user['id']. "'");
    if($check = db_fetch_array($check_query))
    {
        continue;
    }
    
    $settings = new settings($reports['settings']);
                    
    if($settings->get('hide_page_title')!=1)
    {
        $html .= '<h3 class="page-title"><a href="' . url_for('report_page/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3>'; 
    }

    $filters = new report_page\report_filters($reports);
    $filters->set_form_css('from-report-page-filters from-report-page-filters' . $reports['id']);
    $filters->set_form_function('load_report_page' . $reports['id'] . '()');
    $html .= $filters->render();

    $html .= '<div class="row">
                <div class="col-md-12">
                    <div id="report_page_content' . $reports['id'] . '" data-id="' . $reports['id'] . '" data-path=""></div>
                </div>
            </div>

            <script>
            function load_report_page' . $reports['id'] . '()
            {
                $("#report_page_content' . $reports['id'] . '").css("opacity", 0.5).append(\'<div class="data_listing_processing"></div>\')

                let data = $("#report_page_content' . $reports['id'] . '").data()    

                $("#report_page_content' . $reports['id'] . '").load(url_for("report_page/view","action=load&id="+data.id+"&path="+data.path),$(".from-report-page-filters' . $reports['id'] . '").serializeArray(),function(){
                    $(this).css("opacity", 1)

                    app_handle_scrollers()
                })
            }

            $(function(){
                load_report_page' . $reports['id'] . '()
            })

            </script>';  
    
    $has_reports_on_dashboard = true;
}

echo $html;