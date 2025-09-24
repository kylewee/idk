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
chdir(substr(__DIR__, 0, -4));

define('IS_CRON', true);

//load core
require('includes/application_core.php');


//load app lagn
if(is_file($v = 'includes/languages/' . CFG_APP_LANGUAGE))
{
    require($v);
}

if(is_file($v = 'plugins/ext/languages/' . CFG_APP_LANGUAGE))
{
    require($v);
}

$app_users_cache = users::get_cache();
$app_module_action = '';

//check if API enabled
if(CFG_USE_API == 1)
{

    //check json request if POST is empty
    if (empty($_POST)) 
    {
        $jsonData = file_get_contents('php://input');
        // Decode the JSON data into a PHP associative array
        $data = json_decode($jsonData, true);
        // Check if decoding was successful
        if ($data !== null) 
        {
            $_REQUEST = $data;
        }
    }
    
    $api_key = api::_post('key');

    if(strlen(CFG_API_KEY) and CFG_API_KEY == $api_key)
    {
        $api = new api();
        $api->request();
    }
    else
    {
        api::response_error('API Key mismatch');
    }
}
else
{
    api::response_error('API is not enabled');
}