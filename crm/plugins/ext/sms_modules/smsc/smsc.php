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


class smsc
{
	public $title;
	
	public $site;
        public $api;
        public $version;
        public $country;
	
	function __construct()
	{
		$this->title = TEXT_MODULE_SMSC_TITLE;
		$this->site = 'http://smsc.ru';
		$this->api = 'http://smsc.ru/api/';
		$this->version = '1.0';
                $this->country = 'RU';
	}
	
	public function configuration()
	{
		$cfg = array();
		
					
		$cfg[] = array(
				'key'	=> 'login',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSC_LOGIN,				
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'password',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SMSC_PASSWORD,				
				'params' =>array('class'=>'form-control input-large required'),
		);
					
		$cfg[] = array(
				'key'	=> 'use_https',
				'type' => 'dorpdown',
				'default' => 0,
				'choices'=>array(
						'0'=>TEXT_NO,
						'1'=>TEXT_YES,						
				),				
				'title'	=> TEXT_MODULE_SMSC_USE_HTTPS,				
				'params' =>array('class'=>'form-control input-small'),				
		);
							
				
		return $cfg;
	}
		
	function send($module_id, $destination = array(),$text = '')
	{		
		global $alerts;
						
		$cfg = modules::get_configuration($this->configuration(),$module_id);
						
		$url = ($cfg['use_https']==1 ? "https" : "http") . "://smsc.ru/sys/send.php";
							
		foreach($destination as $phone)
		{
			$phone  = preg_replace('/\D/', '', $phone);
			
			$params=[
					'login' => $cfg['login'],
					'psw' => $cfg['password'],
					'phones'=> $phone,
					'mes' => strip_tags($text),
					'charset' => 'utf-8',
					'fmt' => 3,
			];
				
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$result = curl_exec($ch);
			curl_close($ch);
									
			$result = json_decode($result,true);
			
			//print_r($result);
			
			if(isset($result['error']))
			{
				$alerts->add($this->title . ' ' . TEXT_ERROR . ' ' .  $result['error'] . '; error_code: ' . $result['error_code'],'error');
			}
						
		}					
	}				
	
}