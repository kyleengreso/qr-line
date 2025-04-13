<div class="my-5">
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
            <p>
            <a class="text-black text-decoration-none">Login</a>
            </p>
            <p>
            <a class="text-black text-decoration-none">Register</a>
            </p>
            <p>
            <a class="text-black text-decoration-none">FAQ</a>
            </p>
        </div>

        <hr class="w-100 clearfix d-md-none" />

        <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Contact</h6>
            <p><i class="fa-solid fa-location-dot"></i><?php echo $project_address?></p>
            <p><i class="fas fa-envelope mr-3"></i><?php echo $project_email?></p>
            <p><i class="fas fa-phone mr-3"></i><?php echo $project_phone?></p>
        </div>

        <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Follow us</h6>
            <a class="btn btn-primary btn-floating m-1" style="background-color: #3b5998" href="#!" role="button">
            <i class="fab fa-facebook-f"></i>
            </a>
            <a class="btn btn-primary btn-floating m-1" style="background-color: #55acee" href="#!" role="button">
            <i class="fab fa-twitter"></i>
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