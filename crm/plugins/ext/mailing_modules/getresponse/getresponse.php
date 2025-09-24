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


class getresponse
{

    public $title;
    public $site;
    public $api;
    public $version;
    

    function __construct()
    {
        $this->title = 'GetResponse';
        $this->site = 'https://getresponse.com';
        $this->api = 'https://apidocs.getresponse.com/';
        $this->version = '1.0';
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
            'key' => 'api_url',
            'type' => 'input',
            'default' => 'https://api.getresponse.com/v3',
            'title' => 'API Endpoint',
            'description'=> '
                <b>Default</b>: https://api.getresponse.com/v3<br>
                <b>GetResponse MAX platform:</b><br>
                https://api3.getresponse360.pl/v3<br>
                https://api3.getresponse360.com/v3<br>
                ' . TEXT_READ_MORE . ': <a href="https://apidocs.getresponse.com/v3" target="_balnk">https://apidocs.getresponse.com/v3</a>',
            'params' => array('class' => 'form-control input-large required'),
        );


        return $cfg;
    }
    
    function request($url,$cfg, $postfields = [], $is_delete = false)
    {
        $body = false;
        
        $headers = [];
        $headers[] = "X-Auth-Token: api-key " . $cfg['api_key'];   
        $headers[] = 'Content-Type:application/json';                
        
        $ch = curl_init($cfg['api_url'] . '/' . $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                        
        curl_setopt($ch, CURLOPT_TIMEOUT, 13);	
        
        if($is_delete)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        
        if(count($postfields))
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields) );
        }
        
        $result = curl_exec($ch);                
        
        if($result)
        {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($result, 0, $header_size);
            $body = substr($result, $header_size);
        }
        
        $info = curl_getinfo($ch);
        
        curl_close($ch);
                                        
        if($body)
        {
            $body = json_decode($body,true);                                                
        }    
                
        return [
                'http_code'=>$info['http_code'],
                'body'=>$body
            ];
    }

    function get_list_id_choices($module_id)
    {
        $cfg = modules::get_configuration($this->configuration(), $module_id);
                
        $choices = [];                
        
        $result = $this->request('campaigns', $cfg);
                
        if($result['http_code']==200)
        {
            foreach($result['body'] as $campain)
            {
                $choices[$campain['campaignId']] = $campain['name'];
            }
        }
        else
        {
            //print_rr($result['body']);
            echo alert_error(TEXT_ERROR . ' ' . print_r($result['body'],true));
        }

        return $choices;
    }
    
    function prepare_contact_fields($cfg, $postfields, $contact_fields)
    {
        if(isset($contact_fields['name']))
        {
            $postfields['name'] = $contact_fields['name'];
            unset($contact_fields['name']);
        }
        
        if(count($contact_fields))
        {
            $result = $this->request('custom-fields', $cfg);
            
            //print_rr($result);
            
            $customFieldValues = [];
            
            foreach($contact_fields as $k=>$v)
            {
                if(!strlen($v))
                {
                    continue;
                }
                
                $customFieldId = false;
                foreach($result['body'] as $field)
                {
                    if($field['name']== strtolower($k))
                    {
                        $customFieldId = $field['customFieldId'];
                    }
                }
                
                if($customFieldId)
                {
                    $customFieldValues[] = [
                        'customFieldId' => $customFieldId,
                        'value'=> ["$v"]
                    ];
                }
            }
            
            $postfields['customFieldValues'] = $customFieldValues;
        }
        
        return $postfields;
    }

    function subscribe($module_id, $contact_list_id, $contact_email, $contact_fields)
    {
        global $alerts;

        $cfg = modules::get_configuration($this->configuration(), $module_id);
        
        $postfields = [
            'campaign'=>['campaignId'=>$contact_list_id],
            'email'=>$contact_email,
        ];
                
        $postfields = $this->prepare_contact_fields($cfg, $postfields, $contact_fields);
        
        
        //print_rr($postfields);        
        //exit();
                       
        $result = $this->request('contacts', $cfg,$postfields);
                            
        if($result['http_code']!=202 and $result['http_code']!=409 and isset($result['body']['message']))
        {
            $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result['body']['message'] . '<br>' . print_r($result, true), 'error');
        }
        
    }

    function update($module_id, $contact_list_id, $contact_email, $contact_fields, $prev_contact_email)
    {
        global $alerts;
        
        $cfg = modules::get_configuration($this->configuration(), $module_id);
        
        $postfields = [
            'campaign'=>['campaignId'=>$contact_list_id],
            'email'=>$contact_email,
        ];

        $postfields = $this->prepare_contact_fields($cfg, $postfields, $contact_fields);
        
        if($contact_id = $this->get_contact_id_by_email($cfg,$contact_email))
        {
            $result = $this->request('contacts/' . $contact_id, $cfg,$postfields);          
        }
        else
        {         
            $this->delete($module_id, $contact_list_id, $prev_contact_email);
            
            $result = $this->request('contacts', $cfg,$postfields);                             
        }
        
        if($result['http_code']!=202 and $result['http_code']!=409 and isset($result['body']['message']))
        {
            $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result['body']['message'] . '<br>' . print_r($result, true), 'error');
        }
                             
       
    }

    function delete($module_id, $contact_list_id, $contact_email)
    {
        
        $cfg = modules::get_configuration($this->configuration(), $module_id);

        if($contact_id = $this->get_contact_id_by_email($cfg,$contact_email))
        {
            $result = $this->request('contacts/' . $contact_id, $cfg,[],true);
           
            if($result['http_code']!=204 and $result['http_code']!=404 and isset($result['body']['message']))
            {
                $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result['body']['message'] . '<br>' . print_r($result, true), 'error');
            }
        }
    }
    
    function get_contact_id_by_email($cfg,$contact_email)
    {                
        $result = $this->request('contacts/?query[email]=' . $contact_email . '&additionalFlags=exactMatch', $cfg);                               
        
        if(isset($result['body'][0]['contactId']))
        {
           return $result['body'][0]['contactId'];
        }
        else
        {
            return false;
        }
    }

}
