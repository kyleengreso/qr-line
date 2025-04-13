<?php
// Checking if token was present
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $token = decryptToken($token, $master_key);
    $token = json_encode($token);
    $token = json_decode($token);
}
?>
<nav class="navbar fixed-top" style="background-color: rgb(255, 110, 55);">
    <div class="container d-flex justify-content-center align-items-center" style="width: 75vw;">
        <span class="navbar-brand mb-0 text-white d-flex align-items-center">
            <img src="./../asset/images/logo.png" alt="PSU logo" width="40" height="40" class="me-2">
            <span class="fs-5 fw-normal fs-4"><?php echo $project_name_full?></span>
        </span>
    </div>
    <a class="fw-bold text-white text-decoration-none" style="margin-right:10px" id="rtClock"></a>
    <?php if (isset($token) && $token) : ?>
    <a class="text-white fw-bold text-decoration-none" href="#" style="">
        <?php echo htmlspecialchars($token->username); ?>
    </a>
    <a class="btn btn-primary text-decoration-none" style="border-radius: 5px; text-black; border: 1px solid #fff; background-color: transparent; margin-left: 10px; margin-right: 20px;"
        id="btn-logout">
        <i class="fa-solid fa-right-from-bracket"></i>
    </a>
    <script src="./../asset/js/navbar.js"></script>
    <?php endif; ?>

</nav>