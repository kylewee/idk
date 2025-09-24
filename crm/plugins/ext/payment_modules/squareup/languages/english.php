<?php

define('TEXT_MODULE_SQUAREUP_TITLE','Square');
define('TEXT_MODULE_SQUAREUP_ACCESS_TOKEN','Access token');
define('TEXT_MODULE_SQUAREUP_ACCESS_TOKEN_INFO','<a href="https://developer.squareup.com/console/en/apps" target="_blank">https://developer.squareup.com/console/en/apps</a>');
define('TEXT_MODULE_SQUAREUP_LOCATION_ID','Location ID');
define('TEXT_MODULE_SQUAREUP_LOCATION_ID_INFO','Locations represent sources of orders and fulfillments for businesses (such as brick and mortar stores, online marketplaces, warehouses, or anywhere a seller does business). <a href="https://developer.squareup.com/docs/locations-api" target="_blank">Learn more</a>.');
define('TEXT_MODULE_SQUAREUP_WEBHOOK_URL','Webhook URL');
define('TEXT_MODULE_SQUAREUP_WEBHOOK_URL_INFO','<ol>
    <li>Login to your Square Developer Dashboard.</li>
    <li>Choose an application.</li>
    <li>Go to Webhooks > Subscriptions from the left navigation menu.</li>
    <li>Click on the “Add Subscription” button under the Webhooks Subscriptions section.</li>
    <li>Put Webhook url in URL field</li>
    <li>Choose Events <code>payment.updated</code></li>
    </ol>');
define('TEXT_MODULE_SQUAREUP_WEBHOOK_SIGNATURE_KEY','Webhook Signature key');
define('TEXT_MODULE_SQUAREUP_WEBHOOK_SIGNATURE_KEY_INFO','The signature key defined for the subscription.');
define('TEXT_MODULE_SQUAREUP_ENTITY_INFO', 'Enter Entity ID where module will be using.');
define('TEXT_MODULE_SQUAREUP_ORDER_ID','Square Order ID');
define('TEXT_MODULE_SQUAREUP_ORDER_ID_INFO','Enter Field ID where order ID will be stored. This is internal input field. Can be hidden for users.');