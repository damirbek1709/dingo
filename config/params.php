<?php

return [
    'adminEmail' => 'send@dingo.kg',
    //'adminEmail' => 'mg@prosoft.kg',
    'senderEmail' => 'send@dingo.kg',
    'senderName' => 'Example.com mailer',
    'flashpay' => [
        // Sandbox configuration
        'merchant_id' => '147713',
        'api_secret' => '18d8c2073556c0457500bfe1e8de78a35d853e2fa4a10519f3993c84197e709e9b807b0291e7904b53762c5dd9492aa9330c4f6a72d84b9069250b529ecd28f2',
        'api_key' => 'YG92lqrqanpxgrKpdWP24O6hXo41Co',
        
        // API URLs
        'sandbox_url' => 'https://api.sandbox.flashpay.kg',
        'production_url' => 'https://api.flashpay.kg',
        
        // Environment (sandbox/production)
        'environment' => 'sandbox',
        
        // Default settings
        'default_currency' => 'KGS',
        'timeout' => 30,
        
        // Callback URLs
        'callback_url' => 'https://yoursite.com/payment/payout-callback',
        'return_url' => 'https://yoursite.com/payment/payout-success',
    ],

];
