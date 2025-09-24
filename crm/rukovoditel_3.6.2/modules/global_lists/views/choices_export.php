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
<?php echo ajax_modal_template_header(TEXT_EXPORT) ?>

<?php echo form_tag('modal_form', url_for('global_lists/choices','action=export&lists_id=' . $_GET['lists_id']),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('selected_fields') ?>
<div class="modal-body" >
  <div id="modal-body-content">    

<?php
    $list_info = db_find('app_global_lists',_GET('lists_id'));
?>
      <div class="form-group">
          <label class="col-md-3 control-label" for="parent_id"><?php echo TEXT_FILENAME ?></label>
          <div class="col-md-9">	
              <?php echo input_tag('filename',$list_info['name'],['class'=>'form-control required'])  ?>              
          </div>			
      </div>
        
  </div>
</div> 
<?php echo ajax_modal_template_footer() ?>

</form>  

<script>
  $(function(){
     $('#modal_form').validate();
     
     if($('.fields_checkbox:checked').length==0)
     {
       $('#modal-body-content').html('<?php echo TEXT_PLEASE_SELECT_ITEMS ?>')
       $('.btn-primary-modal-action').hide()
     }
     else
     {
       selected_fields_list = $('.fields_checkbox:checked').serialize().replace(/choices%5B%5D=/g,'').replace(/&/g,',');
       $('#selected_fields').val(selected_fields_list);              
     } 
     
              
  })     
</script>