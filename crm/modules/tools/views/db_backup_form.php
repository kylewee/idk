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


<?php echo ajax_modal_template_header(TEXT_HEADING_DB_BACKUP) ?>

<?php echo form_tag('backup_form', url_for('tools/db_backup','action=backup'),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_COMMENT ?></label>
    <div class="col-md-9">	
  	  <?php echo textarea_tag('description','',array('class'=>'form-control')) ?>
  	  <?php echo tooltip_text(TEXT_BACKUP_DESCRIPTION_TIP) ?>
    </div>			
  </div> 
     
  </div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_CREATE_BACKUP) ?>

</form> 

<script>
  $(function() { 
    $('#backup_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                                                  
  });
  
</script>