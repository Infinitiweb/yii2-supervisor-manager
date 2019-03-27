<?php

use infinitiweb\supervisorManager\assets\base\BaseAsset;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $supervisorGroupForm \supervisormanager\models\SupervisorGroupForm
 */

BaseAsset::register($this);

$this->title = 'Supervisor';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('parts/_modal', ['supervisorGroupForm' => $supervisorGroupForm]);
echo $this->render('parts/_create-group', ['supervisorGroupForm' => $supervisorGroupForm]);
?>

<style>
    .container {
        width: 100%;
    }
</style>

<div class="supervisor-index">
    <?php \yii\widgets\Pjax::begin(['id' => 'supervisor', 'timeout' => 5000]); ?>
    <?php echo $this->render('parts/_grid', ['dataProvider' => $dataProvider]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
