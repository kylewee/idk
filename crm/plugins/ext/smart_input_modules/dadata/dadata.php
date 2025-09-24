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


class dadata
{

    public $title;
    public $site;
    public $types_choices;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_DADATA_TITLE;
        $this->site = 'https://dadata.ru';
        $this->api = 'https://dadata.ru/suggestions/usage/';
        $this->version = '4.0';
        $this->country = 'RU';

        $this->types_choices = array();
        $this->types_choices['ADDRESS'] = TEXT_MODULE_DADATA_TYPE_ADDRESS;        
        $this->types_choices['PARTY'] = TEXT_MODULE_DADATA_TYPE_PARTY;
        $this->types_choices['PARTY_BY'] = TEXT_MODULE_DADATA_TYPE_PARTY_BY;
        $this->types_choices['PARTY_KZ'] = TEXT_MODULE_DADATA_TYPE_PARTY_KZ;
        $this->types_choices['BANK'] = TEXT_MODULE_DADATA_TYPE_BANK;
        $this->types_choices['NAME'] = TEXT_MODULE_DADATA_TYPE_NAME;
        $this->types_choices['EMAIL'] = TEXT_MODULE_DADATA_TYPE_EMAIL;

        $this->types_choices['country'] = TEXT_MODULE_DADATA_TYPE_COUNTRY;
        $this->types_choices['currency'] = TEXT_MODULE_DADATA_TYPE_CURRENCY;
        $this->types_choices['postal_office'] = TEXT_MODULE_DADATA_TYPE_POSTAL_OFFICE;
        $this->types_choices['fns_unit'] = TEXT_MODULE_DADATA_TYPE_FNS_UNIT;
        $this->types_choices['okved2'] = TEXT_MODULE_DADATA_TYPE_OKVED2;
        $this->types_choices['okpd2'] = TEXT_MODULE_DADATA_TYPE_OKPD2;
        $this->types_choices['fms_unit'] = TEXT_MODULE_DADATA_TYPE_FMS_UNIT;
        $this->types_choices['car_brand'] = TEXT_MODULE_DADATA_TYPE_CAR_BRAND;
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key' => 'api_key',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_EXT_API_KEY,
            'params' => array('class' => 'form-control input-large required'),
        );

        $cfg[] = array(
            'key' => 'count',
            'type' => 'input',
            'default' => '5',
            'title' => TEXT_MODULE_DADATA_MAX_COUNT,
            'description' => TEXT_MODULE_DADATA_MAX_COUNT_INFO,
            'params' => array('class' => 'form-control input-small'),
        );

        $cfg[] = array(
            'key' => 'minChars',
            'type' => 'input',
            'default' => '1',
            'title' => TEXT_MODULE_DADATA_MIN_CHARS,
            'description' => TEXT_MODULE_DADATA_MIN_CHARS_INFO,
            'params' => array('class' => 'form-control input-small'),
        );

        return $cfg;
    }

    public function render_itnegration_type_name($type)
    {
        return (isset($this->types_choices[$type]) ? $this->types_choices[$type] : $type);
    }

    public function render_itnegration_types($type,$settings = '')
    {
        $cfg = new settings($settings);
        
        $html = '
            <div class="form-group">
                <label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . TEXT_TYPE . '</label>
                <div class="col-md-9">
                    ' . select_tag('type', $this->types_choices, $type, array('class' => 'form-control input-large required')) . '
                </div>
            </div>
            
            <div form_display_rules="type:ADDRESS">  
                <div class="form-group">
                    <label class="col-md-3 control-label">' . TEXT_MODULE_DADATA_TYPE_ADDRESS . '</label>
                    <div class="col-md-9">
                        ' . select_tag('settings[division]', ['ADMINISTRATIVE' => TEXT_MODULE_DADATA_ADMINISTRATIVE, 'MUNICIPAL' => TEXT_MODULE_DADATA_MUNICIPAL], $cfg->get('division'), ['class'=>'form-control input-medium']) . '                       
                    </div>
                </div> 
                <div class="form-group">
                    <label class="col-md-3 control-label">' . TEXT_COUNTRY . '</label>
                    <div class="col-md-9">
                        ' . input_tag('settings[address_counties]', $cfg->get('address_counties'), array('class' => 'form-control input-large')) . '
                        ' . tooltip_text(TEXT_DEFAULT . ': RU <br>' . TEXT_EXAMPLE . ' 1: BY,KZ,RU<br>'  . TEXT_EXAMPLE . ' 2: *'). '    
                    </div>
                </div>   
                <div class="form-group">
                    <label class="col-md-3 control-label">' . TEXT_MODULE_DADATA_ADD_INDEX . '</label>
                    <div class="col-md-9">
                        ' . select_tag_boolean('settings[address_add_index]', $cfg->get('address_add_index')) . '                       
                    </div>
                </div>  
                <div class="form-group">
                    <label class="col-md-3 control-label">' . TEXT_MODULE_DADATA_ADD_COUNTRY . '</label>
                    <div class="col-md-9">
                        ' . select_tag_boolean('settings[address_add_country]', $cfg->get('address_add_country')) . '                       
                    </div>
                </div>  
            </div>
            ';

        return $html;
    }

    public function render_itnegration_rules($rules, $entity_field_html = '')
    {
        $html = $entity_field_html . '
        			<div class="form-group">
						  	<label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . TEXT_RULE_FOR_FIELD . '</label>
						    <div class="col-md-9">
						  	  ' . textarea_tag('rules', $rules, array('class' => 'form-control input-xlarge')) . '
						  	  ' . tooltip_text(TEXT_MODULE_DADATA_RULES_INFO) . '
						    </div>
						  </div>
        			';

        return $html;
    }

    public function render_js_includes($module_id)
    {
       
        $html = '
			<link href="js/jquery.suggestions/22.6.0/suggestions.min.css" type="text/css" rel="stylesheet" />					
			<script type="text/javascript" src="js/jquery.suggestions/22.6.0/jquery.suggestions.min.js"></script>		
		';  

        return $html;
    }

    public function render($module_id, $rules)
    {
        $html = '';

        $cfg = modules::get_configuration($this->configuration(), $module_id);
        
        $settings = new settings($rules['settings']);
                
        $params = [];
        
        if($rules['type']=='ADDRESS')
        {
            $params[] = 'division: "' . ($settings->get('division', 'ADMINISTRATIVE') ). '"';
        }

        $html .= '
            <script type="text/javascript">
                    $(function(){
                            $("#fields_' . $rules['fields_id'] . '").suggestions({
                            token: "' . $cfg['api_key'] . '",
                            type: "' . $rules['type'] . '",
                            count: ' . (($cfg['count'] > 0 and $cfg['count'] < 20) ? $cfg['count'] : 5) . ',
                            minChars: ' . ($cfg['minChars'] > 0 ? $cfg['minChars'] : 1) . ',
                            formatSelected: formatSelected' . $rules['fields_id'] . ', 
                            params: {
                                ' . implode(',', $params) . '
                            },    

                            /* Вызывается, когда пользователь выбирает одну из подсказок */
                            onSelect: function(suggestion) {
                                //console.log(suggestion.data);
                                ' . $this->render_on_select(trim($rules['rules'])) . '					        		
                            }
                            ' . $this->render_address_counties($rules) . '
                        });
                    })	

                    ' . $this->render_formatSelected($rules) . '    
            </script>
				';

        return $html;
    }
    
    private function render_formatSelected($rules)
    {
        $cfg = new settings($rules['settings']);
        
        $html_index = '';
        if($cfg->get('address_add_index')==1)
        {
            $html_index = '
                if (suggestion.data.postal_code) {
                    value =  suggestion.data.postal_code + ", " + value;
                  } 
                ';
        }
        
        $html_country = '';
        if($cfg->get('address_add_country')==1)
        {
            $html_country = '
                if (suggestion.data.country) {
                    value =  suggestion.data.country + ", " + value;
                  } 
                ';
        }
        
        $html = '
            function formatSelected' . $rules['fields_id'] . '(suggestion) {
                let value = suggestion.value
                ' . $html_index . '
                ' . $html_country . '    
                return value;
              }
            ';   
        
        return $html;
    }
    
    private function render_address_counties($rules)
    {        
        $cfg = new settings($rules['settings']);
        
        $address_counties = trim($cfg->get('address_counties'));
        
        if($rules['type']!='ADDRESS' or !strlen($address_counties)) return '';
                        
        $html = '';
        
        if($address_counties=='*')
        {
            $html = '
                ,geoLocation: false,
                enrichmentEnabled: false,
                constraints: {
                    locations: { country: "*" }
                  }
                ';
        }
        else
        {
            $locations = [];
            foreach(explode(',',$address_counties) as $v)
            {
                $locations[] = '{ country_iso_code: "' . trim($v). '" }';
            }
            
            $html = '
                ,geoLocation: false,
                constraints: {
                locations: [
                  ' . implode(',', $locations). '
                ]
              }
                ';
            
        }
        
        return $html;
    }

    public function render_on_select($rules)
    {
        $html = '';

        if (strlen($rules))
        {
            foreach (preg_split('/\r\n|\r|\n/', $rules) as $value)
            {
                $value_array = explode('=', $value);
                $field_id = trim(str_replace(array('[', ']'), '', $value_array[0]));
                $value = trim($value_array[1]);

                if (strstr($value, '_date') or strstr($value, 'valid_from') or strstr($value, 'valid_to'))
                {
                    $html .= '
                            try{

                                    var value = "";

                                    if(suggestion.' . $value . ')
                                    {
                                            var date = new Date(suggestion.' . $value . ');
                                            month = date.getMonth()+1
                                            value	= date.getFullYear()+"-"+(month<9 ? "0"+month:month)+"-"+(date.getDate()<9 ? "0"+date.getDate():date.getDate());							
                                    }

                                    $("#fields_' . $field_id . '").val(value);
                            }			
                            catch (err)
                            {
                                    console.error(err)
                            }									
                            ' . "\n";
                }
                else
                {
                    $html .= '
                            try{
                                    $("#fields_' . $field_id . '").val(suggestion.' . $value . ').trigger("focusout");
                            }			
                            catch (err)
                            {
                                    console.error(err)
                            }			
                            ';
                }
            }
        }

        return $html;
    }

}
