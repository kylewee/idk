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

if($reports['is_public_access']==0 or $app_layout=='layout.php')
{
    require(component_path('ext/map_reports/view_filters'));
}
else
{
    $fiters_reports_id = default_filters::get_reports_id($reports['entities_id'], 'public_map' . $reports['id']);
    $panel_fiters_reports_id = 0;
}


$cfg = new fields_types_cfg($field_info['configuration']);

$html = '
    <script src="https://maps.googleapis.com/maps/api/js?key=' . $cfg->get('api_key') . '"></script>    
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
    <script src="js/geliossoft/geliossoft_objects.js?v=' . PROJECT_VERSION . '" type="text/javascript"></script>
        
    <div id="map_rpeort_' . $reports['id'] . '"></div>
        
    <script>
        function load_map_report' . $reports['id'] . '()
        {
            $("#map_rpeort_' . $reports['id'] . '").load("' . url_for('ext/map_reports/view_google&id=' . $reports['id']) . '",{id: ' . $reports['id'] . ', fiters_reports_id: ' . $fiters_reports_id . ', panel_fiters_reports_id: ' . $panel_fiters_reports_id . '},function(){
                App.initMapSidebar();
            })
        }
        
        $(function(){
            load_map_report' . $reports['id'] . '();
        })        
    </script>
    ';

echo $html;



