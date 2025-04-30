<?php
include_once './../base.php';
$local_ip = getHostByName(getHostName());
$domain_name = $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php
    echo $local_ip;
    head_icon();
    head_css();
    before_js();
    ?>
</head>
<body>
    <span id="test">W</span>
    <?php
    after_js();
    ?>
    <script>
        let test  = document.getElementById('test');
        test.innerHTML = realHost;
    </script>
</body>
</html>