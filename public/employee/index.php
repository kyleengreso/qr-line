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
$priority = $token->priority;
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
    <div class="container-lg d-flex justify-content-center align-items-center before-footer" style="margin-top: 100px">
        <div class="text-center w-100" style="max-width: 1200px;" id="employeeDashboard">
            <div class="row d-flex justify-content-center align-items-start" style="margin: auto;">
                <div class="col-12 col-md-6">
                    <div class="alert text-start alert-success d-none" id="logOutNotify">
                        <span><?php echo $username ?> has logged out successfully</span>
                    </div>
                    <div class="alert text-start alert-success d-none" id="cutOffNotification">Operational</div>
                    <h3 class="fw-bold">
                        COUNTER <span id="employee-counter-number"><?php echo $counterNumber ?></span>
                        <span class="text-danger d-none" id="cutOffState">(Cut-Off)</span>
                    </h3>
                    
                    <p class="mb-3">Current Serving</p>
                    <div class="border border-warning rounded p-4 fw-bold fs-1 mb-3">
                        <span id="queue-number">N/A</span>
                    </div>
                    <form method="POST" id="frmNextTransaction">
                        <div class="w-100 mb-4">
                            <div class="mb-4">
                                <button type="submit" name="next_queue" id="btn-counter-success" class="btn btn-warning text-white fw-bold px-4">NEXT</button>
                                <button type="submit" name="skip_queue" id="btn-counter-skip" class="btn btn-warning text-white fw-bold px-4">SKIP</button>
                            </div>
                            <div>
                                <a class="btn btn-danger ms-auto" id="employee-cut-off">Cut-Off</a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="w-100 mb-4">
                        <div class="card border-1 p-4 text-center">
                            <form class="d-none" action="" id="frmCutOff_trigger">
                                <div class="alert alert-info text-start d-none" id="cutOff_trigger_notification">
                                    <span id="cutOff_trigger_message">
                                        Standby...
                                    </span>
                                </div>
                                <div class="form-floating mb-4">
                                    <select class="form-select" name="cut_off_select" id="cut_off_select">
                                        <option value="null">No action</option>
                                        <option value="1">After this queue</option>
                                        <option value="3">After 3 queries</option>
                                        <option value="5">After 5 queries</option>
                                        <option value="10">After 10 queries</option>
                                        <!-- On production -->
                                        <!-- <option value="last">Until no transaction</option> -->
                                    </select>
                                    <label for="cut_off_select">Auto-cut off action</label>
                                </div>
                            </form>
                            <div class="alert alert-info d-none" id="frmCutOff_trigger_message">
                                <span>You need to resume to show Auto-cut off feature</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                <div class="card border-1 p-4" style="min-height:100%">
                    <table class="table table-striped table-members" id="table-transactions-student">
                        <thead>
                            <tr>
                                <th scope="col-2">#</th>
                                <th scope="col">Email</th>
                                <th scope="col">Payment</th>                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            </div>


        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script>
        var notify_priority = false;
        var notify_priority_timer = 5;
        var cutOff_auto = false;
        var queue_remain = null;
        var this_counter_priority = "<?php echo $priority; ?>";

        let frmCutOff_trigger = document.getElementById('frmCutOff_trigger');
        let frmCutOff_trigger_message = document.getElementById('frmCutOff_trigger_message');
        let cutOff_trigger_notification = document.getElementById('cutOff_trigger_notification');
        let cutOff_trigger_message = document.getElementById('cutOff_trigger_message');
    
        function queue_remain_set(queue_remain) {
            $.ajax({
                url: "/public/api/api_endpoint.php",
                method: "POST",
                data: JSON.stringify({
                    method : "counter_queue_remain",
                    counter_number : <?php echo $counterNumber?>,
                    queue_remain : queue_remain
                }),
                success: function (response) {
                    cutOff_trigger_notification.classList.remove('d-none');
                    notify_priority = true;
                    if (notify_priority && queue_remain != null) {
                        cutOff_trigger_message.innerText = "Queue remining set to " + queue_remain;
                    } else if (notify_priority && queue_remain == null) {
                        cutOff_trigger_message.innerText = "Auto-cut off is disabled";
                    }
                    setTimeout(() => {
                        notify_priority = false;
                        cutOff_trigger_notification.classList.add('d-none');
                    },notify_priority_timer * 1000);
                    console.log(response);
                }
            });
        }

        let cut_off_select = document.getElementById('cut_off_select');
        cut_off_select.addEventListener('change', function (e) {
            console.log(this.value);
            if (this.value == "null") {
                fetchCutOff();
                queue_remain_set(this.null);
            } else {
                fetchCutOff();
                queue_remain_set(this.value);
            }
        });

        function queue_remain_get() {
            let param = new URLSearchParams({
                counter_queue_remain: true,
                counter_number: <?php echo htmlspecialchars($counterNumber); ?>
            });
            $.ajax({
                url: "/public/api/api_endpoint.php?" + param.toString(),
                method: "GET",
                success: function(response) {
                    console.log("Response received:", response); // Log the response
                    queue_remain = response.queue_remain;
                    if (response.status === 'success') {
                        if (response.queue_remain != null) {
                            if (cutOff_trigger_notification.classList.contains('d-none')) {
                                cutOff_trigger_notification.classList.remove('d-none');
                                cutOff_trigger_notification.innerText = response.queue_remain + " queue remain.";
                            }
                        } else {
                            cutOff_trigger_notification.classList.add('d-none');
                        }
                        console.log("Success:", response.message);
                    } else {
                        // cutOff_trigger_notification.innerText = 
                        console.log("Error in response:", response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.error("Response Text:", xhr.responseText); // Log the raw response
                }
            });
        }
    
        let x = <?php echo $counterNumber . $id?>;
        function fetchTransaction() {
            let resp = null;
            console.log("Priority: ", this_counter_priority);
            var params = new URLSearchParams({
                cashier: true,
                employee_id: <?php echo $id?>
            });
            // console.log("Params", params.toString());
            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function(response) {
                    console.log("RECV:", response);
                    let queue_number = document.getElementById('queue-number');
                    if (response.status === 'success') {
                        resp = response;
                        queue_number.innerHTML = response.data.queue_number;
                        console.log(resp);
                    } else {
                        queue_number.innerHTML = "No queue";
                        if (cutOff_auto && cutOff_trigger_queue == 0) {
                            cutOff.click();
                        }
                        // console.log('Error:', response.message);     // Disable
                    }
                },
                error: function(xhr, status, error) {
                    // Check phph erro message json
                    console.log(xhr.responseText);
                    // console.error('AJAX Error:', status, error);
                }
            });
        }

        let btn_counter_success = document.getElementById('btn-counter-success');
        let btn_counter_skip = document.getElementById('btn-counter-skip');
        btn_counter_success.addEventListener('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify({
                    method: 'cashier-success',
                    idemployee: <?php echo $id?>,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        return;
                    } else {
                        console.log('Error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    // console.log('Raw Response:', xhr.responseText);
                }
            });
        });

        
        btn_counter_skip.addEventListener('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify({
                    method: 'cashier-missed',
                    idemployee: <?php echo $id?>,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        return;
                    } else {
                        console.log('Error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    // console.log('Raw Response:', xhr.responseText);
                }
            });
        });

        // Students
        let table_transactions_student = document.getElementById('table-transactions-student');
        let this_employee_id = <?php echo $id?>;
        function fetchStudentTransaction() {
            const params = new URLSearchParams({
                transactions: true,
                employee_id: this_employee_id,
                students: 1,
                email: "palawan.edu.ph",
                desc: true,
                date_range: 'today'
            });
        
            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function(response) {
                    let transactions = response.transactions;
                    while (table_transactions_student.rows.length > 1) {
                        table_transactions_student.deleteRow(-1);
                    }
                    if (Array.isArray(transactions) && transactions.length > 0) {
                        transactions.forEach((transaction) => {
                            let row = table_transactions_student.insertRow();
                            let cell1 = row.insertCell(0);
                            let cell2 = row.insertCell(1);
                            let cell3 = row.insertCell(2);
                            cell1.innerHTML = transaction.queue_number;
                            cell2.innerHTML = transaction.email;
                            cell3.innerHTML = transaction.payment;
                        });
                    } else {
                        // console.warn("No transactions found or invalid data format.");
                    }

                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }


        // Cut Off Feature

        var operational = false;
        let btn_counter_resume = document.getElementById('employee-resume');
        let cutOffNotification = document.getElementById('cutOffNotification');
        
        let cutOffState = document.getElementById('cutOffState');

        let cutOff = document.getElementById('employee-cut-off');
        const params = new URLSearchParams({
            employeeCutOff: true,
            id: <?php echo $id?>
        });

        async function fetchCutOff() {
            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    if (response.status == "success") {
                        console.log(response.cut_off);
                        if (response.cut_off_state == 1) {
                            operational = false;
                            frmCutOff_trigger.classList.add('d-none');
                            // frmCutOff_trigger.querySelectorAll('input, select, button, textarea').forEach(e => {
                            //     e.disabled = true;
                            // });
                            frmCutOff_trigger_message.classList.remove('d-none');
                            cutOffNotification.classList.remove('alert-success');
                            cutOffNotification.classList.add('alert-danger');
                            cutOffNotification.innerHTML = 'You have been cut-off';
                            cutOff.classList.remove('btn-danger');
                            cutOff.innerText = "Resume";
                            cutOff.classList.add('btn-success');
                            cutOffState.classList.remove('d-none');
                            btn_counter_success.disabled = true;
                            btn_counter_skip.disabled = true;
                        } else if (response.cut_off_state == 0){
                            operational = true;
                            frmCutOff_trigger.classList.remove('d-none');
                            frmCutOff_trigger_message.classList.add('d-none');
                            cutOffNotification.classList.remove('alert-danger');
                            cutOffNotification.classList.add('alert-success');
                            cutOffNotification.innerHTML = 'You are back to operational';
                            cutOff.classList.remove('btn-success');
                            cutOff.innerText = "Cut Off";
                            cutOff.classList.add('btn-danger');
                            cutOffState.classList.add('d-none');
                            btn_counter_success.disabled = false;
                            btn_counter_skip.disabled = false;
                        }
                    }
                }
            });
        };

        cutOff.addEventListener('click', function(e) {
            e.preventDefault();
            if (operational) {
                $.ajax({
                    url: '/public/api/api_endpoint.php',
                    type: 'POST',
                    data: JSON.stringify({
                        method: 'employee-cut-off',
                        id: <?php echo $id?>,
                    }),
                    success: function(response) {
                        fetchCutOff();
                        if (response.status === 'success') {
                            operational = false;
                            cutOffNotification.classList.remove('alert-success', 'd-none');
                            cutOffNotification.classList.add('alert-danger');
                            cutOffNotification.innerHTML = 'You have been cut-off';
                            cutOff.classList.remove('btn-danger');
                            cutOff.innerText = "Resume";
                            cutOff.classList.add('btn-success');
                            cutOffState.classList.remove('d-none');
                            btn_counter_success.disabled = true;
                            btn_counter_skip.disabled = true;
                            setTimeout(() => {
                                cutOffNotification.classList.add('d-none');
                            }, 5000);      
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            } else {
                $.ajax({
                    url: '/public/api/api_endpoint.php',
                    type: 'POST',
                    data: JSON.stringify({
                        method: 'employee-cut-off',
                        id: <?php echo $id?>,
                    }),
                    success: function(response) {
                        if (response.status === 'success') {
                            operational = true;
                            cutOffNotification.classList.remove('alert-danger', 'd-none');
                            cutOffNotification.classList.add('alert-success');
                            cutOffNotification.innerHTML = 'You are back to operational';
                            cutOff.classList.remove('btn-success');
                            cutOff.innerText = "Cut Off";
                            cutOff.classList.add('btn-danger');
                            cutOffState.classList.add('d-none');
                            btn_counter_success.disabled = false;
                            btn_counter_skip.disabled = false;
                            setTimeout(() => {
                                cutOffNotification.classList.add('d-none');
                            }, 5000);
                        } else {
                            console.log('Error:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                })
            }
        });


        async function daemon() {
            await fetchCutOff();
            queue_remain_get();
            if (operational) {
                fetchTransaction();
                fetchStudentTransaction();
            }
            // Schedule the next execution
            setTimeout(daemon, 500);
        }
        
        // Start the daemon loop
        daemon();
    </script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
