<?php

namespace infinitiweb\supervisorManager\widgets\supervisor;

use yii\base\Widget;

/**
 * Class SupervisorManagerWidget
 *
 * @package infinitiweb\supervisorManager\widgets\supervisor
 */
class SupervisorManagerWidget extends Widget
{
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     */
    public function run()
    {
        return $this->render('default', [
            'supervisorHtml' => \Yii::$app->runAction('supervisor/default/index'),
        ]);
    }
}
