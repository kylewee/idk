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


class smartystreets
{
	public $title;

	public $site;
	
	public $types_choices;
        
        public $api;
        public $version;
    

	function __construct()
	{
		$this->title = TEXT_MODULE_SMARTYSTREETS_TITLE;
		$this->site = 'https://smartystreets.com';
		$this->api = 'https://smartystreets.com/docs/plugins/website';
		$this->version = '1.0';
		
		$this->types_choices = array();
		$this->types_choices['SINGLE_ADDRESS_US'] = TEXT_MODULE_SMARTYSTREETS_TYPE_SINGLE_ADDRESS_US;
	}

	public function configuration()
	{
		$cfg = array();

		$cfg[] = array(
				'key'	=> 'api_key',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_EXT_API_KEY,
				'params' =>array('class'=>'form-control input-large required'),
		);
		
		
		$cfg[] = array(
				'key'	=> 'debug',
				'type' => 'dorpdown',
				'default' => 'false',
				'choices' =>array(
						'true' => TEXT_YES,
						'false' => TEXT_NO,												
				),
				'title'	=> TEXT_EXT_DEBUG_MODE,
				'params' =>array('class'=>'form-control input-small'),
		);
		
		$cfg[] = array(
				'key'	=> 'autoVerify',
				'type' => 'dorpdown',
				'default' => 'false',
				'choices' =>array(
						'true' => TEXT_YES,
						'false' => TEXT_NO,						
				),
				'title'	=> TEXT_MODULE_SMARTYSTREETS_AUTOVERIFY,
				'description'	=> TEXT_MODULE_SMARTYSTREETS_AUTOVERIFY_INFO,
				'params' =>array('class'=>'form-control input-small'),
		);
				

		return $cfg;
	}
	
	public function render_itnegration_type_name($type)
	{
		return (isset($this->types_choices[$type]) ? $this->types_choices[$type] : $type);
	}

	public function render_itnegration_types($type)
	{				
		$html = '
        			<div class="form-group">
						  	<label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' .  TEXT_TYPE . '</label>
						    <div class="col-md-9">
						  	  ' . select_tag('type',$this->types_choices, $type,array('class'=>'form-control input-large required')) . '
						    </div>
						  </div>
        			';

		return $html;
	}

	public function render_itnegration_rules($rules,$entity_field_html = '')
	{
		$html = '
        			<div class="form-group">
						  	<label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' .  TEXT_RULE_FOR_FIELD . '</label>
						    <div class="col-md-9">
						  	  ' . textarea_tag('rules', $rules,array('class'=>'form-control input-xlarge')) . '
						  	  ' . tooltip_text(TEXT_MODULE_SMARTYSTREETS_RULES_INFO). '
						    </div>
						  </div>
        			';

		return $html;
	}

	public function render_js_includes($module_id)
	{
		$html = '
			<script src="//d79i1fxsrar4t.cloudfront.net/jquery.liveaddress/5.1/jquery.liveaddress.min.js"></script>
		';

		return $html;
	}

	public function render($module_id,$rules)
	{
		$html = '';

		$cfg = modules::get_configuration($this->configuration(),$module_id);

		$html .= '
				<script type="text/javascript">
					$(function(){
				
						try
						{	
						  smartystreets = jQuery.LiveAddress({
						    key: "' . $cfg['api_key'] . '",
						    waitForStreet: true,
						    debug: ' . $cfg['debug'] . ',
						    autoVerify: ' . $cfg['autoVerify'] . ',								    		
						    target: "US", 
						    submitSelector: "[type=submit]",		
						    addresses: [{    						      						      	
						    		' . $this->render_rules(trim($rules['rules'])). '
						    	}
						    ]
						  });
						}
						catch (err)
						{
							alert(err)
						}
						    		
					})
				</script>
				';

		return $html;
	}

	public function render_rules($rules)
	{
		$html = '';

		if(strlen($rules))
		{
			foreach(preg_split('/\r\n|\r|\n/', $rules) as $value)
			{
				$value_array = explode(':',$value);
				$key = trim($value_array[0]);
				$field_id = trim(str_replace(array('[',']'),'',$value_array[1]));
				
				$html .= $key . ': "#fields_' . $field_id . '",' . "\n";
			}
				
		}

		return $html;
	}
		
}