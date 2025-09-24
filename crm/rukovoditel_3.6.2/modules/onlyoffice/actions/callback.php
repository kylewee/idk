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

require('includes/libs/jwt/JWT.php'); 

header('Content-Type: application/json; charset==utf-8');
header('X-Robots-Tag: noindex');
header('X-Content-Type-Options: nosniff');

$entity_id = _GET('entity_id');
$item_id = _GET('item_id');
$field_id = _GET('field');
$file_id = _GET('file');
$date = _GET('date');

// check if item exist
$item_query = db_query("select * from app_entity_{$entity_id} where id={$item_id}");
if(!$item = db_fetch_array($item_query))
{
   onlyoffice::callback_error_log("Item {$item_id} not found in Entity {$entity_id}.");     
}

//check if field exist
if(!isset_field($entity_id, $field_id))
{
    onlyoffice::callback_error_log("Filed {$field_id} not found for Entity {$entity_id}.");    
}

$field_info = $app_fields_cache[$entity_id][$field_id];
$cfg = new settings($field_info['configuration']);

//check if file record exist
$file_query = db_query("select * from app_onlyoffice_files where field_id={$field_id} and id={$file_id} and date_added={$date}");
if(!$file = db_fetch_array($file_query))
{
    onlyoffice::callback_error_log("File {$file_id} not found in database.");    
}

//check if file exist on disk
if(!is_file($filepath = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
{
    onlyoffice::callback_error_log("File #{$file_id} (" . $file['filename'] . ") not found in disk.");    
}

// get the body of the post request and check if it is correct
if (($body_stream = file_get_contents('php://input')) === false) {
    onlyoffice::callback_error_log("Bad Request. Contact to onlyoffice server administrator.");        
}

$data = json_decode($body_stream, true);

// check if the response is correct
if ($data === null) {
    onlyoffice::callback_error_log("Bad Response. Contact to onlyoffice server administrator.");            
}

if (empty($data["token"]))
{
    onlyoffice::callback_error_log("Token is empty.");            
}

$data = \Firebase\JWT\JWT::decode($data["token"],$cfg->get('secret_key'),["HS256"]);  // decode it

//error_log(date('Y-m-d H:i:s') . ' DATA:' . "\n " . print_r($data,true) . "\n\n",3,'log/onlyoffice_callback.log');

/*
 * Save file
 * Status description: https://api.onlyoffice.com/editors/callback#status-descr
 */
if( in_array($data->status,[2,3]) and !empty($data->url))
{
    $url = $data->url;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $content = curl_exec($curl);
    curl_close($curl);
    
    if($content)
    {
        if(!file_put_contents($filepath, $content, LOCK_EX))
        {
            onlyoffice::callback_error_log("Can't save file to {$filepath}. Check file permissions.");
        }
    }
    else
    {
        onlyoffice::callback_error_log("Can't get file content to save.");            
    }
    
    $sql_data = [
        'filekey' => onlyoffice::genFileKey($file['id']),                
        'download_token' =>'', //reset download token
    ];

    db_perform('app_onlyoffice_files', $sql_data,'update','id=' . $file['id']);
}

/*
 * Status 4: It is received after the document is closed for editing with no changes by the last user. Their callbackUrl is used.
 */
if($data->status==4)
{
    $sql_data = [        
        'download_token' =>'', //reset download token
    ];

    db_perform('app_onlyoffice_files', $sql_data,'update','id=' . $file['id']);
}

$response = [
    'error'=>0,
    'status' => 'success'
    ];
        
die(json_encode($response));
