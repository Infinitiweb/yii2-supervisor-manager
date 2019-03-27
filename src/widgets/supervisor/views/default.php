<?php

use yii\widgets\Pjax;

/**
 * @var string $supervisorHtml
 */

?>

<?php Pjax::begin([
    'id' => 'supervisor-manager',
]); ?>
<?= $supervisorHtml; ?>
<?php Pjax::end(); ?>
