<?php

/**
 * @var $progressBarClass string
 * @var $progressBarWidth string
 * @var $priority integer
 */

?>

<div class="progress">
    <div class="progress-bar <?= sprintf("progress-bar-%s", $progressBarClass); ?>"
         role="progressbar" aria-valuenow="<?= $priority; ?>" aria-valuemin="0"
         aria-valuemax="999" style="<?= sprintf("width: %s%%", $progressBarWidth); ?>"><span class="sr-only"></span>
    </div>
</div>
