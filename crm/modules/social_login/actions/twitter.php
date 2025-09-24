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

require 'includes/libs/social_login/Twitter/src/Config.php';
require 'includes/libs/social_login/Twitter/src/Response.php';
require 'includes/libs/social_login/Twitter/src/SignatureMethod.php';
require 'includes/libs/social_login/Twitter/src/HmacSha1.php';
require 'includes/libs/social_login/Twitter/src/Consumer.php';
require 'includes/libs/social_login/Twitter/src/Util.php';
require 'includes/libs/social_login/Twitter/src/Request.php';
require 'includes/libs/social_login/Twitter/src/TwitterOAuthException.php';
require 'includes/libs/social_login/Twitter/src/Token.php';
require 'includes/libs/social_login/Twitter/src/Util/JsonDecoder.php';
require 'includes/libs/social_login/Twitter/src/TwitterOAuth.php';

if(isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier']))
{
    
}
else
{
    /*
     * http://support.heateor.com/how-to-get-twitter-api-key-and-secret/
     */
    
    $connection = new Abraham\TwitterOAuth\TwitterOAuth(CFG_TWITTER_APP_ID, CFG_TWITTER_SECRET_KEY);
    $requestToken = $connection->oauth('oauth/request_token', ['oauth_callback' => url_for('social_login/twitter')]);
    
    if($connection->getLastHttpCode() == 200)
    {
        $url = $connection->url('oauth/authorize', ['oauth_token' => $requestToken['oauth_token']]);
        header('Location: ' . $url);
    }
}

exit();
