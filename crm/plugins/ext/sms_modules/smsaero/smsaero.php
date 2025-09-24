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

require('plugins/ext/sms_modules/smsaero/lib/smsareo_api2.php');

use SmsaeroApiV2\SmsaeroApiV2;

class smsaero
{
	public $title;
	
	public $site;
        public $api;
        public $version;
        public $country;
	
	function __construct()
	{
		$this->title = TEXT_MODULE_SMSAERO_TITLE;
		$this->site = 'https://smsaero.ru';
		$this->api = 'https://smsaero.ru/description/api/';
		$this->version = '2.1';
                $this->country = 'RU';
	}
	
	public function configuration()
	{
		$cfg = array();
		
					
		$cfg[] = array(
				'key'	=> 'login',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSAERO_LOGIN,				
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'password',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSAERO_PASSWORD,
				'description' =>TEXT_MODULE_SMSAERO_PASSWORD_INFO,
				'params' =>array('class'=>'form-control input-large required'),
		);
					
		$cfg[] = array(
				'key'	=> 'sign',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSAERO_SIGN,
				'description' =>TEXT_MODULE_SMSAERO_SIGN_INFO,
				'params' =>array('class'=>'form-control input-large'),				
		);
		
		$cfg[] = array(
				'key'	=> 'channel',
				'type' => 'dorpdown',
				'default' => 'INFO',
				'choices'=>array(
						'INFO'=>'INFO',
						'DIGITAL'=>'DIGITAL',
						'INTERNATIONAL'=>'INTERNATIONAL',
						'DIRECT'=>'DIRECT',
						'SERVICE'=>'SERVICE',
				),
				'description'=>TEXT_MODULE_SMSAERO_CHANNEL_INFO,
				'title'	=> TEXT_MODULE_SMSAERO_CHANNEL,
				'params' =>array('class'=>'form-control input-medium required'),
		);
					
				
		return $cfg;
	}
		
	function send($module_id, $destination = array(),$text = '')
	{		
		global $alerts;
						
		$cfg = modules::get_configuration($this->configuration(),$module_id);
		
		$channel = (!strlen($cfg['channel']) ? 'INFO' : $cfg['channel']);
		
		$sms = new SmsaeroApiV2($cfg['login'],$cfg['password'],$cfg['sign']);
						
		foreach($destination as $phone)
		{
			$phone  = preg_replace('/\D/', '', $phone);
			
			$response = $sms->send($phone,$text,$channel);
																	
			if(!$response['success'])
			{
				$alerts->add($this->title . ' ' . TEXT_ERROR . ' ' .  $response['message'] . '<br>' . print_r($response['data'],true),'error');
			}			
		}					
	}				
	
}