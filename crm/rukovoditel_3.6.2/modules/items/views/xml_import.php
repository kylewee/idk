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

<?php echo ajax_modal_template_header($template_info['name']) ?>

<?php $import_fields = array(); ?>

<?php echo form_tag('import_data', url_for('items/xml_import_preview','path=' . $app_path . '&templates_id=' . $template_info['id']),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>
<?php echo input_hidden_tag('current_time',time()) ?>
<div class="modal-body">
  <div class="form-body">
  
<p><?php echo $template_info['description'] ?></p>

        
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_FILENAME ?></label>
    <div class="col-md-9">	
  	  <?php echo input_file_tag('filename',array('class'=>'form-control required input-xlarge', 'accept'=>fieldtype_attachments::get_accept_types_by_extensions('xml'))) ?>
      <span class="help-block">*.xml</span>      
    </div>			
  </div>  
     
 </div>
</div>

<?php echo ajax_modal_template_footer(TEXT_BUTTON_CONTINUE) ?>

</form> 

<script>
  $(function() { 
    $('#import_data').validate({
	    	submitHandler: function(form){
					app_prepare_modal_action_loading(form)
					return true;
				}
      }); 
                                                                    
  });
  
</script> 

