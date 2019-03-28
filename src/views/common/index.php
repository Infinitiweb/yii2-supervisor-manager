<?php

use infinitiweb\supervisorManager\assets\base\BaseAsset;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $supervisorGroupForm \infinitiweb\supervisorManager\models\SupervisorGroupForm
 */

BaseAsset::register($this);

$this->title = 'Supervisor';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('parts/_modal');
echo $this->render('parts/_create-group', ['supervisorGroupForm' => $supervisorGroupForm]);
?>

<div class="supervisor-index">
    <?php Pjax::begin(['id' => 'supervisor', 'timeout' => 5000]); ?>
    <?= $this->render('parts/_grid', ['dataProvider' => $dataProvider]); ?>
    <?php Pjax::end(); ?>
</div>
