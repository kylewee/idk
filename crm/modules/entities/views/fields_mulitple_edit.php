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

<?php echo ajax_modal_template_header(TEXT_EDIT_FIELDS) ?>

<?php echo form_tag('form-copy-to', url_for('entities/fields','action=mulitple_edit&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('selected_fields') ?>
<div class="modal-body" >
  <div id="modal-body-content">    

<?php 
$choices = array();
$choices['yes'] = TEXT_YES;
$choices['no'] = TEXT_NO;
?>    
    <div class="form-group">
    	<label class="col-md-4 control-label" for="type"><?php echo TEXT_IS_REQUIRED ?></label>
      <div class="col-md-8">	
    	  <?php echo select_tag('is_required',$choices,'',array('class'=>'form-control input-small')) ?>        
      </div>			
    </div>
    
    <div id="entities_form_tabs"></div>
      
  </div>
</div> 
<?php echo ajax_modal_template_footer() ?>

</form>  

<script>
  $(function(){
     if($('.fields_checkbox:checked').length==0)
     {
       $('#modal-body-content').html('<?php echo TEXT_PLEASE_SELECT_FIELDS ?>')
       $('.btn-primary-modal-action').hide()
     }
     else
     {
       selected_fields_list = $('.fields_checkbox:checked').serialize().replace(/fields%5B%5D=/g,'').replace(/&/g,',');
       $('#selected_fields').val(selected_fields_list);              
     } 
     
              
  })     
</script>