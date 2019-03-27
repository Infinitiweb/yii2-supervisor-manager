<?php

namespace infinitiweb\supervisorManager\components\gridView;

/**
 * Class GridView
 *
 * @package infinitiweb\supervisorManager\components\gridView
 */
class GridView extends \yii\grid\GridView
{
    /** @var string Header title that will be displayed at top of table. */
    public $tableTitle;
    /** @var string */
    public $beforeItems = '';

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->layout = $this->render('gridTemplate');
    }
}
