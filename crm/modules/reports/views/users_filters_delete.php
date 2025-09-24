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

<?php echo ajax_modal_template_header(TEXT_DELETE_FILTERS) ?>

<?php echo form_tag('users_filters', url_for('reports/users_filters','action=delete&reports_id=' . $_GET['reports_id'] ),array('class'=>'form-horizontal')) ?>

<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>
<?php if(isset($_GET['path'])) echo input_hidden_tag('path',$_GET['path']) ?>
<?php
  $users_filters = new users_filters($_GET['reports_id']);
?>

<div class="modal-body">
  <div class="form-body">
  
     
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
    <div class="col-md-9">	
  	  <?php echo select_checkboxes_tag('filters',$users_filters->get_choices(),'',array('class'=>'form-control required')) ?>
    </div>			
  </div> 
           
   </div>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form> 

<script>
  $(function() { 
    $('#users_filters').validate();                                                                              
  });     
</script>  

    
 
