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

include("includes/libs/social_login/Vkontakte/Vkontakte.php");

$heateorSsVkontakte = new Vkontakte(array(
    'client_id' => CFG_VKONTAKTE_APP_ID,
    'client_secret' => CFG_VKONTAKTE_SECRET_KEY,
    'redirect_uri' => url_for('social_login/vkontakte')
        ));
$heateorSsVkontakte -> setScope(array('email'));


if(isset($_GET['code']))
{
    $heateorSsVkontakte->authenticate($_GET['code']);
    $userId = $heateorSsVkontakte->getUserId();
    $email = $heateorSsVkontakte->getUserEmail();
    
    //check if there is accass to email
    if(!$email)
    {
        $alerts->add(TEXT_ERROR_USEREMAL_EMPTY,'error');
        redirect_to('users/login');
    }
    
    if($userId)
    {
        $users = $heateorSsVkontakte -> api('users.get', array(
            'user_id' => $userId,
            'fields' => array('first_name', 'last_name', 'nickname', 'screen_name', 'photo_rec', 'photo_big')
        ));
        
        if(isset($users[0]) && isset($users[0]["id"]) && $users[0]["id"])
        {                        
            $social_login->set_user([
                    'first_name' => $users[0]['first_name'],
                    'last_name' => $users[0]['last_name'],
                    'photo' => $users[0]['photo_rec'],
                    'email' => $email]);
            
            $social_login->login();
        }
        
    }
}
else
{
    header('Location: ' . $heateorSsVkontakte->getLoginUrl());
}

exit();
