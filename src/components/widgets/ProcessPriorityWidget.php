<?php

namespace infinitiweb\supervisorManager\components\widgets;

use yii\base\Widget;

/**
 * Class ProcessPriorityWidget
 *
 * @package infinitiweb\supervisorManager\components\widgets
 */
class ProcessPriorityWidget extends Widget
{
    /** @var integer */
    public $priority;
    /** @var integer */
    public $maxPriority = 999;
    /** @var integer */
    public $minPriority = 0;
    /** @var array */
    public $classesRange = [
        'danger' => 33,
        'warning' => 66,
        'success' => 100,
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('priority', [
            'priority' => $this->priority,
            'progressBarWidth' => $this->getPriorityInPercent(),
            'progressBarClass' => $this->getProgressBarClass()
        ]);
    }

    /**
     * @return int
     */
    private function getPriorityInPercent(): int
    {
        return $this->priority * (100 / $this->maxPriority);
    }

    /**
     * @return string
     */
    private function getProgressBarClass(): string
    {
        $progressBarWidth = $this->getPriorityInPercent();
        $resultClass = '';

        foreach ($this->classesRange as $class => $range) {
            if ($progressBarWidth <= $range) {
                $resultClass = $class;
                break;
            }
        }

        $resultClass = $resultClass ?: array_pop($this->classesRange);

        return $resultClass;
    }
}
