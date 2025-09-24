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


class curl_query
{

    public $title;
    public $site;
    public $api;
    public $version;

    function __construct()
    {
        $this->title = TEXT_MODULE_CURL_QUERY_TITLE;
        $this->site = '';
        $this->api = '';
        $this->version = '1';
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key' => 'url',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_URL,
            'description' => TEXT_MODULE_CURL_QUERY_URL_INFO,
            'params' => array('class' => 'form-control input-xlarge required'),
        );

        $cfg[] = array(
            'key' => 'postfields',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_CURL_QUERY_PARAMS,
            'description' => TEXT_MODULE_CURL_QUERY_PARAMS_INFO,
            'params' => array('class' => 'form-control input-xlarge required'),
        );

        $cfg[] = array(
            'key' => 'userpwd',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_CURL_QUERY_USERPWD,
            'description' => TEXT_MODULE_CURL_QUERY_USERPWD_INFO,
            'params' => array('class' => 'form-control input-xlarge'),
        );


        return $cfg;
    }

    function send($module_id, $destination = array(), $text = '')
    {
        global $alerts;


        $cfg = modules::get_configuration($this->configuration(), $module_id);
        $url = ($cfg['url']);

        foreach($destination as $phone)
        {
            $phone = preg_replace('/\D/', '', $phone);
            $text = strip_tags($text);

            $params = str_replace(array('[PHONE]', '[TEXT]'), array($phone, str_replace('&','',$text)), $cfg['postfields']);

            $postfields = [];
            parse_str($params, $postfields);
                        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            if(strlen($cfg['userpwd']))
            {
                curl_setopt($ch, CURLOPT_USERPWD, $cfg['userpwd']);
            }

            $result = curl_exec($ch);

            if(curl_errno($ch))
            {
                $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . curl_error($ch), 'error');
            }

            curl_close($ch);
        }
    }

}
