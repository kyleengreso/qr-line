<?php
include './../base.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Queue | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
    <link rel="stylesheet" href="./../asset/css/user_number.css">
    <?php
    $requester_token = $_SESSION['requester_token'];
    $web_domain = $_SERVER['HTTP_HOST'];
    $web_resource = $_SERVER['REQUEST_URI'];
    $website = $web_domain . $web_resource . '?requester_token=' . $requester_token;
    ?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top:50px;margin-bottom:100px;flex-direction:column">
        <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top:100px;flex-direction:column">
            <div class="alert alert-info" id="this_requester_status_alert">
                Status: <span class="fw-bold" id="this_requester_status_info"></span>
            </div>
        </div>
        <div class="row circle text-center">
            <div>
                <img src="./../asset/images/logo_blk.png" alt="logo" width="75px" style="margin-top: -15px;">
            </div>
            <div class="info-container">
                <div class="info-box">
                    <p class="label">Number:</p>
                    <p class="value" id="queueNumber">N/A</p>
                </div>
                <div class="info-box">
                    <p class="label">Counter:</p>
                    <p class="value" id="counterNumber">N/A</p>
                </div>
            </div>
            <p class="current-number">Current number: <strong><span id="currentQueueNumber">N/A</span></strong></p>
        </div>
        <div class="d-flex justify-content-center align-items-center">
            <div class="mt-4 rounded-start p-4 d-flex justify-content-center" style="width: 100%">
                <a class="btn btn-primary text-white fw-bold" id="btnCancelRequestModal" data-toggle="modal" data-target="#requestCancelModal">Cancel Request</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requestCancelModal" tabindex="-1" role="dialog"  aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Cancel Transaction?</h5>
                </div>
                <div class="modal-body py-4 px-6 fw-bold" id="viewEmployeeBody">
                    Do you want to cancel you current transaction?
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="btnCancelRequest">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php after_js()?>
    <script src="./../asset/js/requester_number.js"></script>
    <script>
        let this_requester_status_alert = document.getElementById("this_requester_status_alert");
        let this_requester_status_info = document.getElementById("this_requester_status_info");

        function fetchYourQuery() {
            const token = new URLSearchParams(window.location.search).get('requester_token');

            if (!token) {
                alert("Token was not assigned");
                window.location.href = `${realHost}/public/requester/requester_form.php`;
            }

            var params = new URLSearchParams({
                requester_number: true,
                requester_token : token
            })
            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        if (response.requester_status == "pending" || response.requester_status == "serve") {
                            this_requester_status_alert.classList.remove('alert-success', 'alert-danger', 'alert-warning');
                            this_requester_status_alert.classList.add('alert-info');
                            this_requester_status_info.textContent = response.requester_status.toUpperCase();
                        } else if (response.requester_status == "completed") {
                            this_requester_status_alert.classList.remove('alert-info', 'alert-danger', 'alert-warning');
                            this_requester_status_alert.classList.add('alert-success');
                            this_requester_status_info.textContent = response.requester_status.toUpperCase();
                        } else if (response.requester_status == "missed") {
                            this_requester_status_alert.classList.remove('alert-success', 'alert-danger', 'alert-info');
                            this_requester_status_alert.classList.add('alert-warning');
                            this_requester_status_info.textContent = response.requester_status.toUpperCase();
                        } else if (response.requester_status == "cancelled") {
                            this_requester_status_alert.classList.remove('alert-success', 'alert-info', 'alert-warning');
                            this_requester_status_alert.classList.add('alert-danger');
                            this_requester_status_info.textContent = response.requester_status.toUpperCase();
                        }

                        // Update button based on status
                        const actionButton = document.getElementById('btnCancelRequestModal');
                        if (response.requester_status == "completed") {
                            actionButton.textContent = 'Exit';
                            actionButton.classList.remove('btn-primary');
                            actionButton.classList.add('btn-success');
                            actionButton.setAttribute('data-toggle', '');
                            actionButton.setAttribute('data-target', '');
                            actionButton.href = '/public/requester/requester_form.php';
                        } else {
                            actionButton.textContent = 'Cancel Request';
                            actionButton.classList.remove('btn-success');
                            actionButton.classList.add('btn-primary');
                            actionButton.setAttribute('data-toggle', 'modal');
                            actionButton.setAttribute('data-target', '#requestCancelModal');
                            actionButton.href = '#';
                        }

                        $('#queueNumber').text(response.queueNumber);
                        $('#counterNumber').text(response.counterNumber);
                        $('#currentQueueNumber').text(response.currentQueueNumber);

                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    $('#user_number').text('0');
                }
            });
        }

        let btnCancelRequest = document.getElementById("btnCancelRequest");
        if (btnCancelRequest) {
            btnCancelRequest.addEventListener("click", function () {
                // get token from url
                const token = new URLSearchParams(window.location.search).get('requester_token');
                console.log(token);
                var data = {
                    method: "requester-form-cancel",
                    token_number: token
                }
                $.ajax({
                    url: '/public/api/api_endpoint.php',
                    type: "POST",
                    data: JSON.stringify(data),
                    success: function (response) {
                        console.log(response);
                        if (response.status) {
                            alert(response.message);
                            window.location.href = '/public/requester/requester_form.php';
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Logout request failed:", error);
                        alert("An error occurred while logging out. Please try again.");
                    }
                });
            });
        }
        fetchYourQuery();

        setInterval(function() {
            fetchYourQuery()
        }, 5000);



    </script>
    <script>


    </script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
