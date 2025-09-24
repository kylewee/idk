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

if(!isset($app_user))
{
    $app_user = [
        'id' => 0,
        'group_id' => 0
    ];
}

//check if report exist
$reports_query = db_query("select * from app_ext_map_reports where id='" . db_input(_POST('id')) . "'");
if(!$reports = db_fetch_array($reports_query))
{
    exit();
}

//check access
if(!map_reports::has_access($reports['users_groups']))
{
    exit();
}

$fiters_reports_id = _POST('fiters_reports_id');
$panel_fiters_reports_id = _POST('panel_fiters_reports_id');

$field_info = db_find('app_fields',$reports['fields_id']);


$cfg = new fields_types_cfg($field_info['configuration']);

$map_reports = new map_reports($reports, $fiters_reports_id, $field_info, $panel_fiters_reports_id);

if(strlen($reports['latlng']))
{
	$latlng = explode(',',$reports['latlng']);
	$center_map_js = '
				var myLatlng = new google.maps.LatLng(' . trim($latlng[0]) . ',' . trim($latlng[1]) . ');
				map.setCenter(myLatlng);
			';
}
else
{
	$center_map_js = 'map.setCenter(myLatlng);';
}	


$html = '';


$html .= '
<script>
var map

$(function(){

        let is_geliossoft = ' . $cfg->get('is_geliossoft',0) . '
            
	var mapOptions = {
		zoom: ' . $reports['zoom'] . ',
	}

	map = new google.maps.Map(document.getElementById("goolge_map_container"), mapOptions);
        
        let markers = []
				
	' . $map_reports->render_google_js() . '
			
	//Got result, center the map and put it out there
	' . $center_map_js . '	
            
        if(markers.length>0)
        {
            const markerCluster = new markerClusterer.MarkerClusterer({ map, markers });
        }
                        
        if(is_geliossoft==1)
        {
            new geliossoft_objects(map,"' . $reports['fields_id'] . '","' . $cfg->get('refresh_interval',60) . '","google")
        }
        
        $(".map-sidebar-item").click(function(){
            lat = $(this).attr("lat")
            lng = $(this).attr("lng")

            ymap_center(lat,lng)                
        })
			
	set_goolge_map_height();
			
	$( window ).resize(function(){
		set_goolge_map_height();
	})		

})

function ymap_center(lat, lng)
{
    map.setCenter(new google.maps.LatLng(lat,lng))
    map.setZoom(13)
}
			
function set_goolge_map_height()
{
    if($(".header").length)
    {
        $("#goolge_map_container").height($( window ).height()-$(".portlet-filters-preview").height()-$(".header").height()-150);        
    }
    else
    {
        $("#goolge_map_container").height($( window ).height())
        
    }    
}			
					
</script>
				
';	

if($reports['display_sidebar']==1)
{
    $portlets = new portlets('map_sidebar_' . $reports['id']);
    
    $html .= '
        <table class="table-sidebar">
            <tr>
                <td class="table-sidebar-content">
                    <div id="goolge_map_container" style="width:100%;  height: 600px;"></div>
                </td>
                <td class="table-sidebar-body" width="' . pivot_map_reports::get_sidebar_width($reports) . '" ' . $portlets->render_body(). '>
                    <div class="map-sidebar-list-scroller">' . $map_reports->get_sidebar() . '</div>
                </td>
                <td class="table-sidebar-action right ' . $portlets->button_css() . '"></td>
            </tr>    
        </table>         
        ';        
}
else
{
    $html .= '<div id="goolge_map_container" style="width:100%;  height: 600px;"></div>';
}

echo (count($map_reports->markers) ? $html : '<div class="alert alert-warning">' . TEXT_NO_RECORDS_FOUND . '</div>');
