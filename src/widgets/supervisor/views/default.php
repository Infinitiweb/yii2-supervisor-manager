<?php

use yii\widgets\Pjax;

/**
 * @var string $supervisorHtml
 */

?>

<?php Pjax::begin([
    'id' => 'supervisor-manager-widget',
]); ?>
<?= $supervisorHtml; ?>
<?php Pjax::end(); ?>
