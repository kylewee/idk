<?php

/**
 * Google reCAPTCHA
 * If keys entered then reCAPTCHA will be display on login screen
 * You can get keys here https://www.google.com/recaptcha/admin 
 */
define('CFG_RECAPTCHA_ENABLE',false);
define('CFG_RECAPTCHA_KEY','');
define('CFG_RECAPTCHA_SECRET_KEY','');
define('CFG_RECAPTCHA_TRUSTED_IP',''); //Enter IP by comma. reCAPTCHA will be disabled for entered IP


/**
 * Yandex SmartCaptcha
 * If keys entered then Yandex SmartCaptcha will be display on login screen
 * You can get keys here https://yandex.cloud/ru/docs/smartcaptcha/quickstart
 * 
 * NOTE: ONLY ONE CAPTCHA CAN BE ENABLED 
 */
define('CFG_YANDEX_SMARTCAPTCHA_ENABLE',false);
define('CFG_YANDEX_SMARTCAPTCHA_KEY','');
define('CFG_YANDEX_SMARTCAPTCHA_SECRET_KEY','');
define('CFG_YANDEX_SMARTCAPTCHA_TRUSTED_IP',''); //Enter IP by comma. reCAPTCHA will be disabled for entered IP

/**
 * Restricted countries
 * Enter allowed countries list by comma, for example: RU,US
 */
define('CFG_RESTRICTED_COUNTRIES_ENABLE',false);
define('CFG_ALLOWED_COUNTRIES_LIST','');

/**
 * Restriction by IP
 * Enter allowed IP list by comma, for example: 192.168.2.1,192.168.2.2
 */
define('CFG_RESTRICTED_BY_IP_ENABLE',false);
define('CFG_ALLOWED_IP_LIST','');