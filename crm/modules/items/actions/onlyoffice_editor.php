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

$field_id = _GET('field');
$file_id = _GET('file');

if(!isset_field($current_entity_id, $field_id))
{
    die(TEXT_RECORD_NOT_FOUND);
}

$field_info = $app_fields_cache[$current_entity_id][$field_id];
$cfg = new settings($field_info['configuration']);

$file_query = db_query("select * from app_onlyoffice_files where field_id={$field_id} and id={$file_id}");
if(!$file = db_fetch_array($file_query))
{
    die(TEXT_FILE_NOT_FOUD);
}

if(!is_file($filepath = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
{
    die(TEXT_FILE_NOT_FOUD);
}

switch ($app_module_action)
{   
    case 'open':                
        //echo $cfg->get('url_to_js_api');
        //echo $filepath;
        
        $documentInfo = onlyoffice::getDocumentInfo($file['filename']);
         // specify the document config
        $config = [
            "type" => 'desktop',
            "documentType" => $documentInfo['documentType'],
            "document" => [
                "title" => $file['filename'],
                "url" => onlyoffice::getDownloadUrl($file),                
                "fileType" => $documentInfo['fileType'],
                "key" => $file['filekey'],
                
                'permissions'=>[
                    'rename'=>false,
                    'protect'=>false,
                ],
                
                'referenceData' =>[
                    'fileKey' => $file_id,
                    'userAddress' => $_SERVER['HTTP_HOST'],
                ]
            ],
             "editorConfig" => [
                 'callbackUrl' =>url_for('onlyoffice/callback', 'entity_id=' . $current_entity_id . '&item_id=' . $current_item_id . '&field=' . $field_id. '&file=' . $file_id . '&date=' . $file['date_added']),
                 
                 'mode' => onlyoffice::getMode($cfg,$current_entity_id,$current_item_id),
                 
                 //localisation
                 'lang' => $cfg->get('lang'),
                 'location' => $cfg->get('location'),
                 'lang' => $cfg->get('region'),
                 
                 // the user currently viewing or editing the document
                 "user" => [  
                    "id" => base64_encode($_SERVER['HTTP_HOST'] . '-' . $app_user['id']),
                    "name" =>  $app_user['name'],
                    "group" => $app_user['group_name'],
                    ],
                 ],
            ];
        
        $token = \Firebase\JWT\JWT::encode($config, $cfg->get('secret_key'));
        
        $config['token'] = $token;
        
        //print_rr($config);
        //exit();
        
        $html = '
        <!DOCTYPE html>
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1,
                        maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui" />
                <meta name="apple-mobile-web-app-capable" content="yes" />
                <meta name="mobile-web-app-capable" content="yes" />
                <!--link rel="icon" href="css/images/{docType}.ico" type="image/x-icon" /-->
                <title>' . $file['filename'] . '</title>

                <style>
                    html {
                        height: 100%;
                        width: 100%;
                    }

                    body {
                        background: #333;
                        color: #333;
                        font-family: Arial, Tahoma,sans-serif;
                        font-size: 12px;
                        font-weight: normal;
                        height: 100%;
                        margin: 0;
                        overflow-y: hidden;
                        padding: 0;
                        text-decoration: none;
                    }

                    form {
                        height: 100%;
                    }

                    div {
                        margin: 0;
                        padding: 0;
                    }
                </style>

                <script type="text/javascript" src="' . $cfg->get('url_to_js_api') . '"></script> 
                
                <script>
                var сonnectEditor = function () {
                    config = ' . json_encode($config) . '
                    docEditor = new DocsAPI.DocEditor("iframeEditor", config);
                }
                
                if (window.addEventListener) {
                    window.addEventListener("load", сonnectEditor);
                } else if (window.attachEvent) {
                    window.attachEvent("load", сonnectEditor);
                }
                </script>
                
                ' . app_favicon() . '
                </head>
                <body>
                    <form id="form1">
                        <div id="iframeEditor">                        
                        </div>
                    </form>
                </body>
                </html>

            ';     
        
        echo $html;
        
        app_exit();
        break;            
}
