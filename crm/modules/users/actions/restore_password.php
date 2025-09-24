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

	//check security settings if they are enabled
	app_restricted_countries::verify();
	app_restricted_ip::verify();

  $app_layout = 'login_layout.php';

  switch($app_module_action)
  {
    case 'restore':    
     	  //chck form token
    	  app_check_form_token('users/restore_password');
    	  
	    	if(app_recaptcha::is_enabled())
	    	{
	    		if(!app_recaptcha::verify())
	    		{
	    			$alerts->add(TEXT_RECAPTCHA_VERIFY_ROBOT,'error');
	    			redirect_to('users/restore_password');
	    		}
	    	}   	
    	
        $user_query = db_query("select * from app_entity_1 where field_12='" . db_input($_POST['username']) . "' and field_9='" . db_input($_POST['email']) . "'");
        if($user = db_fetch_array($user_query))
        {
           if($user['field_5']==1)
           {
             $hasher = new PasswordHash(11, false);
  
             $password = users::get_random_password();
                          
             $sql_data = array();  
             $sql_data['password']=$hasher->HashPassword($password);
          
             db_perform('app_entity_1',$sql_data,'update',"id='" . db_input($user['id']). "'");
            
             $options = array('to' => $user['field_9'],
                              'to_name' => $user['field_7'] . ' ' . $user['field_8'],
                              'subject'=> TEXT_RESTORE_PASSWORD_EMAIL_SUBJECT,
                              'body'=>TEXT_RESTORE_PASSWORD_EMAIL_BODY . '<p><b>' . TEXT_LOGIN_DETAILS . '</b></p><p>' . TEXT_USERNAME .': ' . $user['field_12'] . '<br>' . TEXT_PASSWORD . ': ' . $password . '</p><p><a href="' . url_for('users/login','',true) . '">' . url_for('users/login','',true). '</a></p>',
                              'from'=> CFG_EMAIL_ADDRESS_FROM,
                              'from_name'=>CFG_EMAIL_NAME_FROM,
             									'send_directly' => true,             		
             );
                             
             users::send_email($options);
             
             $alerts->add(TEXT_RESTORE_PASSWORD_SUCCESS,'success');
             redirect_to('users/login');
           
           }
           else
           {
             $alerts->add(TEXT_USER_IS_NOT_ACTIVE,'error');
             redirect_to('users/restore_password');
           }
        }
        else
        {
          $alerts->add(TEXT_USER_NOT_FOUND,'error');
          redirect_to('users/restore_password');
        }
                        
      break;
  }