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

$reports_entities_query = db_query("select * from app_ext_pivot_map_reports_entities where reports_id=" . $reports['id']. " order by id limit 1");
$reports_entities = db_fetch_array($reports_entities_query);

$cfg = new fields_types_cfg($app_fields_cache[$reports_entities['entities_id']][$reports_entities['fields_id']]['configuration']);


$html = '';

$html .= '
    <script src="https://maps.googleapis.com/maps/api/js?key=' . $cfg->get('api_key') . '"></script>
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>   
    <script src="js/geliossoft/geliossoft_objects.js?v=' . PROJECT_VERSION . '" type="text/javascript"></script>
        
    <div id="map_rpeort_' . $reports['id'] . '"></div>
        
    <script>
        function load_pivot_map_report' . $reports['id'] . '()
        {
            $("#map_rpeort_' . $reports['id'] . '").load("' . url_for('ext/pivot_map_reports/view_google&id=' . $reports['id']) . '",{id: ' . $reports['id'] . '},function(){
                App.initMapSidebar();
            })
        }
        
        $(function(){
            load_pivot_map_report' . $reports['id'] . '();
        })        
    </script>
        ';

echo $html;


