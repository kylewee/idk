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

class app_recaptcha
{
    static function is_enabled()
    {
        return (self::is_google_enabled() or self::is_yandex_enabled()) ? true : false;        
    }

    static function is_google_enabled()
    {
        if(strlen(CFG_RECAPTCHA_KEY) and strlen(CFG_RECAPTCHA_SECRET_KEY) and CFG_RECAPTCHA_ENABLE == true)
        {
            if(!defined('CFG_RECAPTCHA_TRUSTED_IP')) define('CFG_RECAPTCHA_TRUSTED_IP','');
            
            if(strlen(CFG_RECAPTCHA_TRUSTED_IP) and in_array($_SERVER['REMOTE_ADDR'],array_map('trim',explode(',',CFG_RECAPTCHA_TRUSTED_IP))))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }
    
    static function is_yandex_enabled()
    {
        if(strlen(CFG_YANDEX_SMARTCAPTCHA_KEY) and strlen(CFG_YANDEX_SMARTCAPTCHA_SECRET_KEY) and CFG_YANDEX_SMARTCAPTCHA_ENABLE == true)
        {                        
            if(defined('CFG_YANDEX_SMARTCAPTCHA_TRUSTED_IP') and strlen(CFG_YANDEX_SMARTCAPTCHA_TRUSTED_IP) and in_array($_SERVER['REMOTE_ADDR'],array_map('trim',explode(',',CFG_YANDEX_SMARTCAPTCHA_TRUSTED_IP))))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }
    
    static function render_js()
    {
        if(self::is_google_enabled())
        {
            return self::render_google_js();
        }
        elseif(self::is_yandex_enabled())
        {
            return self::render_yandex_js();
        }
    }

    static function render_google_js()
    {       
        return '<script src="https://www.google.com/recaptcha/api.js?hl=' . APP_LANGUAGE_SHORT_CODE . '"></script>';        
    }
    
    static function render_yandex_js()
    {       
        return '<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>';        
    }
    
    static function render()
    {
        if(self::is_google_enabled())
        {
            return self::render_google();
        }
        elseif(self::is_yandex_enabled())
        {
            return self::render_yandex();
        }
    }

    static function render_google()
    {
        return '<div class="g-recaptcha" data-sitekey="' . CFG_RECAPTCHA_KEY . '"></div>';
    }
    
    static function render_yandex()
    {
        return '<div id="captcha-container" class="smart-captcha" data-sitekey="' . CFG_YANDEX_SMARTCAPTCHA_KEY . '"></div>';
    }
    
    
    static function verify()
    {        
        if(self::is_google_enabled())
        {
            return self::verify_google();
        }
        elseif(self::is_yandex_enabled())
        {            
            return self::verify_yandex();
        }                
    }

    static function verify_google()
    {
        require('includes/libs/ReCaptcha/ReCaptcha.php');
        require('includes/libs/ReCaptcha/RequestMethod.php');
        require('includes/libs/ReCaptcha/RequestParameters.php');
        require('includes/libs/ReCaptcha/Response.php');
        require('includes/libs/ReCaptcha/RequestMethod/Curl.php');
        require('includes/libs/ReCaptcha/RequestMethod/CurlPost.php');

        $recaptcha = new \ReCaptcha\ReCaptcha(CFG_RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        //print_r($resp->getErrorCodes());		
        //exit();

        return $resp->isSuccess();
    }
    
    static function verify_yandex()
    {
        $ch = curl_init("https://smartcaptcha.yandexcloud.net/validate");
        $args = [
            "secret" => CFG_YANDEX_SMARTCAPTCHA_SECRET_KEY,
            "token" => $_POST['smart-token']??'',
            "ip" => $_SERVER['REMOTE_ADDR'],                         
        ];
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_POST, true);    
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch); 
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {            
            return true;
        }

        $resp = json_decode($server_output);
        return $resp->status === "ok";
    }

}
