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
    case 'get_refresh_token':
        $app_code = $_POST['app_code'];
        $app_key = $_POST['app_key'];
        $app_secret = $_POST['app_secret'];
        
        $endpoint = 'https://api.dropbox.com/oauth2/token?code=' . $app_code . '&grant_type=authorization_code';
        
        $ch = curl_init($endpoint);
        $headers = array("Content-Type: application/json");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, "$app_key:$app_secret");
        curl_setopt($ch, CURLOPT_POSTFIELDS, []);
        $r = curl_exec($ch);
        curl_close($ch);
                        
        echo $r;
        
        exit();
        
        break;
}
