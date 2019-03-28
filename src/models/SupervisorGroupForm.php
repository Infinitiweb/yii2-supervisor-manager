<?php

namespace infinitiweb\supervisorManager\models;

use infinitiweb\supervisorManager\components\supervisor\config\ProcessConfig;
use yii\base\Model;

/**
 * Class SupervisorGroupForm
 *
 * @package infinitiweb\supervisorManager\models
 */
class SupervisorGroupForm extends Model
{
    /** @var string */
    public $groupName;
    /** @var string */
    public $command;
    /** @var boolean */
    public $autostart;
    /** @var integer */
    public $startretries;
    /** @var integer */
    public $numprocs;
    /** @var integer */
    public $priority;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['groupName', 'command', 'startretries', 'numprocs'],
                'filter',
                'filter' => 'trim'
            ],
            [
                ['groupName', 'command', 'startretries', 'numprocs', 'priority'],
                'required'
            ],
            [['groupName', 'command'], 'string'],
            [['startretries', 'numprocs', 'priority'], 'integer'],
            [['numprocs'], 'integer', 'max' => 20],
            [['priority'], 'integer', 'min' => 1, 'max' => 999],
            [['autostart'], 'boolean'],
            [['autostart'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'groupName' => 'Group name',
            'command' => 'Process command',
            'startretries' => 'Number of start retry',
            'numprocs' => 'Number of processes',
            'autostart' => 'Auto start',
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function saveGroup()
    {
        $processConfig = new ProcessConfig($this->groupName);

        return $processConfig->createGroup($this->attributes) ? true : false;
    }
}