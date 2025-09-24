<script>
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

$(function(){
	
//start chat	
		$('.chat-to-user').click(function(){

			if(!app_caht_selection_process)
			{
				app_caht_selection_process = true		
			}
			else
			{
				return false;
			}
			
			$('.chat-user').removeClass('selected')
			$(this).addClass('selected')
			$('.chat-user-count-new-msg',this).addClass('hidden');
			
		  assigned_to = $(this).attr('data-assigned-to');

			//reset timer
			if(is_app_caht_timer)
			{		
				clearInterval(app_caht_timer)
				is_app_caht_timer = false;
			}

			$('#chat_messages').html('<div class="fa-ajax-loader fa fa-spinner fa-spin"></div>');
			
			$('#chat_messages').load('<?php echo url_for('ext/app_chat/messages')?>',{assigned_to:assigned_to},function(){
													
				})
		})
		
//start conversation	
		$('.chat-to-conversation').click(function(){

			if(!app_caht_selection_process)
			{
				app_caht_selection_process = true		
			}
			else
			{
				return false;
			}
			
			$('.chat-user').removeClass('selected')
			$(this).addClass('selected')
			$('.chat-user-count-new-msg',this).addClass('hidden');
			
		  assigned_to = $(this).attr('data-assigned-to');

			//reset timer
			if(is_app_caht_timer)
			{		
				clearInterval(app_caht_timer)
				is_app_caht_timer = false;
			}

			$('#chat_messages').html('<div class="fa-ajax-loader fa fa-spinner fa-spin"></div>');
			
			$('#chat_messages').load('<?php echo url_for('ext/app_chat/conversation_messages')?>',{assigned_to:assigned_to},function(){
													
				})
		})		
	
})		
</script>