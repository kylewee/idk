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

$client_id = _GET('client');
$rss_id = _GET('rss');

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

$feed_query = db_query("select * from app_ext_rss_feeds where (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to)) and rss_id={$rss_id}");
if(!$feed = db_fetch_array($feed_query))
{
    die(TEXT_NO_RECORDS_FOUND);
}

header("Content-Type: text/xml");

 echo '
    <rss version="2.0">
        <channel>
            <title>' . $feed['name'] . '</title>
    '; 
 
$rss_feed = new rss_feed($feed);
echo $rss_feed->render();

echo '
        </channel>
    </rss>
    ';
        

app_exit();