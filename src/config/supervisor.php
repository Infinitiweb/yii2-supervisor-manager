<?php

use infinitiweb\supervisorManager\components\supervisor\Connection;
use yii\filters\AccessControl;

return [
    'components' => [],
    'params' => [
        'supervisorConnection' => [
            'class' => Connection::class,
            'user' => 'user',
            'password' => '123',
            'url' => 'http://127.0.0.1:9001/RPC2'
        ],
        'supervisorConfiguration' => [
            'configDir' => \Yii::getAlias('@app/config/supervisor'),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ],
    ],
];
