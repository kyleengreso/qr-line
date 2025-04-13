<?php

include_once __DIR__ . "/../base.php";
restrictEmployeeMode();

$token = $_COOKIE['token'];
$token = decryptToken($token, $master_key);
$token = json_encode($token);
$token = json_decode($token);

$id = $token->id;
$username = $token->username;
$role_type = $token->role_type;
$email = $token->email;
$counterNumber = $token->counterNumber;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Dashboard | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>
    <div></div>
    <div class="container d-flex justify-content-center align-items-center before-footer container-set" style="margin-top: 50px">
        <div class="text-center w-100" style="max-width: 400px;" id="employeeDashboard">
            <h3 class="fw-bold">COUNTER <span id="employee-counter-number"><?php echo $counterNumber?></span></h3>

            <p class="mb-3">Current Serving</p>
            <div class="border border-warning rounded p-4 fw-bold fs-1 mb-3">
                <span id="queue-number"></span>
            </div>
            <form method="POST" id="frmNextTransaction">
                <button type="submit" name="next_queue" id="btn-counter-success"class="btn btn-warning text-white fw-bold px-4">NEXT</button>
                <button type="submit" name="skip_queue" id="btn-counter-skip"class="btn btn-warning text-white fw-bold px-4">SKIP</button>
            </form>
            <div class="py-3">
                <a class="btn btn-info fw-bold text-white" id="employee-cut-off" data-toggle="modal" data-target="#cutOffModal">Cut Off</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cutOffModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Employee: <?php echo $username?> is cut off</h5>
                </div>
                <div class="modal-body py-4 px-6 fw-bold" id="viewEmployeeBody">
                    You are cut off for temporary.
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="employee-resume">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script>
        var protocol = window.location.protocol;
        var host = window.location.host;
        var realHost = protocol + '//' + host;
        let x = <?php echo $counterNumber . $id?>;
        function fetchTransaction() {
            let resp = null;
            const params = new URLSearchParams({
                cashier: true,
                employee_id: <?php echo $id?>
            });
            $.ajax({
                url: `${realHost}/public/api/api_endpoint.php?${params}`,
                type: 'GET',
                success: function(response) {
                    let queue_number = document.getElementById('queue-number');
                    if (response.status === 'success') {
                        resp = response;
                        queue_number.innerHTML = response.data.queue_number;
                        console.log(resp);
                    } else {
                        queue_number.innerHTML = "No queue";
                        console.log('Error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }

        let btn_counter_success = document.getElementById('btn-counter-success');
        let btn_counter_skip = document.getElementById('btn-counter-skip');
        btn_counter_success.addEventListener('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: `${realHost}/public/api/api_endpoint.php`,
                type: 'POST',
                data: JSON.stringify({
                    method: 'cashier-success',
                    idemployee: <?php echo $id?>,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        fetchTransaction();
                        // console.log('Transaction completed successfully');
                    } else {
                        console.log('Error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Raw Response:', xhr.responseText);
                }
            });
        });

        
        btn_counter_skip.addEventListener('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: `${realHost}/public/api/api_endpoint.php`,
                type: 'POST',
                data: JSON.stringify({
                    method: 'cashier-missed',
                    idemployee: <?php echo $id?>,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        fetchTransaction();
                        // console.log('Transaction skipped successfully');
                    } else {
                        console.log('Error:', response.message);
                        console.log('Raw Response:', xhr.responseText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        });

        // Cut Off Feature
        var operational = true;
        let cutOff = document.getElementById('employee-cut-off');
        let btn_counter_resume = document.getElementById('employee-resume');
        
        cutOff.addEventListener('click', function(e) {
            e.preventDefault();
            operational = false;
        });
        
        btn_counter_resume.addEventListener('click', function(e) {
            e.preventDefault();
            operational = true;
        });


        fetchTransaction();

        setInterval(() => {
            if (operational) {
                fetchTransaction();
            }
        }, 5000);


    </script>
    <!-- <script src="./../asset/js/dashboard_cashier.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
