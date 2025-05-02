<div>
<footer class="text-center text-lg-start text-black footer">
    <div class="container p-4 pb-0">
    <section class="">
        <div class="row">
        <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
            <h2 class="text-uppercase mb-4 fw-bold">
            <img class="footer-logo-icon" src="./../asset/images/logo_blk.png" alt="">
            <?php echo $project_name; ?>
            </h2>
            <p>
            <?php echo $project_description; ?>
            </p>
        </div>

        <hr class="w-100 clearfix d-md-none" />

        <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
            <h6 class="text-uppercase mb-4">LINKS</h6>
            <?php if (!isset($_COOKIE['token'])): ?>
                <p>
                    <a class="text-black text-decoration-none" href="/public/auth/login.php">Login</a>
                </p>
                <?php if ($enable_register_employee) : ?>
                    <p>
                        <a class="text-black text-decoration-none" href="/public/auth/register.php">Register</a>
                    </p>
                    <?php endif; ?>
                <p>
                    <a class="text-black text-decoration-none" href="/public/stats">Stats</a>
                </p>
                <!-- <p>
                    <a class="text-black text-decoration-none">FAQ</a>
                </p> -->
                <?php endif; ?>
        </div>

        <hr class="w-100 clearfix d-md-none" />

        <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Contact</h6>
            <p><i class="bi bi-geo-alt-fill"></i><?php echo $project_address?></p>
            <p><i class="bi bi-envelope-at-fill"></i><?php echo $project_email?></p>
            <p><i class="bi bi-telephone-fill"></i><?php echo $project_phone?></p>
        </div>

        <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Follow us</h6>
            <a class="rounded-circle btn btn-floating m-1" href="#!" role="button">
            <i class="fs-1 bi bi-facebook" style="color:#3b5998"></i>
            </a>
            <a class="rounded-circle btn btn-floating m-1" href="#!" role="button">
            <i class="fs-1 bi bi-twitter-x"></i>
            </a>
        </div>
        </div>
    </section>
    </div>

    <div class="text-center p-3 footer-extend">
        <span>&copy <?php echo project_year()?> <?php echo $project_name?>, All Rights Reserved.</span>
    </div>
</footer>
</div>
