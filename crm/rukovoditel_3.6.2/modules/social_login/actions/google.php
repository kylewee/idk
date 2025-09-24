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

if(isset($_GET['code']) && isset($_GET['state']))
{
    
    $postData = array(
        'grant_type' => 'authorization_code',
        'code' => urldecode(trim($_GET['code'])),
        'redirect_uri' => url_for('social_login/google'),
        'client_id' => CFG_GOOGLE_APP_ID,
        'client_secret' => CFG_GOOGLE_SECRET_KEY
    );
    
    //print_rr($postData);

    $ch = curl_init("https://accounts.google.com/o/oauth2/token");    
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $response = json_decode($response,true);

    if(isset($response['error']))
    {
        $alerts->add(TEXT_ERROR . ' ' . $response['error'] . ' (' . $response['error_description'] . ')','error');
        redirect_to('users/login');
    }
    else
    {
        $authorization = "Bearer " . $response['access_token'];
        
        $ch = curl_init("https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $response['access_token']);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);        
        $response = curl_exec($ch);
        curl_close($ch);
        
        //echo $response;

        $response = json_decode($response,true);
        
        if(isset($response['email']))
        {
            $social_login->set_user([
                    'first_name' => $response['given_name'],
                    'last_name' => $response['family_name'],
                    'photo' => $response['picture'],
                    'email' => $response['email']]);
            
            $social_login->login();
        }
    }          
}
else
{
    $url = "https://accounts.google.com/o/oauth2/auth?client_id=" . CFG_GOOGLE_APP_ID . "&scope=https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile&state=". mt_rand() ."&response_type=code&redirect_uri=" . url_for('social_login/google');
    header('Location: ' . $url);
}

exit();
