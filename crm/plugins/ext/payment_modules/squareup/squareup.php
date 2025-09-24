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

/*
 * Docs
 * https://developer.squareup.com/docs/checkout-api/quick-pay-checkout#create-a-quick-pay-checkout-page
 * https://developer.squareup.com/docs/webhooks/step3validate#webhook-event-notification-validation---php
 */

require_once('plugins/ext/payment_modules/squareup/sdk/vendor/autoload.php');

use Square\SquareClientBuilder;
use Square\Authentication\BearerAuthCredentialsBuilder;
use Square\Environment;
use Square\Exceptions\ApiException;
use Square\Utils\WebhooksHelper;

class squareup
{

    public $title;
    public $site;
    public $api;
    public $version;
    public $js;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_SQUAREUP_TITLE;
        $this->site = 'https://squareup.com';
        $this->api = 'https://developer.squareup.com/docs/checkout-api/quick-pay-checkout';
        $this->version = '1.0';
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key' => 'access_token',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_SQUAREUP_ACCESS_TOKEN,
            'description' => TEXT_MODULE_SQUAREUP_ACCESS_TOKEN_INFO,
            'params' => array('class' => 'form-control input-large required'),
        );
        
        $cfg[] = array(
            'key' => 'location_id',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_SQUAREUP_LOCATION_ID,
            'description' => TEXT_MODULE_SQUAREUP_LOCATION_ID_INFO,
            'params' => array('class' => 'form-control input-large required'),
        );

        $cfg[] = array(
            'key' => 'currency',
            'type' => 'input',
            'default' => 'USD',
            'title' => TEXT_EXT_MODULE_TRANSACTION_CURRENCY,
            'description' => TEXT_EXT_MODULE_TRANSACTION_CURRENCY_INFO . '. <a href="https://developer.squareup.com/reference/square/enums/Currency" target="_blank">' . TEXT_MORE_INFO . '</a>.',
            'params' => array('class' => 'form-control input-small required'),
        );
        
        $webhook_url = url_for_file('api/ipn.php?module_id=' . (int) ($_GET['id']??0));
        $cfg[] = array(
            'key' => 'webhook_url',
            'type' => 'text',
            'default' => input_tag('webhook_url',url_for_file('api/ipn.php?module_id=' . (int) ($_GET['id']??0)),['class' => 'form-control select-all','readonly'=>'readonly']) . input_hidden_tag('cfg[webhook_url]', $webhook_url),
            'title' => TEXT_MODULE_SQUAREUP_WEBHOOK_URL,
            'description' => TEXT_MODULE_SQUAREUP_WEBHOOK_URL_INFO . '',            
        );
        
        $cfg[] = array(
            'key' => 'webhook_signature_key',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_SQUAREUP_WEBHOOK_SIGNATURE_KEY,
            'description' => TEXT_MODULE_SQUAREUP_WEBHOOK_SIGNATURE_KEY_INFO,
            'params' => array('class' => 'form-control input-large required'),
        );
        
        
        $cfg[] = array(
            'key' => 'lc',
            'type' => 'input',
            'default' => 'en_US',
            'title' => TEXT_LANGUAGE,
            'description' => '<a href="https://developer.squareup.com/docs/build-basics/general-considerations/language-preferences" target="_blank">Language Preferences for Applications</a>',
            'params' => array('class' => 'form-control input-small')
        );

        $cfg[] = array(
            'key' => 'environment',
            'type' => 'dorpdown',
            'choices' => array(
                'live' => TEXT_MODULE_GATEWAY_SERVER_LIVE,
                'sandbox' => TEXT_MODULE_GATEWAY_SERVER_SANDBOX,
            ),
            'default' => 'live',
            'title' => TEXT_MODULE_GATEWAY_SERVER,
            'info' => TEXT_MODULE_GATEWAY_SERVER_INFO,
            'params' => array('class' => 'form-control input-small')
        );

        $cfg[] = array(
            'key' => 'custom_title',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_CUSTOM_TITLE,
            'description' => TEXT_DEFAULT . ' "' . $this->title . '".',
            'params' => array('class' => 'form-control input-large')
        );

        $cfg[] = array(
            'key' => 'item_name',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_PURPOSE_OF_PAYMENT,
            'description' => TEXT_ENTER_TEXT_PATTERN_INFO,
            'params' => array('class' => 'form-control input-large required'),
        );
        
        $cfg[] = array(
            'key' => 'entity_id',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_ENTITY,
            'description' => TEXT_MODULE_SQUAREUP_ENTITY_INFO,
            'params' => array('class' => 'form-control input-small required','type'=>'number'),
        );

        $cfg[] = array(
            'key' => 'amount',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_PAYMENT_TOTAL,
            'description' => TEXT_MODULE_PAYMENT_TOTAL_INFO,
            'params' => array('class' => 'form-control input-small required','type'=>'number'),
        );
        
        $cfg[] = array(
            'key' => 'square_order_id',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_SQUAREUP_ORDER_ID,
            'description' => TEXT_MODULE_SQUAREUP_ORDER_ID_INFO,
            'params' => array('class' => 'form-control input-small required','type'=>'number'),
        );

        return $cfg;
    }

    function confirmation($module_id, $process_id)
    {
        global $app_path, $current_item_id, $current_entity_id, $app_redirect_to;

        $html = '';
                
        $cfg = modules::get_configuration($this->configuration(), $module_id);
        
        //print_rr($cfg);

        $item_info_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($current_entity_id, '') . " from app_entity_" . $current_entity_id . " e  where e.id='" . $current_item_id . "'");
        if($item_info = db_fetch_array($item_info_query))
        {
            $amount = $item_info['field_' . $cfg['amount']];

            $fieldtype_text_pattern = new fieldtype_text_pattern();

            $item_name = $fieldtype_text_pattern->output_singe_text($cfg['item_name'], $current_entity_id, $item_info);
            
            $form_action_url = '';
            
            //
            if($cfg['environment']=='sandbox')
            {
                $client = SquareClientBuilder::init()
                    ->bearerAuthCredentials(
                        BearerAuthCredentialsBuilder::init(
                            $cfg['access_token']
                          )
                    )
                    ->environment(Environment::SANDBOX)
                    ->build();
            }
            else
            {   
                $client = SquareClientBuilder::init()
                    ->bearerAuthCredentials(
                        BearerAuthCredentialsBuilder::init(
                            $cfg['access_token']
                          )
                    )                    
                    ->build();                
            }
            
            $price_money = new \Square\Models\Money();
            $price_money->setAmount($amount*100);
            $price_money->setCurrency($cfg['currency']);

            $quick_pay = new \Square\Models\QuickPay(
                $item_name,
                $price_money,
                $cfg['location_id']
            );
            
            $checkout_options = new \Square\Models\CheckoutOptions();
            $checkout_options->setRedirectUrl(url_for('items/info', 'path=' . $app_path));
            

            $body = new \Square\Models\CreatePaymentLinkRequest();
            //$body->setIdempotencyKey('{UNIQUE_KEY}');
            $body->setQuickPay($quick_pay);
            $body->setCheckoutOptions($checkout_options);
            
            $api_response = $client->getCheckoutApi()->createPaymentLink($body);

            if ($api_response->isSuccess()) {
                $result = $api_response->getResult();
                
                $form_action_url = $result->getPaymentLink()->getUrl();
                $square_order_id = $result->getPaymentLink()->getOrderId();
                
                //echo 'ok' . $result->payment_link->url;
                //var_dump($result);
                //print_rr($result);
            } else {
                $errors = $api_response->getErrors();
                
                //print_rr($errors);
                
                $html = '';
                foreach($errors as $error)
                {
                    $html .= alert_error('<b>' . $errors[0]->getCode() . ':</b> ' . $errors[0]->getDetail() . ' '. $errors[0]->getField());
                }
                
                echo $html;
                exit();                                
            }
            
            
            //echo  $form_action_url;
            
            if(strlen($form_action_url))
            {                            
                $html .= '<p class="to-pay">' . TEXT_EXT_TO_PAY . ': ' . $amount . ' ' . $cfg['currency'] . '</p>';
                $html .= '<a href="' . $form_action_url . '" class="btn btn-primary btn-pay">' . TEXT_EXT_BUTTON_PAY . '</a>' ;

                db_query("update app_entity_{$current_entity_id} set field_" . $cfg['square_order_id'] . "='{$square_order_id}' where id={$current_item_id}");
            }           
        }

        return $html;
    }

    function ipn($module_id)
    {
        $cfg = modules::get_configuration($this->configuration(), $module_id);

        // The URL where event notifications are sent.
        $notification_url = $cfg['webhook_url'];

        // The signature key defined for the subscription.
        $signature_key = $cfg['webhook_signature_key'];

        // Start a simple server for local testing.
        // Different frameworks may provide the raw request body in other ways.
        // INSTRUCTIONS
        // 1. Run the server:
        //    php -S localhost:8000 server.php
        // 2. Send the following request from a separate terminal:
        //    curl -vX POST localhost:8000 -d '{"hello":"world"}' -H "X-Square-HmacSha256-Signature: 2kRE5qRU2tR+tBGlDwMEw2avJ7QM4ikPYD/PJ3bd9Og="      

        if(!isset($_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE']))
        {
            http_response_code(403);
            exit();
        }

        $signature = $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'];

        $body = '';
        $handle = fopen('php://input', 'r');
        while(!feof($handle))
        {
            $body .= fread($handle, 1024);
        }

        //error_log("signature: $signature\n $signature_key\n $notification_url",3,'payment.log.txt'); 

        if(WebhooksHelper::isValidWebhookEventSignature($body, $signature, $signature_key, $notification_url))
        {
            // Signature is valid. Return 200 OK.
            http_response_code(200);

            $response = json_decode($body, true);

            //error_log(date('Y-m-d H:i:s') . ': ' . print_r($response, true) . "\n", 3, 'payment.log.txt');
            
            if(($response['type']??'')=='payment.updated' and $response['data']['object']['payment']['status']=='COMPLETED')
            {
                //error_log(date('Y-m-d H:i:s') . ': ' . "COMPLETED\n\n", 3, 'payment.log.txt');   
                
                $order_id = $response['data']['object']['payment']['order_id'];
                
                $current_entity_id = $cfg['entity_id'];
                                
                $item_info_query = db_query("select e.* from app_entity_{$current_entity_id} e  where e.field_"  . $cfg['square_order_id'] . "='" . $order_id . "'");
                if($item_info = db_fetch_array($item_info_query))
                {
                    $amount = $response['data']['object']['payment']['total_money']['amount'];
                    $currency = $response['data']['object']['payment']['total_money']['currency'];
                    
                    $comment = '<b>' . TEXT_EXT_PAYMENT_NOTIFICATION . '</b><br>' .
                            TEXT_EXT_PAYMENT_MODULE . ': ' . $this->title . '<br>' .
                            TEXT_MODULE_PAYMENT_TOTAL . ': ' . number_format($amount/100, 2, '.', '') . ' ' . strtoupper($currency) . '<br>' .
                            TEXT_MODULE_TRANSACTION_ID . ': ' . $order_id . '<br>' .
                            TEXT_MODULE_PAYMENT_STATUS . ': ' . modules::status_label('Completed', 'Completed');

                    $sql_data = array(
                        'description' => $comment,
                        'entities_id' => $current_entity_id,
                        'items_id' => $item_info['id'],
                        'date_added' => time(),
                        'created_by' => 0,
                    );

                    db_perform('app_comments', $sql_data);

                    //run process
                    $process_info_query = db_query("select * from app_ext_processes where entities_id={$current_entity_id} and find_in_set({$module_id},payment_modules)");
                    if($app_process_info = db_fetch_array($process_info_query))
                    {
                        $processes = new processes($current_entity_id);
                        $processes->items_id = $item_info['id'];
                        $processes->run($app_process_info, false, true);
                    }
                }
            }
        }
        else
        {
            // Signature is invalid. Return 403 Forbidden.
            http_response_code(403);
        }

        http_response_code();
    }
}
