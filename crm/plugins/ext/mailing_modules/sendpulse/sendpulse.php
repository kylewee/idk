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


require("plugins/ext/mailing_modules/sendpulse/api/src/ApiInterface.php");
require("plugins/ext/mailing_modules/sendpulse/api/src/ApiClient.php");
require("plugins/ext/mailing_modules/sendpulse/api/src/Storage/TokenStorageInterface.php");
require("plugins/ext/mailing_modules/sendpulse/api/src/Storage/FileStorage.php");
require("plugins/ext/mailing_modules/sendpulse/api/src/Storage/SessionStorage.php");
require("plugins/ext/mailing_modules/sendpulse/api/src/Storage/MemcachedStorage.php");
require("plugins/ext/mailing_modules/sendpulse/api/src/Storage/MemcacheStorage.php");

use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;

class sendpulse
{
	public $title;
	
	public $site;
        public $api;
        public $version;
        public $country;
	
	function __construct()
	{
	  $this->title = 'SendPulse';
		$this->site = 'https://sendpulse.com';
		$this->api = 'https://sendpulse.com/integrations/api';
		$this->version = '1.0';
                $this->country = 'RU';
	}
	
	public function configuration()
	{
		$cfg = array();
							
		$cfg[] = array(
				'key'	=> 'user_id',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SENDPULSE_USER_ID,				
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'secret',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_SENDPULSE_SECRET,
				'params' =>array('class'=>'form-control input-large required'),
		);
									
				
		return $cfg;
	}
	
	function get_list_id_choices($module_id)
	{
		$cfg = modules::get_configuration($this->configuration(),$module_id);
		
		$choices = array();
		
		try 
		{
			$SPApiClient = new ApiClient($cfg['user_id'], $cfg['secret'], new FileStorage());
		
			$result = $SPApiClient->listAddressBooks();
							
			foreach($result as $obj)
			{
				$choices[$obj->id] = $obj->name;
			}
		}
		catch (Exception $e)
		{
			echo alert_error('<b>' . $this->title . ':</b> ' . $e->getMessage());
		}
		
		return $choices;
		
	}
		
	function subscribe($module_id, $contact_list_id, $contact_email, $contact_fields)
	{		
		global $alerts;
									
		$cfg = modules::get_configuration($this->configuration(),$module_id);
		
		try
		{					
			$SPApiClient = new ApiClient($cfg['user_id'], $cfg['secret'], new FileStorage());
			
			$emails = array(
					array(
							'email' => $contact_email,
							'variables' => $contact_fields
					)
			);
			
			$SPApiClient->addEmails($contact_list_id, $emails);
		}	
		catch (Exception $e)
		{
			$alerts->add('<b>' . $this->title . ':</b> ' .  $e->getMessage(),'error');
		}
		
	}	
	
	function update($module_id, $contact_list_id, $contact_email, $contact_fields,$prev_contact_email)
	{				
		$cfg = modules::get_configuration($this->configuration(),$module_id);
					
		//check if email updated
		if($contact_email==$prev_contact_email)
		{			
			
			//check if there are fields to update
			if(count($contact_fields))
			{	
				$this->subscribe($module_id, $contact_list_id, $contact_email, $contact_fields);
			}						
		}
		else
		{
			//delete previous email
			$this->delete($module_id, $contact_list_id, $prev_contact_email);
			
			//subscribe new account with new email
			$this->subscribe($module_id, $contact_list_id, $contact_email, $contact_fields);
		}	
									
	}
	
	function delete($module_id, $contact_list_id, $contact_email)
	{			
		$cfg = modules::get_configuration($this->configuration(),$module_id);
		
		try
		{					
			$SPApiClient = new ApiClient($cfg['user_id'], $cfg['secret'], new FileStorage());
									
			$result = $SPApiClient->removeEmails($contact_list_id, [$contact_email]);						
		}	
		catch (Exception $e)
		{
			
		}	
	}
	
}