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
	$modules = new modules($obj['type']);
	
	$module = new $obj['module'];
	
	echo ajax_modal_template_header($module->title) 
?>

<?php echo form_tag('module_form', url_for('ext/modules/modules','action=save&id=' . _get::int('id') . '&type=' . $_GET['type']),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body ajax-modal-width-790">
   
	  <div class="form-group">
	  	<label class="col-md-4 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
	    <div class="col-md-8">	
	  	  <p class="form-control-static"><?php echo input_checkbox_tag('is_active',1,array('checked'=>$obj['is_active'])) ?></p>      
	    </div>			
	  </div> 
	  
	  <?php echo $modules->render_configuration($module, _get::int('id')) ?>
		       
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() {           
    $('#module_form').validate({ignore:'.ignore-validation'});                                                               
  });    
</script>   
    
 
