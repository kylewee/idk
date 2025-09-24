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

<?php echo ajax_modal_template_header(TEXT_EXT_UPDATE_CURRENCIES) ?>

<?php echo form_tag('currencies_form', url_for('ext/currencies/currencies','action=update' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">

<?php $currencies = new currencies; ?>
		<div class="form-group">
	  	<label class="col-md-3 control-label" for="is_default"><?php echo TEXT_EXT_MODULE ?></label>
	    <div class="col-md-9">	
	  	  <?php echo select_tag('module',$currencies->get_modules(),CFG_CURRENCIES_UPDATE_MODULE,array('class'=>'form-control input-xlarge')) ?>
	  	  <?php echo tooltip_text(TEXT_EXT_CURRENCIES_MODULE_INFO . '<br>' . DIR_FS_CATALOG . 'cron/currencies.php') ?>	      
	    </div>			
	  </div>
 
   </div>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_UPDATE) ?>

</form> 

<script>
  $(function() { 
    $('#currencies_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });                                           
  }); 
</script>  