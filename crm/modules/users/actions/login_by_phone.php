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

if(CFG_2STEP_VERIFICATION_ENABLED!=1 or CFG_LOGIN_BY_PHONE_NUMBER!=1 or CFG_2STEP_VERIFICATION_TYPE!='sms' or !isset($app_fields_cache[1][CFG_2STEP_VERIFICATION_USER_PHONE]))
{
	redirect_to('users/login');	
}

//check security settings if they are enabled
app_restricted_countries::verify();
app_restricted_ip::verify();

if(app_session_is_registered('app_logged_users_id'))
{		
	redirect_to('users/login','action=logoff');
}

$app_layout = 'login_layout.php';


switch($app_module_action)
{
	case 'login':

		//chck form token
		app_check_form_token('users/login_by_phone');

		//check reaptcha
		if(app_recaptcha::is_enabled())
		{
			if(!app_recaptcha::verify())
			{
				$alerts->add(TEXT_RECAPTCHA_VERIFY_ROBOT,'error');
				redirect_to('users/login_by_phone');
			}
		}
			
		//check phone
		if(!strlen(preg_replace('/\D/', '', $_POST['phone'])))
		{			
			$alerts->add(TEXT_USER_IS_NOT_FOUD,'error');
			redirect_to('users/login_by_phone');
		}
		
		//check if user exist with this phone
		$user_query = db_query("select id from app_entity_1 where length(field_" . CFG_2STEP_VERIFICATION_USER_PHONE . ")>0 and rukovoditel_regex_replace('[^0-9]','',field_" . CFG_2STEP_VERIFICATION_USER_PHONE . ") = '" . db_input(preg_replace('/\D/', '', $_POST['phone'])) . "'");
		if($user = db_fetch_array($user_query))
		{
			app_session_register('app_logged_users_id',$user['id']);
			
			redirect_to('users/2step_verification');
		}
		else
		{
			$alerts->add(TEXT_USER_IS_NOT_FOUD,'error');
			redirect_to('users/login_by_phone');
		}
					
		break;
}