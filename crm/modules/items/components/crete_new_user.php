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

$hasher = new PasswordHash(11, false);

if(strlen(trim($_POST['password']))>0)
{
  $password = trim($_POST['password']);
}
else
{
  $password = users::get_random_password();
}

$sql_data['password']=$hasher->HashPassword($password);

$to_name = (CFG_APP_DISPLAY_USER_NAME_ORDER=='firstname_lastname' ? $_POST['fields'][7] . ' ' . $_POST['fields'][8] : $_POST['fields'][8] . ' ' . $_POST['fields'][7]);


if(strstr(CFG_REGISTRATION_EMAIL_BODY??'','[password]'))
{
    $login_details = '';
}
else
{
    $login_details = '<p><b>' . TEXT_LOGIN_DETAILS . '</b></p><p>' . TEXT_USERNAME .': ' . $_POST['fields'][12] . '<br>' . TEXT_PASSWORD . ': ' . $password . '</p><p><a href="' . url_for('users/login','',true) . '">' . url_for('users/login','',true). '</a></p>';
}

$body = str_replace(['[FirstName]', '[LastName]', '[password]'],[db_prepare_input($_POST['fields'][7]), db_prepare_input($_POST['fields'][8]), $password],CFG_REGISTRATION_EMAIL_BODY??'');

$item = ['id'=>0];
foreach($_POST['fields'] as $k=>$v)
{
    $item['field_' . $k] =  $v;
}
$fieldtype_text_pattern = new fieldtype_text_pattern();
$body = $fieldtype_text_pattern->output_singe_text($body, 1, $item, ['is_email'=>true]);

$options = array('to' => $_POST['fields'][9],
                 'to_name' => $to_name,
                 'subject'=>((!is_null(CFG_REGISTRATION_EMAIL_SUBJECT) and strlen(CFG_REGISTRATION_EMAIL_SUBJECT))>0 ? CFG_REGISTRATION_EMAIL_SUBJECT :TEXT_NEW_USER_DEFAULT_EMAIL_SUBJECT),
                 'body'=> $body . $login_details,
                 'from'=> CFG_EMAIL_ADDRESS_FROM,
                 'from_name'=> CFG_EMAIL_NAME_FROM );
                 
users::send_email($options);