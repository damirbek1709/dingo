<?php

return [
    'adminEmail' => 'send@dingo.kg',
    //'adminEmail' => 'mg@prosoft.kg',
    'senderEmail' => 'send@dingo.kg',
    'senderName' => 'Example.com mailer',
    'flashpay' => [
        'project_id' => 147713, // Your FlashPay project ID
        'secret_key' => '18d8c2073556c0457500bfe1e8de78a35d853e2fa4a10519f3993c84197e709e9b807b0291e7904b53762c5dd9492aa9330c4f6a72d84b9069250b529ecd28f2', // Your FlashPay secret key
        'api_url' => 'https://gateway.flashpay.kg/v2/payment/individual/payout',
        'test_mode' => true, // Set to false for production
    ],

];
