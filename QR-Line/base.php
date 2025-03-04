<?php

$project_name = "QR-Line";

function after_js() {
    echo '
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>';
    return;
}
?>