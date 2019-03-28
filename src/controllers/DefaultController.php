<?php

namespace infinitiweb\supervisorManager\controllers;

use infinitiweb\supervisorManager\components\filters\AjaxAccess;
use infinitiweb\supervisorManager\components\supervisor\control\Group;
use infinitiweb\supervisorManager\components\supervisor\control\MainProcess;
use infinitiweb\supervisorManager\components\supervisor\control\Process;
use infinitiweb\supervisorManager\components\supervisor\exceptions\ConnectionException;
use infinitiweb\supervisorManager\components\supervisor\exceptions\SupervisorException;
use infinitiweb\supervisorManager\components\supervisor\Supervisor;
use supervisormanager\components\supervisor\config\ConfigFileHandler;
use supervisormanager\components\supervisor\config\ProcessConfig;
use supervisormanager\models\SupervisorGroupForm;
use yii\base\Event;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class DefaultController
 *
 * @package infinitiweb\supervisorManager\controllers
 */
class DefaultController extends Controller
{
    /** @var string */
    private const VIEWS_DIR = '@infinitiwebSupervisorManager/views/common';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            [
                'class' => ContentNegotiator::class,
                'except' => [
                    'index',
                    'restore-from-backup',
                    'create-group',
                    'start-supervisor'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => AjaxAccess::class,
                'except' => [
                    'index',
                    'restore-from-backup',
                    'create-group',
                    'start-supervisor'
                ],
            ]
        ];
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function actionIndex(): string
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

        return $this->renderProcess(sprintf("%s/%s", self::VIEWS_DIR, 'index'), [
            'supervisorGroupForm' => $supervisorGroupForm,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return Response
     */
    public function actionStartSupervisor(): Response
    {
        MainProcess::forceStart(1500);

        return $this->redirect(Url::to('/supervisor/default/index'));
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function actionCreateGroup(): Response
    {
        $model = new SupervisorGroupForm;

        if ($model->load(\Yii::$app->request->post())) {
            $model->saveGroup();
        }

        Event::trigger(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED);

        return $this->redirect(Url::to('/supervisor/default/index'));
    }

    /**
     * @return Response
     */
    public function actionRestoreFromBackup(): Response
    {
        (new ConfigFileHandler())->restoreFromBackup();
        Event::trigger(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED);

        return $this->redirect(Url::to('/supervisor/default/index'));
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionProcessControl(): array
    {
        $request = \Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $process = $this->getSupervisorProcess($request->post('processName'));

            if ($process->hasMethod($actionType)) {
                $process->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSupervisorControl(): array
    {
        $request = \Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $supervisor = $this->getSupervisorMainProcess();

            if ($supervisor->hasMethod($actionType)) {
                $supervisor->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGroupControl(): array
    {
        $request = \Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $group = $this->getSupervisorGroup($request->post('groupName'));

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionProcessConfigControl(): array
    {
        $request = \Yii::$app->request;
        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $group = new ProcessConfig($request->post('groupName'));

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }

            Event::trigger(Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED);
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \infinitiweb\supervisorManager\components\supervisor\exceptions\ProcessException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetProcessLog(): array
    {
        $request = \Yii::$app->request;

        $response = ['isSuccessful' => false];

        try {
            $processLog = $this->getSupervisorProcess($request->post('processName'))
                ->getProcessOutput($request->post('logType'));

            $response = [
                'isSuccessful' => true,
                'processLog' => $processLog ?: 'No logs'
            ];
        } catch (SupervisorException $error) {
            $response = ['error' => $error->getMessage()];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionCountGroupProcesses(): array
    {
        $group = new ProcessConfig(\Yii::$app->request->post('groupName'));

        return [
            'count' => $group->getNumprocs()
        ];
    }

    /**
     * @param $view
     * @param $data
     *
     * @return string
     */
    private function renderProcess($view, $data): string
    {
        return $this->renderAjax($view, $data);
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

    /**
     * @return MainProcess|object
     * @throws \yii\base\InvalidConfigException
     */
    private function getSupervisorMainProcess(): MainProcess
    {
        return \Yii::$container->get(MainProcess::class);
    }

    /**
     * @param $processName
     *
     * @return Process|object
     * @throws \yii\base\InvalidConfigException
     */
    private function getSupervisorProcess($processName): Process
    {
        return \Yii::$container->get(Process::class, [$processName]);
    }

    /**
     * @param $groupName
     *
     * @return Group|object
     * @throws \yii\base\InvalidConfigException
     */
    private function getSupervisorGroup($groupName): Group
    {
        return \Yii::$container->get(Group::class, [$groupName]);
    }
}
