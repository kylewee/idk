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
switch($app_module_action)
{

	case 'send':
		
		$module_id = _post::int('module_id');
		$phone = db_prepare_input($_POST['phone']);
		$message_text = db_prepare_html_input($_POST['message_text']);
		
		sms::send_by_module($module_id, $phone, $message_text);
		
		if($alerts->count())
		{
			echo $alerts->output();
			
			echo '
					<script>
						$(".primary-modal-action-loading").hide();
					</script>
					';
		}
		else
		{
			$sql_data = [
					'type' => 'sms',
					'date_added' => time(),
					'direction' =>'',
					'phone' => db_prepare_input(preg_replace('/\D/', '', $_POST['phone'])),
					'duration' => 0,
					'sms_text' => db_prepare_html_input($_POST['message_text']),
			];
			
			db_perform('app_ext_call_history', $sql_data);
			
			echo '<div class="alert alert-success">' . TEXT_EXT_MESSAGE_SENT . '</div>';
			echo '
					<script>
						setTimeout(function(){
							$("#ajax-modal").modal("toggle");
						}, 1000);						
					</script>
			';
			
		}
		
		exit();
		break;
}