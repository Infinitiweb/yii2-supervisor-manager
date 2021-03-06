<?php

namespace infinitiweb\supervisorManager;

use infinitiweb\supervisorManager\components\supervisor\ConnectionInterface;
use infinitiweb\supervisorManager\components\supervisor\Supervisor;
use yii\base\Event;
use Zend\XmlRpc\Client;

/**
 * Class Module
 *
 * @property array supervisorConnection
 *
 * @package infinitiweb\modules\yii2\supervisorManager
 */
class Module extends \yii\base\Module
{
    /** @var array Supervisor client authenticate data. */
    public $authData = [];
    /** @var array Supervisor configuration data */
    public $configData = [];
    /** @var string */
    public $controllerNamespace = 'infinitiweb\supervisorManager\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initExtension();
    }

    /**
     * @return void
     */
    public function initExtension(): void
    {
        Event::on(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED, function () {
            exec('supervisorctl update', $output, $status);
        });

        \Yii::configure($this, require(__DIR__ . '/config/supervisor.php'));
        \Yii::$app->params['supervisorConfiguration'] = array_merge($this->params['supervisorConfiguration'], $this->configData);

        $this->params['supervisorConnection'] = array_merge($this->params['supervisorConnection'], $this->authData);

        $this->registerIoC();
    }

    /**
     * @return void
     */
    protected function registerIoC(): void
    {
        \Yii::$container->set(Client::class, function () {
            return new Client($this->params['supervisorConnection']['url']);
        });

        \Yii::$container->set(ConnectionInterface::class, $this->params['supervisorConnection']);
    }
}
