<?php

namespace infinitiweb\supervisorManager\controllers;

use Exception;
use infinitiweb\supervisorManager\components\filters\AjaxAccess;
use infinitiweb\supervisorManager\components\supervisor\config\ConfigFileHandler;
use infinitiweb\supervisorManager\components\supervisor\config\ProcessConfig;
use infinitiweb\supervisorManager\components\supervisor\control\Group;
use infinitiweb\supervisorManager\components\supervisor\control\MainProcess;
use infinitiweb\supervisorManager\components\supervisor\control\Process;
use infinitiweb\supervisorManager\components\supervisor\exceptions\SupervisorException;
use infinitiweb\supervisorManager\components\supervisor\Supervisor;
use infinitiweb\supervisorManager\models\SupervisorGroupForm;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\filters\ContentNegotiator;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class DefaultController
 *
 * @package infinitiweb\supervisorManager\controllers
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            AjaxAccess::class,
            'access' => Yii::$app->params['supervisorConfiguration']['access'],
            [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actionIndex(): array
    {
        return ['success' => true];
    }

    /**
     * @return array
     */
    public function actionStartSupervisor(): array
    {
        MainProcess::forceStart(1500);

        return ['success' => true];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionCreateGroup(): array
    {
        $model = new SupervisorGroupForm;

        if (!$model->load(Yii::$app->request->post()) || !$model->saveGroup()) {
            return [
                'success' => false,
                'message' => Json::encode($model->getErrors())
            ];
        }

        Event::trigger(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED);

        return ['success' => true];
    }

    /**
     * @return array
     */
    public function actionRestoreFromBackup(): array
    {
        (new ConfigFileHandler())->restoreFromBackup();
        Event::trigger(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED);

        return ['success' => true];
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function actionProcessControl(): array
    {
        $request = Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['success' => true];

        try {
            $process = $this->getSupervisorProcess($request->post('processName'));

            if ($process->hasMethod($actionType)) {
                $process->$actionType();
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Undefined action',
                ];
            }
        } catch (SupervisorException $error) {
            $response = [
                'success' => false,
                'message' => $error->getMessage(),
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function actionSupervisorControl(): array
    {
        $request = Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['success' => true];

        try {
            $supervisor = $this->getSupervisorMainProcess();

            if ($supervisor->hasMethod($actionType)) {
                $supervisor->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'success' => false,
                'message' => $error->getMessage(),
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function actionGroupControl(): array
    {
        $request = Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['success' => true];

        try {
            $group = $this->getSupervisorGroup($request->post('groupName'));

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'success' => false,
                'message' => $error->getMessage(),
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionProcessConfigControl(): array
    {
        $request = Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['success' => true];

        try {
            $group = new ProcessConfig($request->post('groupName'));

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }

            Event::trigger(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED);
        } catch (SupervisorException $error) {
            $response = [
                'success' => false,
                'message' => $error->getMessage(),
            ];
        }

        return $response;
    }

    /**
     * @return array
     */
    public function actionGetProcessLog(): array
    {
        $request = Yii::$app->request;

        try {
            $processLog = $this->getSupervisorProcess($request->post('processName'))
                ->getProcessOutput($request->post('logType'));

            $response = [
                'success' => true,
                'processLog' => $processLog ?: 'No logs',
            ];
        } catch (SupervisorException $error) {
            $response = [
                'success' => false,
                'message' => $error->getMessage(),
            ];
        } catch (Exception $error) {
            $response = [
                'success' => false,
                'message' => $error->getMessage(),
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionCountGroupProcesses(): array
    {
        $group = new ProcessConfig(Yii::$app->request->post('groupName'));

        return [
            'success' => true,
            'count' => $group->getNumprocs()
        ];
    }

    /**
     * @return MainProcess|object
     * @throws InvalidConfigException
     */
    private function getSupervisorMainProcess(): MainProcess
    {
        return Yii::$container->get(MainProcess::class);
    }

    /**
     * @param $processName
     *
     * @return Process|object
     * @throws InvalidConfigException
     */
    private function getSupervisorProcess($processName): Process
    {
        return Yii::$container->get(Process::class, [$processName]);
    }

    /**
     * @param $groupName
     *
     * @return Group|object
     * @throws InvalidConfigException
     */
    private function getSupervisorGroup($groupName): Group
    {
        return Yii::$container->get(Group::class, [$groupName]);
    }
}
