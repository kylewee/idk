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

<div id="pivotuitable<?php echo $pivotreports['id'] ?>"><div class="fa fa-spinner fa-spin"></div></div>

<script type="text/javascript">

$(function(){   

		var derivers = $.pivotUtilities.derivers;  
  	var renderers = $.extend($.pivotUtilities.locales['fr'].renderers,$.pivotUtilities.locales['fr'].c3_renderers,$.pivotUtilities.locales['fr'].export_renderers);
  
    Papa.parse("<?php echo url_for('ext/pivotreports/view','id=' . $pivotreports['id'] . '&action=get_csv')?>", {
        download: true,
        skipEmptyLines: true,            
        complete: function(parsed){
            $("#pivotuitable<?php echo $pivotreports['id'] ?>").pivotUI(parsed.data,{ 
            	renderers: renderers,               	
            	            	
							<?php echo pivotreports::render_reports_settings($pivotreports['reports_settings']) ?>
							            
              },false, "fr");
        }
    });

 });    
</script>

<style>
.pvtAxisContainer{
	display:none;
}

.pvtAxisContainer, .pvtVals{
	border: 0;
	background: transparent;
} 

.pvtAggregator{
	width: 320px;
}

.pvtAttrDropdown{
	width: 320px;
} 

.pvtRenderer{
	width: 320px;
	position: absolute;
  margin-left: 350px;  
}

.pvtRendererTD{
	position: absolute;
}

</style>
