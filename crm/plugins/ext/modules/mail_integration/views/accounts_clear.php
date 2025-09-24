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

<?php echo ajax_modal_template_header(TEXT_EXT_CLEAR_EMAIL_ACCOUNT) ?>

<?php $obj = db_find('app_ext_mail_accounts',$_GET['id']); ?>

<?php echo form_tag('clear_form', url_for('ext/mail_integration/accounts','action=clear&id=' . $_GET['id'] )) ?>
    
<div class="modal-body"> 
	<?php echo '<div class="single-checkbox"><label>' . input_checkbox_tag('delete_confirm',1,['class'=>'required']) . ' ' . sprintf(TEXT_EXT_CLEAR_EMAIL_ACCOUNT_CONFIRM,$obj['login']) . '</label></div>'; ?>   
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_CONTINUE) ?>

</form>  

<script>
 $('#clear_form').validate({
	 submitHandler: function(form){
			app_prepare_modal_action_loading(form)
			form.submit();
		},
	 errorPlacement: function(error, element) {
		 error.insertAfter(".single-checkbox");                       
   }
 });
</script> 