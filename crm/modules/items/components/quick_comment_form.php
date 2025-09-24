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
<div id="quick-comment">		
	<?php echo form_tag('quick_comments_form', url_for('items/comments','action=save&is_quick_comment=true' ),array('class'=>'form-horizontal')) ?>
		<?php echo input_hidden_tag('path',$_GET['path']) ?>	
		<?php echo textarea_tag('quick_comments_description','',array('class'=>'form-control required','placeholder'=>TEXT_COMMENT_PLACEHOLDER))?>		
		<?php echo submit_tag(TEXT_BUTTON_SAVE,array('class'=>'btn btn-primary btn-sm btn-primary-modal-action')) . ' <div class="fa fa-spinner fa-spin primary-modal-action-loading"></div> <button onClick="quick_comment_toggle()" type="button" class="btn btn-sm btn-default">' . TEXT_BUTTON_CANCEL. '</button>' ?>							 
	</form>				
</div>

<script>
	function quick_comment_toggle()
	{
		$('#quick-comment').toggle();
		$('#quick_comments_description').focus();
	}
	
  $(function() {   
    $("#quick_comments_form").validate({
    	submitHandler: function(form)
      {
    		app_prepare_modal_action_loading(form);	
    		form.submit(); 
      }
    });                                                                                   
  });   
</script>