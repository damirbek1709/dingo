<?php
$server = strtolower($_SERVER['HTTP_HOST'] ?? php_uname('n'));
$db_arr = [];
if ($server === 'partner.dingo.kg') {
    $db_arr = [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=dingo_db',
        'username' => 'dingo_user',
        'password' => '9a(Q}ZjjwfJ[Rx+k',
        'charset' => 'utf8mb4'
    ];
} elseif ($server === 'dev.dingo.kg') {
    $db_arr = [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=dingo_dev',
        'username' => 'dingo_dev',
        'password' => 'ctpwt4ZpXSsea[(k',
        'charset' => 'utf8mb4'
    ];
} else {
    $db_arr = [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=dingo_db',
        'username' => 'dingo_user',
        'password' => '9a(Q}ZjjwfJ[Rx+k',
        'charset' => 'utf8mb4'
    ];
}
return $db_arr;