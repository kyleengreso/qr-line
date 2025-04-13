<?php
include_once __DIR__ . "/../base.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
<!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-orange-custom">
            <h5 class="modal-title fw-bold text-white" id="exampleModalLabel">Employee Info: marc</h5>
            </div>
            <div class="modal-body">
                <div class="p-4">
                    <div>
                        <div class="mb-2 row">
                            <div class="col-6">User ID</div>
                            <div class="col-6 fw-bold">1</div>
                        </div>
                        <div class="mb-2 row">
                            <div class="col-6">Username:</div>
                            <div class="col-6 fw-bold">marc</div>
                        </div>
                        <div class="mb-2 row">
                            <div class="col-6">Active</div>
                            <div class="col-6 fw-bold">Yes</div>
                        </div>
                        <div class="mb-2 row">
                            <div class="col-6">Role Type</div>
                            <div class="col-6 fw-bold">Admin</div>
                        </div>
                        <div class="mb-2 row">
                            <div class="col-6">Last Login</div>
                            <div class="col-6 fw-bold">Yesterday</div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        
        <!-- Button trigger modal -->

        </div>
    </div>
    <?php after_js()?>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
        Launch demo modal
    </button>
    <div class="role_type_admin">
        Administrator
    </div>
    <div class="role_type_employee">
        Cashier
    </div>
    <div>
        <a class="btn btn-info" style="border-top-right-radius:0px;border-bottom-right-radius:0px">Info</a>
        <a class="btn btn-primary" style="border-top-right-radius:0px;border-bottom-right-radius:0px;border-top-left-radius:0px;border-bottom-left-radius:0px">Update</a>
        <a class="btn btn-danger" style="border-top-left-radius:0px;border-bottom-left-radius:0px">Delete</a>
    </div>
    <div>
        <a class="btn btn-info rounded-left">Info</a>
        <a class="btn btn-primary rounded-0">Update</a>
        <a class="btn btn-danger rounded-right">Delete</a>
    </div>


    <!-- Remove the container if you want to extend the Footer to full width. -->
<div class="container my-5">

<footer style="background-color: #eee6d3;">
  <div class="container p-4">
    <div class="row">
      <div class="col-lg-6 col-md-12 mb-4">
        <h5 class="mb-3 text-dark">footer content</h5>
        <p>
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Iste atque ea quis
          molestias. Fugiat pariatur maxime quis culpa corporis vitae repudiandae aliquam
          voluptatem veniam, est atque cumque eum delectus sint!
        </p>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <h5 class="mb-3 text-dark">links</h5>
        <ul class="list-unstyled mb-0">
          <li class="mb-1">
            <a href="#!" style="color: #4f4f4f;">FAQ</a>
          </li>
          <li class="mb-1">
            <a href="#!" style="color: #4f4f4f;">Classes</a>
          </li>
          <li class="mb-1">
            <a href="#!" style="color: #4f4f4f;">Pricing</a>
          </li>
          <li>
            <a href="#!" style="color: #4f4f4f;">Safety</a>
          </li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <h5 class="mb-1 text-dark">opening hours</h5>
        <table class="table" style="border-color: #666;">
          <tbody>
            <tr>
              <td>Mon - Fri:</td>
              <td>8am - 9pm</td>
            </tr>
            <tr>
              <td>Sat - Sun:</td>
              <td>8am - 1am</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
    © 2020 Copyright:
    <a class="text-dark" href="https://mdbootstrap.com/">MDBootstrap.com</a>
  </div>
  <!-- Copyright -->
</footer>

</div>
<!-- End of .container -->


<!-- Remove the container if you want to extend the Footer to full width. -->
<div class="container my-5">
<!-- Footer -->
<footer
        class="text-center text-lg-start text-white"
        style="background-color: #929fba"
        >
    <!-- Grid container -->
    <div class="container p-4 pb-0">
    <!-- Section: Links -->
    <section class="">
        <!--Grid row-->
        <div class="row">
        <!-- Grid column -->
        <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">
            Company name
            </h6>
            <p>
            Here you can use rows and columns to organize your footer
            content. Lorem ipsum dolor sit amet, consectetur adipisicing
            elit.
            </p>
        </div>
        <!-- Grid column -->

        <hr class="w-100 clearfix d-md-none" />

        <!-- Grid column -->
        <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Products</h6>
            <p>
            <a class="text-white">MDBootstrap</a>
            </p>
            <p>
            <a class="text-white">MDWordPress</a>
            </p>
            <p>
            <a class="text-white">BrandFlow</a>
            </p>
            <p>
            <a class="text-white">Bootstrap Angular</a>
            </p>
        </div>
        <!-- Grid column -->

        <hr class="w-100 clearfix d-md-none" />

        <!-- Grid column -->
        <hr class="w-100 clearfix d-md-none" />

        <!-- Grid column -->
        <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Contact</h6>
            <p><i class="fas fa-home mr-3"></i> New York, NY 10012, US</p>
            <p><i class="fas fa-envelope mr-3"></i> info@gmail.com</p>
            <p><i class="fas fa-phone mr-3"></i> + 01 234 567 88</p>
            <p><i class="fas fa-print mr-3"></i> + 01 234 567 89</p>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
            <h6 class="text-uppercase mb-4 font-weight-bold">Follow us</h6>

            <!-- Facebook -->
            <a
            class="btn btn-primary btn-floating m-1"
            style="background-color: #3b5998"
            href="#!"
            role="button"
            ><i class="fab fa-facebook-f"></i
            ></a>

            <!-- Twitter -->
            <a
            class="btn btn-primary btn-floating m-1"
            style="background-color: #55acee"
            href="#!"
            role="button"
            ><i class="fab fa-twitter"></i
            ></a>

            <!-- Google -->
            <a
            class="btn btn-primary btn-floating m-1"
            style="background-color: #dd4b39"
            href="#!"
            role="button"
            ><i class="fab fa-google"></i
            ></a>

            <!-- Instagram -->
            <a
            class="btn btn-primary btn-floating m-1"
            style="background-color: #ac2bac"
            href="#!"
            role="button"
            ><i class="fab fa-instagram"></i
            ></a>

            <!-- Linkedin -->
            <a
            class="btn btn-primary btn-floating m-1"
            style="background-color: #0082ca"
            href="#!"
            role="button"
            ><i class="fab fa-linkedin-in"></i
            ></a>
            <!-- Github -->
            <a
            class="btn btn-primary btn-floating m-1"
            style="background-color: #333333"
            href="#!"
            role="button"
            ><i class="fab fa-github"></i
            ></a>
        </div>
        </div>
        <!--Grid row-->
    </section>
    <!-- Section: Links -->
    </div>
    <!-- Grid container -->

    <!-- Copyright -->
    <div
        class="text-center p-3"
        style="background-color: rgba(0, 0, 0, 0.2)"
        >
    © 2020 Copyright:
    <a class="text-white" href="https://mdbootstrap.com/"
        >MDBootstrap.com</a
        >
    </div>
    <!-- Copyright -->
</footer>
<!-- Footer -->
</div>
<!-- End of .container -->
</body>
</html>
