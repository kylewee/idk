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

class smsgateway24
{
	public $title;
	
	public $site;
        public $api;
        public $version;
        public $country;
	
	function __construct()
	{
		$this->title = 'SmsGateWay24';
		$this->site = 'https://smsgateway24.com';
		$this->api = 'https://smsgateway24.com/ru/docs/apidocumentation';
		$this->version = '1.0';
	}
	
	public function configuration()
	{
		$cfg = array();
		
		$cfg[] = array(
				'key'	=> 'token',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSGATEWAY24_TOKEN,				
				'description'	=> '',
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'device_id',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSGATEWAY24_DEVICE_ID,
				'description'	=> '',
				'params' =>array('class'=>'form-control input-small required'),
		);
		
		$cfg[] = array(
				'key'	=> 'sim',
				'type' => 'input',
				'default' => 0,
				'title'	=> TEXT_MODULE_SMSGATEWAY24_SIM,
				'description'	=> '',				
				'params' =>array('class'=>'form-control input-xsmall required'),
		);
							
				
		return $cfg;
	}
		
	function send($module_id, $destination = array(),$text = '')
	{		
		global $alerts;
					
		$cfg = modules::get_configuration($this->configuration(),$module_id);
				
		foreach($destination as $phone)
		{									
			if(!strstr($phone,'+')) $phone = '+' . $phone;
			
			$params=[
					'token' => $cfg['token'],
					'sendto' => $phone,
					'body' => strip_tags($text),
					'device_id' => $cfg['device_id'],
					'sim' => $cfg['sim'],
			];
									
			$ch = curl_init('https://smsgateway24.com/getdata/addsms');
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);			
			$result = curl_exec($ch);
			curl_close($ch);
			
			//print_r($result);
			//exit();
			
			if($result)
			{
				$result = json_decode($result,true);
				
				if($result['error']==1)
				{
					$alerts->add($this->title . ' ' . TEXT_ERROR .  ' ' .  $result['message'] ,'error');
				}
			}	
			
		}
	}				
	
}