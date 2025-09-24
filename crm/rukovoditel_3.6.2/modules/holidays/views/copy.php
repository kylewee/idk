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

<?php echo ajax_modal_template_header(TEXT_COPY) ?>

<?php echo form_tag('form-copy-to', url_for('holidays/copy','action=copy_selected'),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('selected_items') ?>
<div class="modal-body" >
  <div id="modal-body-content">    
    
    <div class="form-group">
    	<label class="col-md-4 control-label" for="type"><?php echo TEXT_COPY_TO ?></label>
      <div class="col-md-8">	
    	  <?php echo input_tag('year',date('Y')+1,array('class'=>'form-control input-small','type'=>'number')) ?>        
      </div>			
    </div>        
      
  </div>
</div> 
<?php echo ajax_modal_template_footer(TEXT_COPY) ?>

</form>  

<script>
  $(function(){
     if($('.holidays_checkbox:checked').length==0)
     {
       $('#modal-body-content').html('<?php echo TEXT_PLEASE_SELECT_ITEMS ?>')
       $('.btn-primary-modal-action').hide()
     }
     else
     {
       selected_fields_list = $('.holidays_checkbox:checked').serialize().replace(/holidays%5B%5D=/g,'').replace(/&/g,',');
       $('#selected_items').val(selected_fields_list);             
     } 


     $('#form-copy-to').validate({
 			submitHandler: function(form){
 				app_prepare_modal_action_loading(form)
 				form.submit();
 			}
     });        
  })
  
  
 
</script>