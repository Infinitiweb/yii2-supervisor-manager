<?php

namespace infinitiweb\supervisorManager\widgets\supervisorManager;

use infinitiweb\supervisorManager\components\supervisor\control\MainProcess;
use infinitiweb\supervisorManager\components\supervisor\exceptions\ConnectionException;
use infinitiweb\supervisorManager\models\SupervisorGroupForm;
use infinitiweb\supervisorManager\Module;
use yii\base\Widget;
use yii\data\ArrayDataProvider;

/**
 * Class SupervisorManagerWidget
 *
 * @package infinitiweb\supervisorManager\widgets\supervisorManager
 */
class SupervisorManagerWidget extends Widget
{
    /** @var string */
    private const VIEWS_DIR = '@infinitiweb/supervisorManager/views/common';

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        \Yii::$app->getModule('supervisor')->init();

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function run()
    {
        try {
            $supervisor = $this->getSupervisorMainProcess();
        } catch (ConnectionException $error) {
            return $this->renderErrorHandle($error);
        }

        $groups = $supervisor->getAllProcessesByGroup();

        foreach ($groups as $groupName => &$group) {
            $group['group'] = $groupName;
            $group['processList'] = new ArrayDataProvider([
                'allModels' => $group['processList'],
                'pagination' => [
                    'pageSize' => 5,
                    'pageParam' => $groupName,
                ],
            ]);
        }

        $supervisorGroupForm = new SupervisorGroupForm();
        $dataProvider = new ArrayDataProvider([
            'models' => $groups,
            'totalCount' => count($groups),
        ]);

        return $this->render(sprintf("%s/%s", self::VIEWS_DIR, 'index'), [
            'supervisorGroupForm' => $supervisorGroupForm,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return MainProcess|object
     * @throws \yii\base\InvalidConfigException
     */
    private function getSupervisorMainProcess(): MainProcess
    {
        return \Yii::$container->get(MainProcess::class);
    }

    /**
     * @param \Exception $error
     *
     * @return string
     */
    private function renderErrorHandle(\Exception $error): string
    {
        return $this->render(sprintf("%s/%s", self::VIEWS_DIR, 'error'), ['message' => $error->getMessage()]);
    }
}
