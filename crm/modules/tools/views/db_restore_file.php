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

<?php echo ajax_modal_template_header(TEXT_BUTTON_DB_RESOTRE_FROM_FILE) ?>

<?php echo form_tag('restore_file_form', url_for('tools/db_restore_process','action=restore_from_file'),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>
<div class="modal-body">
  <div class="form-body">
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_FILE ?></label>
    <div class="col-md-9">	
  	  <?php echo input_file_tag('filename',array('class'=>'form-control')) ?>
  	  <?php echo tooltip_text('(*.sql | *.zip) ' . sprintf(TEXT_MAX_FILE_SIZE,CFG_SERVER_UPLOAD_MAX_FILESIZE)) ?>
    </div>			
  </div> 
     
  </div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_RESTORE) ?>

</form> 

<script>
  $(function() { 
    $('#restore_file_form').validate({
      rules: {
          filename: {
            required: true,
            extension: "zip|sql"          
          }
          
        }
    });                                                                 
  });  
</script>  