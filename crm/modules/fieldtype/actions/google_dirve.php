<?php

/* 
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */

switch($app_module_action)
{
    case 'get_access_token':
        $app_code = $_POST['app_code'];
        $client_id = $_POST['client_id'];
        $app_secret = $_POST['app_secret'];
        
        $redirectUrl = url_for('dashboard/dashboard');
        
        $GoogleDriveApi = new GoogleDriveApi();
                    
        $access_token = false;
        $response = [];
                    
        try
        {                                                
            $data = $GoogleDriveApi->GetAccessToken($client_id, $redirectUrl, $app_secret, $app_code); 
            $access_token = $data['access_token']; 
            
        }
        catch(Exception $e)
        {            
            $response = [
                'error' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
           
        }  
        
        if($access_token)
        {
            $response = [                
                'access_token' => $access_token,
            ];
        }
        
        echo json_encode($response);
        
        exit();
        
        break;
}