# Supervisor manager

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```$xslt
php composer.phar require --prefer-dist infinitiweb/yii2-supervisor-manager "^1.1"
```

or add

```$xslt
"infinitiweb/yii2-supervisor-manager": "^1.1"
```

to the require section of your `composer.json` file.

Usage
-----

Add module of extension in app config:
```$xslt
...
    'modules' => [
    ...
        'supervisorManager' => [
            'class' => 'infinitiweb\supervisorManager\Module',
            'authData' => [
                'user' => 'supervisor_user',
                'password' => 'supervisor_pass',
                'url' => 'http://127.0.0.1:9001/RPC2',
            ],
            'supervisorConfiguration' => [
                'configDir' => \Yii::getAlias('@common/config/supervisor'),
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
    ...
    ],
...
```

Simply use it in your code by:

```$xslt
<?php

use infinitiweb\supervisorManager\widgets\supervisor\SupervisorManagerWidget;

echo SupervisorManagerWidget::widget();

?>
```
