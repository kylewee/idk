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

$phone = '';
$field_id = _get::int('field_id');

$item_info = db_find('app_entity_' . $current_entity_id, _get::int('item_id'));

if(isset($item_info['field_' . $field_id]))
{
    $phone = db_prepare_input($item_info['field_' . $field_id]);
}
else
{
    exit();
}

$module_info_query = db_query("select * from app_ext_modules where id='" . _GET('module_id') . "' and type='telephony' and is_active=1");
if($module_info = db_fetch_array($module_info_query))
{
    modules::include_module($module_info,'telephony');
    
    $module = new $module_info['module'];    
}
else
{
    exit();
}

switch($app_module_action)
{
    
    case 'send':
                
        $message_text = db_prepare_html_input($_POST['message_text']);
        
        $restul = $module->sms_to_number($module_info['id'], $phone, $message_text);
                
        if(!$restul)
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
                'phone' => preg_replace('/\D/', '',$phone),
                'duration' => 0,
                'sms_text' => $message_text,
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
