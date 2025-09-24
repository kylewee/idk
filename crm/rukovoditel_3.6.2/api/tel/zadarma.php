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

if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']);

chdir(substr(__DIR__,0,-8));

define('IS_CRON',true);

//load core
require('includes/application_core.php');

//error_log(date('Y-m-d H:i:s') . print_r($_REQUEST,true),3,'api/tel/log.txt');

if(isset($_REQUEST['event']) and in_array($_REQUEST['event'],['NOTIFY_OUT_END','NOTIFY_END']))
{
    $data = [
        'type' => 'phone',
        'direction' =>$_REQUEST['event']=='NOTIFY_OUT_END' ? 'out':'in',
        'date_added' => get_date_timestamp($_REQUEST['call_start']),
        'phone' => $_REQUEST['event']=='NOTIFY_OUT_END' ? $_REQUEST['destination']: $_REQUEST['caller_id'],
        'recording' => $_REQUEST['call_id_with_rec']??'',
        'client_name' => '',
        'duration' => $_REQUEST['duration'],
        'module' => 'zadarma',
    ];
    
    db_perform('app_ext_call_history', $data);
    
}