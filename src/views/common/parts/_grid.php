<?php

use infinitiweb\supervisorManager\components\gridView\GridView;
use infinitiweb\supervisorManager\components\widgets\ProcessPriorityWidget;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $this View
 * @var $dataProvider ArrayDataProvider
 */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'tableTitle' => Yii::t('common', 'Process status'),
    'beforeItems' => $this->render('_supervisorControl'),
    'layout' => '{items}{pager}',
    'columns' => [
        [
            'label' => Yii::t('common', 'Group Name'),
            'value' => 'group',
        ],
        [
            'label' => Yii::t('common', 'Group Control'),
            'format' => 'raw',
            'contentOptions' => ['class' => 'groupOptions'],
            'value' => function ($model) {
                return $this->render('_groupControl', ['groupName' => $model['group']]);
            }
        ],
        [
            'label' => Yii::t('common', 'Group Process List'),
            'format' => 'raw',
            'contentOptions' => ['class' => 'processList'],
            'value' => function ($model) {
                return GridView::widget([
                    'dataProvider' => $model['processList'],
                    'layout' => '{items}{pager}',
                    'columns' => [
                        [
                            'label' => Yii::t('common', 'Process Name'),
                            'value' => 'name',
                            'options' => ['width' => '13%'],
                        ],
                        [
                            'label' => Yii::t('common', 'Up Time'),
                            'format' => 'raw',
                            'options' => ['width' => '7%'],
                            'value' => function ($model) {
                                preg_match('/\d{1,2}(?:\:\d{1,2}){2}/', $model['description'], $upTime);

                                $classes = [
                                    'RUNNING' => 'success',
                                    'STOPPED' => 'warning',
                                    'SHUTDOWN' => 'primary',
                                    'FATAL' => 'danger'
                                ];

                                $class = sprintf("label label-%s", ArrayHelper::getValue($classes, $model['statename'], 'warning'));

                                return isset($upTime[0]) ? $upTime[0] : Html::tag('span', $model['statename'], ['class' => $class]);
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Last stop'),
                            'options' => ['width' => '11%'],
                            'value' => function ($model) {
                                return $model['stop'] ? date('Y-m-d H:i:s', $model['stop']) : "Wasn't stopped";
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Status'),
                            'format' => 'html',
                            'contentOptions' => ['align' => 'center'],
                            'options' => ['width' => '7%'],
                            'value' => function ($model) {
                                $classes = [
                                    'RUNNING' => 'success',
                                    'STOPPED' => 'warning',
                                    'SHUTDOWN' => 'primary',
                                    'FATAL' => 'danger'
                                ];

                                $class = sprintf("label label-%s", ArrayHelper::getValue($classes, $model['statename'], 'warning'));

                                return Html::tag('span', $model['statename'], ['class' => $class]);
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Process ID'),
                            'contentOptions' => ['align' => 'center'],
                            'options' => ['width' => '7%'],
                            'value' => 'pid'
                        ],
                        [
                            'label' => Yii::t('common', 'Started'),
                            'options' => ['width' => '13%'],
                            'value' => function ($model) {
                                return $model['start'] ? date('Y-m-d H:i:s', $model['start']) : "Wasn't started";
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Priority'),
                            'format' => 'raw',
//                                    'options' => ['width' => '10%'],
                            'value' => function ($model) {
                                return ProcessPriorityWidget::widget(['priority' => $model['priority']]);
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Output'),
                            'format' => 'raw',
                            'contentOptions' => ['align' => 'center'],
                            'value' => function ($model) {
                                return Html::button(Yii::t('common', 'Show output'), [
                                    'class' => 'btn btn-default showLog',
                                    'data-process-name' => "{$model['group']}:{$model['name']}",
                                    'data-log-type' => 'stdout_logfile'
                                ]);
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Error Log'),
                            'format' => 'raw',
                            'contentOptions' => ['align' => 'center'],
                            'value' => function ($model) {
                                return Html::button(Yii::t('common', 'Show errors'), [
                                    'class' => 'btn btn-default showLog',
                                    'data-process-name' => "{$model['group']}:{$model['name']}",
                                    'data-log-type' => 'stderr_logfile'
                                ]);
                            }
                        ],
                        [
                            'label' => Yii::t('common', 'Process Control'),
                            'contentOptions' => ['align' => 'center'],
                            'format' => 'raw',
//                                    'options' => ['width' => '5%'],
                            'value' => function ($model) {
                                $iconClass = 'stop';
                                $dataAction = 'stopProcess';

                                if ($model['state'] === 0 || $model['statename'] === 'FATAL') {
                                    $iconClass = 'play';
                                    $dataAction = 'startProcess';
                                }

                                $content = Html::tag('span', '', ['class' => "glyphicon glyphicon-{$iconClass}"]);

                                return Html::tag('a', $content, [
                                    'class' => 'btn btn-default processControl',
                                    'data-action-type' => $dataAction,
                                    'data-process-name' => "{$model['group']}:{$model['name']}"
                                ]);
                            }
                        ]
                    ]
                ]);
            }
        ],
    ],
]);
