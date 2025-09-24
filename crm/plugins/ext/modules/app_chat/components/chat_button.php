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
<?php if($app_chat->has_access and $app_action!='chat_window'): ?>
	
	<div class="app-chat-button noprint" onClick="open_dialog('<?php echo url_for('ext/app_chat/chat_window') ?>')">
		<i class="fa fa-comments" aria-hidden="true"></i>&nbsp;
		<?php echo TEXT_EXT_CHAT_MESSAGES ?>
		<span id="app-chat-button-count-unread"><?php echo $app_chat->render_count_all_unrad() ?></span> 
	</div>
	
<script>
	var app_meta_title = $('title').html();
	
	app_chat_set_meta_title()
	
	setInterval(function(){
		$('#app-chat-button-count-unread').load('<?php echo url_for('ext/app_chat/chat','action=get_count_unrad_messages') ?>',function(){
				app_chat_set_meta_title()
			}) 	
	},10000);
</script>	
	
<?php endif ?>
