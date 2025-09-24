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

<?php echo ajax_modal_template_header(TEXT_EXT_EMPTY_TRASH) ?>


<?php echo form_tag('empty_trash', url_for('ext/mail/accounts','action=empty_trash')) ?>
    
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE ?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_EMPTY) ?>

</form> 

<script>
$(function() {     
    $('#empty_trash').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });
});   
</script>     
     