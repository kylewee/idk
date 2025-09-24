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


class mango_office
{
    public $title;
    
    public $site;
    public $api;
    public $version;
    public $country;
    
    function __construct()
    {
        $this->title = TEXT_MODULE_MANGO_OFFICE_TITLE;
        $this->site = 'https://mango-office.ru';
        $this->api = 'https://www.mango-office.ru/products/integraciya/api/';
        $this->version = '1.0';
        $this->country = 'RU';
    }
    
    public function configuration()
    {
        global $app_users_cache;
        
        $cfg = array();
        
                        
        
        $cfg[] = array(
            'key'	=> 'api_url',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_MANGO_OFFICE_ADDRESS,            
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        
        $cfg[] = array(
            'key'	=> 'api_key',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_MANGO_OFFICE_API_KEY,
            'description'=>TEXT_MODULE_MANGO_OFFICE_API_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        
        $cfg[] = array(
            'key'	=> 'api_salt',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_MANGO_OFFICE_API_SALT,
            'description'=>TEXT_MODULE_MANGO_OFFICE_API_SALT_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'crm_url',
            'type' => 'text',            
            'default' => input_tag('crm_url',url_for_file('api/tel/mango_office.php'),['class'=>'form-control select-all','readonly'=>'readonly']),
            'title'	=> TEXT_MODULE_MANGO_OFFICE_CRM_ADDRESS,                        
        );
      
        $choices = array();
        $users_query = db_query("select u.*,a.name as group_name from app_entity_1 u left join app_access_groups a on a.id=u.field_6 order by u.field_8, u.field_7");
        while($users = db_fetch_array($users_query))
        {
            $group_name = ((isset($users['group_name']) and strlen($users['group_name'])>0) ? $users['group_name'] : TEXT_ADMINISTRATOR);
            $choices[$group_name][$users['id']] = $users['field_8'] . ' ' . $users['field_7'] . ' (' . $users['field_7'] . ')';
        }
        
        //print_rr($app_users_cache);
        
        $choices = array();
        foreach($app_users_cache as $id=>$user)
        {
            $choices[$user['group_name']][$id] = $user['name'];
        }
        
        $cfg[] = array(
            'key'	=> 'users',
            'type' => 'dorpdown',
            'choices' => $choices,
            'multiple' =>true,
            'default' => '',
            'title'	=> TEXT_USERS,
            'description' => TEXT_MODULE_MANGO_OFFICE_USER_INFO,
            'params' =>array('class'=>'form-control input-xlarge required chosen-select'),
        );
        
        $choices = [];
        
        $fields_query = fields::get_query(1," and f.type not in ('" . implode("','", array_merge(fields_types::get_reserved_data_types(), fields_types::get_users_types())) . "')");
        while($fields = db_fetch_array($fields_query))
        {
            $choices[$fields['id']] = $fields['name'];
        }
        
        $cfg[] = array(
            'key'	=> 'user_phone',
            'type' => 'dorpdown',
            'choices' => $choices,            
            'default' => '',
            'title'	=> TEXT_PHONE,
            'description' => TEXT_MODULE_MANGO_OFFICE_PHONE_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
                                            
        return $cfg;
    }
    
    static function get_crm_key()    {                     
        $module_query = db_query("select id from app_ext_modules where module='mango_office'");
        if($module = db_fetch_array($module_query))
        {
        
            $cfg_query = db_query("select * from app_ext_modules_cfg where modules_id='" . $module['id'] . "' and cfg_key='api_key'");
            if($cfg = db_fetch_array($cfg_query))
            {
                return $cfg['cfg_value'];
            }
        }
		
        return '';        
    }  

  
    function prepare_url($module_id, $phone_number, $options)
    {
        global $alerts, $app_user, $is_js_inserted;
                     
        $cfg = modules::get_configuration($this->configuration(),$module_id);
                               
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {                                               
            if(has_access_to_call_history())
            {
                $call_history_url = '<a href="' . url_for('ext/call_history/view','search=' . preg_replace('/\D/', '',$phone_number) ) . '" target="_new"><i class="fa fa-history" aria-hidden="true"></i> История</a>';
            }
            else
            {
                $call_history_url = '';
            }
            
            $html = '
                <div class="btn-group">
					<a class="dropdown-toggle mango-office-dropdown" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
					'  . $phone_number . '</i>
					</a>
					<ul class="dropdown-menu" role="menu" style="position: absolute;width: 175px;">
						<li>
                                                    ' . $call_history_url . '
						</li>
                                                <li>
							<a title="' . TEXT_EXT_SMS . '" href="javascript: open_dialog(\'' . url_for('items/phone_sms','path=' . $options['path'] . '&module_id=' . $module_id . '&field_id=' . $options['field']['id'] . '&item_id=' . $options['item']['id']) . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> Отправить СМС</a>
						</li>
						<li>
							<a href="javascript: open_dialog(\'' . url_for('items/phone_call','path=' . $options['path'] . '&module_id=' . $module_id . '&field_id=' . $options['field']['id'] . '&item_id=' . $options['item']['id'] ) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> Позвонить</a>
						</li>																				
					</ul>
				</div>
             ';
            
            if($is_js_inserted!=true)
            {
                $is_js_inserted = true;
                
                $html .= '
                    <script>
                        $(".mango-office-dropdown").click(function (e) { //Default mouse Position 
                            //alert(e.pageX + " , " + e.pageY);
                            console.log(e)
                            $(this).next().css("top",(e.pageY-$(window).scrollTop())+"px").css("left",e.pageX+"px")
                        });
                    </script>';
                
            }
            
            return $html;
        }
        else
        {
            return $phone_number;
        }
        
    }
    
    function call_history_url($module_id, $phone_number)
    {
        global $alerts, $app_user;
                     
        $cfg = modules::get_configuration($this->configuration(),$module_id);
                               
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {                        
            $url = parse_url($cfg['api_url']);
            
            $html = '
                <div class="btn-group">
                    <a class="dropdown-toggle mango-office-dropdown1" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
                    '  . $phone_number . '</i>
                    </a>
                    <ul class="dropdown-menu" role="menu" >
                        <li>
                            <a href="javascript: open_dialog(\'' . url_for('ext/call_history/phone_call','module_id=' . $module_id . '&phone=' . $phone_number) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> Позвонить ' . $phone_number . '</a>
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
        else
        {
            return $phone_number;
        }
        
    }
    
    protected function getSign($data, $cfg) {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return hash('sha256', $cfg['api_key'] . $data . $cfg['api_salt']);
    }
    
    function call_to_number($module_id, $phone_number)
    {
        global $app_user;
        
        $phone_number = preg_replace('/\D/', '', $phone_number);
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {
                                    
            $user = $app_user['fields']['field_' . $cfg['user_phone']]??'';
            
            if(strstr($user,'+'))
            {
                $user = preg_replace('/\D/', '', $user);
            }
            
            //print_rr($app_user);
            
            $data = [
                'command_id'=> 'call_' . $phone_number,
                'from'      => [
                    'extension' => $user,
                ],
                'to_number' => $phone_number
            ];     
            
            $post = [
                'vpbx_api_key' => $cfg['api_key'],
                'sign'         => $this->getSign($data,$cfg),
                'json'         => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ];
            
            $url = $cfg['api_url'] . 'commands/callback';
            
            //echo '<div class="ajax-modal-width-1100"></div>';
            //echo $url;
            //print_rr($post);
            //exit();               
            
            $ch = curl_init($url);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_POST, 1);            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );              
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);	
            $result = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($result,true);
            
            //print_rr($result);
            //exit();
            
            if(isset($result['result']) and $result['result']==1000)
            {
                echo '
                    <div class="alert alert-success">' . TEXT_EXT_CALL_SENT . '</div>

                    <script>
                            setTimeout(function(){
                                    $("#ajax-modal").modal("toggle");
                            }, 3000);
                    </script>';
            }
            if(isset($result['result']) and $result['result']!=1000)
            {
                echo '<div class="alert alert-danger">'  . $this->title . ' ' . TEXT_ERROR . ' ' .  $result['result'] .  ' ' . $this->result_code_description($result['result']). '</div>';
            }
            elseif(isset($result['message']))
            {
                echo '<div class="alert alert-danger">'  . $this->title . ' ' . TEXT_ERROR . ' ' .  $result['name'] . ' (' . $result['message'] . ')' . '</div>';
            }
        }
    }
    
    function sms_to_number($module_id, $phone_number, $message_text)
    {
        global $app_user, $alerts;
                
        
        $phone_number = preg_replace('/\D/', '', $phone_number);
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {
                                    
            $user = $app_user['fields']['field_' . $cfg['user_phone']]??'';
            
            if(strstr($user,'+'))
            {
                $user = preg_replace('/\D/', '', $user);
            }
            
            //print_rr($app_user);
            
            $data = [
                'command_id'=> 'sms_' . $phone_number,
                'from_extension' => $user,
                'to_number' => $phone_number,
                'text'=>$message_text,                
            ];     
            
            $post = [
                'vpbx_api_key' => $cfg['api_key'],
                'sign'         => $this->getSign($data,$cfg),
                'json'         => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ];
            
            $url = $cfg['api_url'] . 'commands/sms';
            
            //echo $url;
            //print_rr($post);
            //exit();               
            
            $ch = curl_init($url);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_POST, 1);            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );              
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);	
            $result = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($result,true);
            
            //print_rr($result);
            //exit();
            
            if(isset($result['result']) and $result['result']==1000)
            {
                return true;
            }
            if(isset($result['result']) and $result['result']!=1000)
            {
                $alerts->add( $this->title . ' ' . TEXT_ERROR . ' ' .  $result['result'] .  ' ' . $this->result_code_description($result['result']),'error');
                return false;
            }
            elseif(isset($result['message']))
            {
                $alerts->add( $this->title . ' ' . TEXT_ERROR . ' ' .  $result['name'] . ' (' . $result['message'] . ')' ,'error');
                return false;
            }
        }
        
        return false;
    }
    
    function result_code_description($v)
    {
        $code = [];
        $code[1000] = 'Действие успешно выполнено';
        $code[1100] = ' Вызов завершен в нормальном режиме';
        $code[1110] = ' Вызов завершен вызывающим абонентом';
        $code[1111] = ' Вызов не получил ответа в течение времени ожидания ';
        $code[1120] = ' Вызов завершен вызываемым абонентом ';
        $code[1121] = ' Получен ответ "занято" от удаленной стороны ';
        $code[1122] = ' Вызов отклонен вызываемым абонентом';
        $code[1123] = ' Получен сигнал "не беспокоить"';
        $code[1124] = ' Вызов завершен по причине недоступности сотрудника';
        $code[1130] = ' Ограничения для вызываемого номера';
        $code[1131] = ' Вызываемый номер недоступен ';
        $code[1132] = ' Вызываемый номер не обслуживается';
        $code[1133] = ' Вызываемый номер не существует';
        $code[1134] = ' Превышено максимальное число переадресаций';
        $code[1140] = ' Вызовы на регион запрещены настройками ВАТС';
        $code[1150] = ' Ограничения для вызывающего номера';
        $code[1151] = ' Вызывающий номер в «черном» списке ';
        $code[1152] = ' Вызывающий номер не найден в «белом» списке';
        $code[1160] = ' Вызов на группу не удался';
        $code[1161] = ' Удержание запрещено настройками ВАТС ';
        $code[1162] = ' Очередь удержания заполнена';
        $code[1163] = ' Превышено время ожидания в очереди удержания';
        $code[1164] = ' Все операторы в данный момент недоступны';
        $code[1170] = ' Вызов завершен согласно схеме переадресации';
        $code[1171] = ' Неверно настроена схема переадресации ';
        $code[1180] = ' Вызов завершен командой пользователя';
        $code[1181] = ' Вызов завершен по команде из внешней системы';
        $code[1182] = ' Вызов завершен перехватом на другого оператора (только для исходящих плеч) ';
        $code[1183] = ' Назначен новый оператор (при команде ApiConnect, обычно при переводах)';
        $code[1190] = ' Вызываемый номер неактивен либо нерабочее расписание';
        $code[1191] = ' Вызываемый номер неактивен (снят флажок активности ЛК)';
        $code[1192] = ' Вызываемый номер неактивен по расписанию';
        $code[1200] = ' Ошибка сессий КЦ';
        $code[1201] = ' Достигнут лимит подключений';
        $code[1202] = ' Данные сессии не найдены';
        $code[1210] = ' Сервер КЦ не может принять подключение';
        $code[1211] = ' Режим обслуживания';
        $code[1212] = ' Сервер отключен от БД (БРТ)';
        $code[1230] = ' Звершение сессии КЦ по независимым от пользователя причинам';
        $code[1231] = ' Перезагрузка сервера КЦ';
        $code[1232] = ' Сессия завершена администратором';
        $code[1233] = ' Сессия завершена администратором, рекомендовано восстановление';
        $code[1234] = ' Сессия завершена администратором, рекомендовано оставаться в оффлайн';
        $code[1235] = ' Сервер отключился от БД (переход в БРТ)';
        $code[1236] = ' Изменены критичные данные сессии (логин, пароль, номер телефона, и т.д.)';
        $code[2000] = ' Ограничение биллинговой системы';
        $code[2100] = ' Доступ к счету невозможен';
        $code[2110] = ' Счет заблокирован';
        $code[2120] = ' Счет закрыт';
        $code[2130] = ' Счет не обслуживается (frozen)';
        $code[2140] = ' Счет недействителен';
        $code[2200] = ' Доступ к счету ограничен';
        $code[2210] = ' Доступ ограничен периодом использования';
        $code[2211] = ' Достигнут дневной лимит использования услуги';
        $code[2212] = ' Достигнут месячный лимит использования услуги';
        $code[2220] = ' Количество одновременных вызовов/действий ограничено';
        $code[2230] = ' Услуга недоступна';
        $code[2240] = ' Недостаточно средств на счете';
        $code[2250] = ' Ограничение на количество использований услуги в биллинге';
        $code[2300] = ' Направление заблокировано';
        $code[2400] = ' Ошибка биллинга';
        $code[3000] = ' Неверный запрос';
        $code[3100] = ' Переданы неверные параметры команды';
        $code[3101] = ' Запрос выполнен по методу, отличному от POST';
        $code[3102] = ' Значение ключа не соответствуют рассчитанному';
        $code[3103] = ' В запросе отсутствует обязательный параметр';
        $code[3104] = ' Параметр передан в неправильном формате';
        $code[3105] = ' Неверный ключ доступа';
        $code[3200] = ' Неверно указан номер абонента';
        $code[3300] = ' Объект не существует';
        $code[3310] = ' Вызов не найден';
        $code[3320] = ' Запись разговора не найдена ';
        $code[3330] = ' Номер не найден у ВАТС или сотрудника';
        $code[3340] = ' Файл не найден';
        $code[4000] = ' Действие не может быть выполнено ';
        $code[4001] = ' Команда не поддерживается';
        $code[4002] = ' Продолжительность записи меньше минимально возможной в ВАТС, запись не будет сохранена';
        $code[4100] = ' Выполнить команду по логике работы ВАТС невозможно';
        $code[4101] = ' Вызов завершен либо не существует';
        $code[4102] = ' Запись разговора уже осуществляется';
        $code[4200] = ' Связаться с абонентом в данный момент невозможно';
        $code[4300] = ' SMS сообщение отправить не удалось ';
        $code[4301] = ' SMS сообщение устарело';
        $code[4391] = ' SMS сообщение утеряно (статус возвращает внешний оператор)';
        $code[4392] = ' SMS сообщение отклонено (статус возвращает внешний оператор)';
        $code[4393] = ' SMS сообщение отменено (статус возвращает внешний оператор)';
        $code[4400] = ' Невозможно добавить участника в конференцию';
        $code[4401] = ' Аппаратная ошибка';
        $code[4402] = ' Сервис не доступен';
        $code[4403] = ' Недостаточно ресурсов';
        $code[4404] = ' Превышено ограничение на количество участников конференции';
        $code[4405] = ' Подключение запрещено настройками комнаты конференций';
        $code[4500] = ' Ограничения системы безопасности';
        $code[4501] = ' Установлено ограничение частоты звонков';
        $code[4502] = ' Вызывающий номер в черном списке входящих номеров';
        $code[4503] = ' Превышен максимальный размер файла';
        $code[4504] = ' Не удалось определить размер файла';
        $code[4505] = ' Формат файла не соответствует разрешенному';
        $code[5000] = ' Ошибка сервера';
        $code[5001] = ' Перезапуск коммутатора, выполняется при срабатывании какого-либо ограничения канала или уровня на коммутаторе.';
        $code[5002] = ' Перезапуск коммутатора по команде администратора / разработчика';
        $code[5003] = ' Технические проблемы, внутренняя ошибка на коммутаторе';
        $code[5004] = 'Проблемы доступа к базе данных коммутатора: не удалось подключиться к базе данных или в результате обработки запроса на манипуляцию с даными (чтение/вставка/удаление/ и так далее) в базе данных выдано сообщение об ошибке. ';
        $code[5007] = ' Во сторонней системе, связанной с коммутатором, выдано сообщение о внутренней ошибке. Сторонняя система не доступна коммутатору. ';
        $code[5101] = ' Нет продукта ЦОВ/ Контакт центр';
        $code[5102] = ' Превышен лимит активных кампаний';
        $code[5103] = ' Превышен лимит кампаний';
        $code[5105] = ' Указанный при создании кампании "abonent_id" в поле "created by" не существует';
        $code[5106] = ' Не удалось вставить задания из-за неподходящего статуса кампании';
        $code[5107] = ' Количество заданий для кампании превышает допустимое значение (10 000)';
        $code[5212] = ' Нет активных номеров. Укажите хотя бы один номер';
        $code[6000] = ' Доставка факса не выполнялась';
        $code[6010] = ' Технические проблемы сервиса факс-рассылок';
        $code[6011] = ' Указанный в задании на рассылку номер недоступен в течение часа';
        $code[6012] = ' Указанный в задании на рассылку номер не существует';
        $code[6013] = ' На указанном номере не установлен факс-аппарат';
        $code[6014] = ' Адресат отказался принимать факс';
        $code[6100] = ' Ошибка при преобразовании факса';
        $code[6101] = ' Превышен допустимый размер исходного файла (10 мегабайт)';
        $code[6102] = ' Превышено допустимое число страниц (30)';
        
        return $code[$v]??'';
    }

    
}