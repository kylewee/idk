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


$result = json_decode(file_get_contents("php://input"), true);

//error_log(print_r($result,true),3,'log.txt');

if(isset($result['webhook']) and $result['webhook']['action']=='call.finish' and isset($result['event']))
{
    $data = [
        'type' => 'phone',
        'direction' => $result['event']['direction']==1 ? 'out':'in',
        'date_added' =>$result['event']['end_time'],
        'phone' => $result['event']['client_number'],
        'recording' => $result['event']['recording'],
        'client_name' => $result['event']['client_name'],
        'duration' => $result['event']['duration'],
    ];
    
    db_perform('app_ext_call_history', $data);
} 