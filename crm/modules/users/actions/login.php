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

//force ldap login only
if(CFG_LDAP_USE == 1 and CFG_USE_LDAP_LOGIN_ONLY == 1 and $app_module_action != 'logoff')
{
    redirect_to('users/ldap_login');
}

//check security settings if they are enabled 
app_restricted_countries::verify();
app_restricted_ip::verify();

$app_layout = 'login_layout.php';

switch($app_module_action)
{
    case 'logoff':
        if(isset($app_user['id']))
        {
            who_is_online::remove($app_user['id']);
        }
        
        app_session_unregister('app_logged_users_id');
        app_session_unregister('app_current_version');
        app_session_unregister('two_step_verification_info');
        app_session_unregister('app_email_verification_code');
        app_session_unregister('app_session_token');

        setcookie('app_stay_logged', '', time() - 3600, '/');
        setcookie('app_remember_user', '', time() - 3600, '/');
        setcookie('app_remember_pass', '', time() - 3600, '/');
        setcookie('izoColorPickerColors', '', time() - 3600, '/');

        redirect_to('users/login');
        break;
    case 'login':

        //if only social login enabled
        if(CFG_ENABLE_SOCIAL_LOGIN==2)
        {
            redirect_to('users/login');
        }
        
        //chck form token
        app_check_form_token('users/login');

        //check reaptcha
        if(app_recaptcha::is_enabled())
        {
            if(!app_recaptcha::verify())
            {
                $alerts->add(TEXT_RECAPTCHA_VERIFY_ROBOT, 'error');
                redirect_to('users/login');
            }
        }
        
        //login attempt
        if(!login_attempt::verify())
        {
            $alerts->add(TEXT_LOGIN_ATTEMPT_VERIFY_ERROR, 'error');
            redirect_to('users/login');
        }

        users::login($_POST['username'], $_POST['password'], (isset($_POST['remember_me']) ? 1 : 0));

        break;
}

//check if user already logged
if(app_session_is_registered('app_logged_users_id'))
{
    redirect_to('dashboard/dashboard');
}