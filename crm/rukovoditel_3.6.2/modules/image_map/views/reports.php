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
?>		
	<!--tabs-->
	<div id="tabsContainer"   >	
		<div id="mapViewTab" class="tab-pane fade" >
				  
			<div id="mapContainer" class="" data-path="<?php echo url_for('image_map/map','action=getMapView&reports_id=' . _get::int('reports_id') . '&fiters_reports_id=' .  _get::int('fiters_reports_id')) ?>" data-id="<?php echo $image_map_report_filter[$reports['id']] ?>" data-edit-access="<?php echo image_map::has_access($reports['users_groups'],'full')?>">
				
         <div id="mapViewer" class="viewer" style="width: 100%; height: 100%;" ></div>
                                    
         <div class="cfm-legend hide" >            
            <ul class="unstyled"></ul>
          </div>
                  
                  
          <div class="cfm-info-left" >
          &nbsp;
          </div>        
          <div class="cfm-info" >              
             <ul class="cfm-breadcrumb breadcrumb"></ul>
          </div>
                
          <div class="crosshair" data-state="region"></div>
                  
      </div> 
		  		
		</div>
  </div>
  
  <?php echo image_map::render_markers_color($reports['fields_id']) ?>
  		
  		
    <script src="js/image-map/common/js/jquery-1.11.3.min.js"></script>	
    <script src="js/image-map/common/js/bootstrap.js"></script>
    <script src="js/image-map/common/js/bootstrap-adds.js"></script>
    <script src="js/image-map/common/js/leaflet.js"></script>	
    <script src="js/image-map/admin/js/jquery.validate.min.js"></script>

    <script src="js/image-map/common/js/common_custom.js?v=4"></script>	
    <script src="js/image-map/admin/js/plugins_custom.js?v=4"></script>	
    <script src="js/image-map/admin/js/admin_custom.js?v=4"></script>
	   
  <script>
	$(function(){
		//open map		
		viewCtrl.showTab("mapViewTab", [<?php echo $image_map_report_filter[$reports['id']] ?>, "marker"])	
	})
		 		
  </script>
  
<?php 
if(!image_map::has_access($reports['users_groups'],'full'))
{
	echo image_map::render_cfm_selected_css();
}
?>  
  
  