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

if(isset($_GET['download']))
{
    $filename = 'calendar-' . $_GET['type'] . (isset($_GET['id']) ? '-' . (int)$_GET['id']: '') . '.ics';
    header( 'Content-Type: text/calendar; charset=utf-8' );    
    header( 'Content-Disposition: attachment; filename="'.$filename.'"' );  
}
else
{
    header('Content-type: text/plain; charset=utf-8');
}



header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once("includes/libs/icalendar-master/zapcallib.php");

$client_id = _GET('client');
$type = $_GET['type'];
$reports_id = (isset($_GET['id']) ? (int)$_GET['id']: false);

//check if user exist by client ID and user is active
$user_query = db_query("select * from app_entity_1 where client_id='{$client_id}' and field_5=1");
if(!$user = db_fetch_array($user_query))
{
    die(TEXT_NO_ACCESS);
}
else
{
    $app_user = array(
          'id'=>$user['id'],          
          'group_id'=>(int)$user['field_6']
            );
    
    //generat users access to entities schema
    if($app_user['group_id'] > 0)
    {
        $app_users_access = users::get_users_access_schema($app_user['group_id']);
    }
    else
    {
        $app_users_access = array();
    }
        
}


$icalendar = new icalendar($type,$reports_id);

$icalendar->export();

app_exit();