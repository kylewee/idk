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

class link_for_calls
{

    public $title;
    public $site;
    public $api;
    public $version;

    function __construct()
    {
        $this->title = TEXT_MODULE_LINK_FOR_CALLS_TITLE;
        $this->site = '';
        $this->api = '';
        $this->version = '1.0';
    }

    public function configuration()
    {
        $cfg = array();


        $cfg[] = array(
            'key' => 'prefix',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_LINK_FOR_CALLS_LINK_PREFIX,
            'description' => TEXT_MODULE_LINK_FOR_CALLS_LINK_PREFIX_INFO,
            'params' => array('class' => 'form-control input-xlarge required'),
        );

        return $cfg;
    }

    function prepare_url($module_id, $phone_number, $options=[])
    {
        global $alerts, $app_user;

        $phone_number_val = preg_replace('/\D/', '', $phone_number);

        $params = '';

        $cfg = modules::get_configuration($this->configuration(), $module_id);

        if(preg_match_all('/\[(\w+)\]/', $cfg['prefix'], $matches))
        {
            $url = str_replace('[phone]', $phone_number_val, $cfg['prefix']);

            foreach($matches[1] as $matches_key => $matches_v)
            {
                if(isset($app_user['fields']['field_' . $matches_v]))
                {
                    $url = str_replace('[' . $matches_v . ']', $app_user['fields']['field_' . $matches_v], $url);
                }
            }

            $params = 'target="_new"';
        }
        else
        {
            $url = $cfg['prefix'] . $phone_number_val;
        }

        return '<a ' . $params . ' href="' . $url . '"><i class="fa fa-phone" aria-hidden="true"></i> ' . $phone_number . '</a>';
    }
    
    function call_history_url($module_id, $phone_number)
    {
        global $alerts, $app_user;
                     
        $cfg = modules::get_configuration($this->configuration(),$module_id);
                               
                              
        $url = parse_url($cfg['api_url']);

        $html = '
            <div class="btn-group">
                <a class="dropdown-toggle moizvonki-dropdown1" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
                '  . $phone_number . '</i>
                </a>
                <ul class="dropdown-menu" role="menu" >
                    <li>
                        ' . $this->prepare_url($module_id, $phone_number) . '
                    </li>

                    <li>
                        <a  href="javascript: copyTextToClipboard(\'' . $phone_number . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> ' . TEXT_COPY. '</a>
                    </li>
                    <li>
                        <a href="' . url_for('ext/call_history/view','search=' . preg_replace('/\D/', '', $phone_number)) . '"><i class="fa fa-history" aria-hidden="true"></i> ' . TEXT_EXT_ALL_CALLS_BY_NUMBER . '</a>
                    </li>


                </ul>
        </div>
         ';            

        return $html;
                
    }

}
