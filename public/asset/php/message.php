<?php


function message_success($message) { ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php }


function message_error($message) { ?>
    <div class="alert alert-danger"><?php echo $message; ?></div>
<?php }


function message_warning($message) { ?>
    <div class="alert alert-warning"><?php echo $message; ?></div>
<?php }
?>