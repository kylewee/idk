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

<?php 
    if((int)$obj['parent_id']==0 and !isset($_GET['parent_id']))
    {        
?>	
  <div class="form-group">
  	<label class="col-md-4 control-label" for="sort_order"><?php echo tooltip_icon(TEXT_IMAGE_MAP_FILENAME_INFO) . TEXT_IMAGE ?></label>
    <div class="col-md-8">	
  	  <?php echo input_file_tag('filename',array('class'=>'form-control input-large ' . (!strlen($obj['filename']) ? 'required':''))) . $obj['filename'] ?>
  	  <?php echo tooltip_text(TEXT_IMAGE_MAP_FILENAME_DESCRIPTION) ?>
  	  <?php echo tooltip_text(TEXT_MAX_UPLOAD_FILE_SIZE . ': ' . CFG_SERVER_UPLOAD_MAX_FILESIZE . ' MB') ?>
  	        
    </div>			
  </div>
<?php 
    } 
?>