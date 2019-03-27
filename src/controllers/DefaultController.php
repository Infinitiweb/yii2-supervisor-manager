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
     * Lists all Domain models.
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        try {
            $supervisor = $this->_supervisorMainProcess();
        } catch (ConnectionException $error) {
            return $this->_errorHandle($error);
        }

        $groups = $supervisor->getAllProcessesByGroup();

        foreach ($groups as $groupName => &$group) {

            $group['group'] = $groupName;

            $group['processList'] = new ArrayDataProvider(
                ['allModels' => $group['processList'],
                    'pagination' => ['pageSize' => 5, 'pageParam' => $groupName]
                ]
            );
        }

        $supervisorGroupForm = new SupervisorGroupForm();

        $dataProvider = new ArrayDataProvider(['models' => $groups]);

        return $this->_renderProcess(
            'index',
            [
                'supervisorGroupForm' => $supervisorGroupForm,
                'dataProvider' => $dataProvider
            ]
        );
    }

    public function actionStartSupervisor()
    {
        MainProcess::forceStart(1500);

        $this->redirect(Url::to('/supervisor/default/index'));
    }

    /**
     * @throws \Exception
     */
    public function actionCreateGroup()
    {
        $model = new SupervisorGroupForm;

        $request = \Yii::$app->request;

        if ($model->load($request->post())) {
            $model->saveGroup();
        }

        Event::trigger(
            Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED
        );

        $this->redirect(Url::to('/supervisor/default/index'));
    }

    public function actionRestoreFromBackup()
    {
        (new ConfigFileHandler())->restoreFromBackup();

        Event::trigger(
            Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED
        );

        $this->redirect(Url::to('/supervisor/default/index'));
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionProcessControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $process = $this->_supervisorProcess($request->post('processName'));

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
    public function actionSupervisorControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $supervisor = $this->_supervisorMainProcess();

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
     * Responsible for the process control of the entire group.
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGroupControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $group = $this->_supervisorGroup(
                $request->post('groupName')
            );

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
    public function actionProcessConfigControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $group = new ProcessConfig(
                $request->post('groupName')
            );

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }

            Event::trigger(
                Supervisor::class, Supervisor::EVENT_CONFIG_CHANGED
            );
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * Get log or errors output of single supervisor process.
     *
     * @return array
     * @throws \infinitiweb\supervisorManager\components\supervisor\exceptions\ProcessException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetProcessLog()
    {
        $request = \Yii::$app->request;

        $response = ['isSuccessful' => false];

        try {
            $processLog = $this->_supervisorProcess(
                $request->post('processName')
            )->getProcessOutput($request->post('logType'));

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
    public function actionCountGroupProcesses()
    {
        $request = \Yii::$app->request;

        $group = new ProcessConfig(
            $request->post('groupName')
        );

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
    private function _renderProcess($view, $data)
    {
        if(\Yii::$app->request->getHeaders()->has('X-PJAX')) {
            return $this->renderAjax($view, $data);
        } else {
            return $this->render($view, $data);
        }
    }

    /**
     * @param \Exception $error
     *
     * @return string
     */
    private function _errorHandle(\Exception $error)
    {
        return $this->render('error', ['message' => $error->getMessage()]);
    }

    /**
     * @return MainProcess|object
     * @throws \yii\base\InvalidConfigException
     */
    private function _supervisorMainProcess()
    {
        return \Yii::$container->get(MainProcess::class);
    }

    /**
     * @param $processName
     *
     * @return Process|object
     * @throws \yii\base\InvalidConfigException
     */
    private function _supervisorProcess($processName)
    {
        return \Yii::$container->get(Process::class, [$processName]);
    }

    /**
     * @param $groupName
     *
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    private function _supervisorGroup($groupName)
    {
        return \Yii::$container->get(Group::class, [$groupName]);
    }
}
