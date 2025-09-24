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
<?php
if(IS_AJAX)
{
    echo ajax_modal_template_header(TEXT_EXT_CHAT_DIALOGUES);
}
else
{
    echo '<h3 class="page-title">' . TEXT_EXT_CHAT_DIALOGUES . '</h3>';
}
?>

<div class="<?php echo (IS_AJAX ? 'modal-body':'') ?> chat-modal-body">
	<div class="ajax-modal-width-1100">
		<?php require(component_path('ext/app_chat/chat')); ?>
	</div>
</div>


<div class="modal-footer chat-modal-footer">    
  
</div>
      
  
<script>
  jQuery(document).ready(function() {                  
     appHandleUniform()                     
  });
</script>