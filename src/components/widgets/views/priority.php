<?php

/**
 * @var $progressBarClass string
 * @var $progressBarWidth string
 * @var $priority integer
 */

?>

<div class="progress">
    <div class="progress-bar progress-bar-<?= $progressBarClass; ?>"
         role="progressbar" aria-valuenow="<?= $priority; ?>" aria-valuemin="0"
         aria-valuemax="999" style="width: <?= $progressBarWidth ?>%"><span class="sr-only"></span>
    </div>
</div>
