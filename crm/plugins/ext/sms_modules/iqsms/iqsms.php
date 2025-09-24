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


require('plugins/ext/sms_modules/iqsms/lib/iqsms_JsonGateV2.php');

use iqsms_JsonGateV2\iqsms_JsonGate;

class iqsms
{

    public $title;
    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_IQSMS_TITLE;
        $this->site = 'https://iqsms.ru';
        $this->api = 'https://iqsms.ru/api/api_json-php/';
        $this->version = '1.0';
        $this->country = 'RU';
    }

    public function configuration()
    {
        $cfg = array();


        $cfg[] = array(
            'key' => 'login',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_IQSMS_LOGIN,
            'params' => array('class' => 'form-control input-large required'),
        );

        $cfg[] = array(
            'key' => 'password',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_IQSMS_PASSWORD,
            'params' => array('class' => 'form-control input-large required'),
        );

        $cfg[] = array(
            'key' => 'sign',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_IQSMS_SIGN,
            'description' => TEXT_MODULE_IQSMS_SIGN_INFO,
            'params' => array('class' => 'form-control input-large'),
        );

        return $cfg;
    }

    function send($module_id, $destination = array(), $text = '')
    {
        global $alerts;

        $cfg = modules::get_configuration($this->configuration(), $module_id);

        $sms = new iqsms_JsonGate($cfg['login'], $cfg['password']);

        $i = 1;

        foreach($destination as $phone)
        {
            $phone = preg_replace('/\D/', '', $phone);

            $messages = array();
            $messages[] = array(
                'clientId' => $i++,
                'phone' => $phone,
                'text' => $text,
                'sender' => $cfg['sign']
            );

            $response = $sms->send($messages);
            
            //print_rr($response);
            //exit();

            if($response['status'] != 'ok')
            {
                $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $response['description'] . ' ' . $response['message']['0']['status'], 'error');
            }
        }
    }

}
