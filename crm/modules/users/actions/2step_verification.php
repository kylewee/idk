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

//check if user is logged
if(!app_session_is_registered('app_logged_users_id') or CFG_2STEP_VERIFICATION_ENABLED != 1)
{
    redirect_to('users/login');
}

//check if is checked
if(isset($two_step_verification_info['is_checked']))
{
    redirect_to('dashboard/');
}

if(!isset($two_step_verification_info['code']))
{
    two_step_verification::send_code();
}

switch($app_module_action)
{
    case 'check':

        //chck form token
        app_check_form_token('users/login');

        if($two_step_verification_info['code'] == $_POST['code'])
        {
            two_step_verification::approve();
        }
        else
        {
            $alerts->add(TEXT_INCORRECT_CODE, 'error');
            
            two_step_verification::count_checks();
                        
            redirect_to('users/2step_verification');
        }

        break;
}

$app_layout = 'public_layout.php';
