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

chdir(substr(__DIR__,0,-8));

define('IS_CRON',true);

//load core
require('includes/application_core.php');
require('plugins/ext/telephony_modules/megafon/megafon.php');

$result = $_POST;

//print_rr($result);

//error_log(print_r($result,true),3,'api/log.txt');

if(isset($result['cmd']) and $result['cmd']=='history')
{
    $data = [
        'type' => 'phone',
        'direction' => $result['type'],
        'date_added' =>time(),
        'phone' => $result['phone'],
        'recording' => $result['link'],
        'client_name' => '',
        'duration' => ($result['status']=='Success' ? $result['duration'] : 0),
    ];
    
	$crm_key = megafon::get_crm_key();
                
	if(isset($result['crm_token']) and $result['crm_token']==$crm_key) 
	{
		db_perform('app_ext_call_history', $data);
	}	
} 