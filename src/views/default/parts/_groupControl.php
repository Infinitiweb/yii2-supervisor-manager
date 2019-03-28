<?php

/**
 * @var $groupName string
 */

?>

<!--<div class="margin">-->
<div class="btn-group groupControl" data-group-name="<?= $groupName ?>">
    <button type="button" class="btn btn-default">
        <?= Yii::t('common', 'Group Options'); ?>
    </button>

    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">
            <?= Yii::t('common', 'Toggle Dropdown'); ?>
        </span>
    </button>

    <ul class="dropdown-menu" role="menu">
        <li>
            <a href="#" data-action="startProcessGroup">
                <i class="fa fa-play"></i>
                <?= Yii::t('common', 'Start all'); ?>
            </a>
        </li>

        <li class="divider"></li>
        <li>
            <a href="#" data-action="stopProcessGroup">
                <i class="fa fa-stop"></i>
                <?= Yii::t('common', 'Stop all'); ?>
            </a>
        </li>

        <li class="divider"></li>
        <li>
            <a href="#" class="processConfigControl" data-action="addNewGroupProcess">
                <i class="fa fa-plus"></i>
                <?= Yii::t('common', 'Create new process'); ?>
            </a>
        </li>

        <li class="divider"></li>
        <li>
            <a href="#" class="processConfigControl" data-group-process-delete>
                <i class="fa fa-remove"></i>
                <?= Yii::t('common', 'Remove process'); ?>
            </a>
        </li>

        <li class="divider"></li>
        <li>
            <a href="#" class="processConfigControl" data-action="deleteProcess" data-need-confirm>
                <i class="fa fa-minus-square"></i>
                <?= Yii::t('common', 'Remove group'); ?>
            </a>
        </li>
    </ul>
</div>
