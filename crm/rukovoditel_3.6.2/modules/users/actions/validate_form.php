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

$msg = array();

if(CFG_ALLOW_REGISTRATION_WITH_THE_SAME_EMAIL==0 and isset($_POST['useremail']))
{
  $check_query = db_query("select count(*) as total from app_entity_1 where field_9='" . db_input($_POST['useremail']) . "' " . (isset($_GET['id']) ? " and id!='" . db_input($_GET['id']) . "'":''));
  $check = db_fetch_array($check_query);
  if($check['total']>0)
  {
    $msg[] = TEXT_ERROR_USEREMAL_EXIST;
  }
}

if(isset($_POST['username']))
{	
	$check_query = db_query("select count(*) as total from app_entity_1 where field_12='" . db_input($_POST['username']) . "' " . (isset($_GET['id']) ? " and id!='" . db_input($_GET['id']) . "'":''));
	$check = db_fetch_array($check_query);
	if($check['total']>0)
	{
	  $msg[] = TEXT_ERROR_USERNAME_EXIST;
	}
}

if(isset($_POST['password']) and strlen($_POST['password']))
{
    if(strlen($_POST['password'])<CFG_PASSWORD_MIN_LENGTH)
    {
        $msg[] = sprintf(TEXT_ERROR_PASSOWRD_LENGTH,CFG_PASSWORD_MIN_LENGTH);
    }
    
    if(CFG_IS_STRONG_PASSWORD)
    {
        if(!preg_match('/[A-Z]/', $_POST['password']) or !preg_match('/[0-9]/', $_POST['password']) or !preg_match('/[^\w]/', $_POST['password']))
        {
            $msg[] = TEXT_STRONG_PASSWORD_TIP;
        }
    }
}

if(count($msg)==0)
{
  echo 'success';
}
else
{
  echo implode('<br>',$msg);
}

exit();