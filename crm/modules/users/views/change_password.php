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

<h3 class="page-title"><?php echo  TEXT_HEADING_CHANGE_PASSWORD ?></h3>


<?php echo form_tag('change_password_form', url_for('users/change_password','action=change'),array('class'=>'form-horizontal')) ?>
  <div class="form-body">
  
    <div class="form-group">
    	<label class="col-md-3 control-label" for="password_new"><?php echo TEXT_NEW_PASSWORD ?></label>
      <div class="col-md-9">	
    	  <?php  echo input_password_tag('password_new',array('autocomplete'=>'off','class'=>'form-control input-medium required')) ?>
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="password_confirmation"><?php echo TEXT_PASSWORD_CONFIRMATION ?></label>
      <div class="col-md-9">	
    	  <?php  echo input_password_tag('password_confirmation',array('autocomplete'=>'off','class'=>'form-control input-medium  required')) ?>
      </div>			
    </div> 
    
<?php echo submit_tag(TEXT_BUTTON_CHANGE,array('class'=>'btn btn-primary'))  ?>     
 
  </div>
</form>

<script>
  $(function() { 
    $('#change_password_form').validate();                                                                            
  });    
</script> 