<div class="row">
    <div class="col-sm-6">
        <div class="btn-group">
            <button type="button" class="btn btn-default supervisorControl" data-action="restart">
                <i class="glyphicon glyphicon-repeat"></i>
                <?= Yii::t('common', 'Restart Supervisor'); ?>
            </button>

            <button type="button" class="btn btn-default supervisorControl" data-action="refresh">
                <span class="glyphicon glyphicon-refresh"></span>
                <?= Yii::t('common', 'Update Status'); ?>
            </button>

            <button type="button" class="btn btn-default supervisorControl" data-action="stop">
                <i class="glyphicon glyphicon-stop"></i>
                <?= Yii::t('common', 'Stop All'); ?>
            </button>

            <button type="button" class="btn btn-default supervisorControl" data-action="start">
                <i class="glyphicon glyphicon-play"></i>
                <?= Yii::t('common', 'Start All'); ?>
            </button>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default createGroup" data-target="#createGroup" data-toggle="modal">
                <span class="glyphicon glyphicon-plus"></span>
                <?= Yii::t('common', 'New Group'); ?>
            </button>
        </div>
    </div>
</div>
