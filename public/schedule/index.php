<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
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
    <?php head_icon() ?>
    <title>Settings | <?php echo $project_name ?></title>
    <?php head_css() ?>
    <?php before_js() ?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;min-height:500px">
        <div class="col-md-6" style="min-width:400px;max-width:900px;transform:scale(0.9)">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username ?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" style="text-decoration:none;color:black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Settings</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header">
                    Transaction: Limit Rate/day
                </div>
                <form id="frmTransactionLimitForm"> 
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>This feature will set limit transaction requests per day.</span>
                        </div>
                        <div class="alert alert-success d-none" id="notify-transaction-limit">
                            <i class="bi bi-info-circle-fill"></i>
                            <span id="notify-transaction-limit-message">Transaction limit set successfully.</span>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="schedule_requester_enable" id="schedule_requester_enable" value="1">
                            <label class="form-check-label" for="schedule_requester_enable">Enable</label>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12">
                                <span>Transaction Limit:</span>
                            </div>
                            <div class="col-12">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-hourglass-top"></i></div>
                                    <div class="form-floating">
                                        <input type="number" name="transaction_limit" id="transaction_limit" class="form-control" placeholder="Transaction Limit" value="10">
                                        <label for="transaction_limit">Transaction Limit</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 w-md-25" type="submit">
                            <i class="bi bi-check-lg"></i> 
                            <span>
                                Save
                            </span>   
                        </button>
                    </div>
                </form>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header">
                    Schedule: Time Range
                </div>
                <form id="frmScheduleRequesterForm">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>This schedule is used for what time will open the requester form.</span>
                        </div>
                        <div class="alert alert-success d-none" id="notify-scheduler-requester">
                            <i class="bi bi-info-circle-fill"></i>
                            <span id="notify-scheduler-requester-message">Schedule set successfully.</span>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="schedule_requester_enable" id="schedule_requester_enable" value="1">
                            <label class="form-check-label">Enable</label>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <span>Time Range</span>
                            </div>
                            <div class="col-6">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-hourglass-top"></i></div>
                                    <div class="form-floating">
                                        <input type="time" name="schedule_requester_time_start" id="schedule_requester_time_start" class="form-control" placeholder="Time Schedule Start" value="08:00">
                                        <label for="schedule_requester_time_start">Time Start</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-hourglass-bottom"></i></div>
                                    <div class="form-floating">
                                        <input type="time" name="schedule_requester_time_end" id="schedule_requester_time_end" class="form-control" placeholder="Time Schedule End" value="17:00">
                                        <label for="schedule_requester_time_end">Time End</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12">
                                <span>Days of the Week:</span>
                            </div>
                            <div class="col-12">
                                <div class="border border-primary rounded p-3">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="sun" value="sun">
                                            <label class="form-check-label" for="sunday">Sun</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="mon" value="mon">
                                            <label class="form-check-label" for="monday">Mon</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="tue" value="tue">
                                            <label class="form-check-label" for="tuesday">Tue</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="wed" value="wed">
                                            <label class="form-check-label" for="wednesday">Wed</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="thu" value="thu">
                                            <label class="form-check-label" for="thursday">Thu</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="fri" value="fri">
                                            <label class="form-check-label" for="friday">Fri</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="sat" value="sat">
                                            <label class="form-check-label" for="saturday">Sat</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 w-md-25" type="submit">
                            <i class="bi bi-check-lg"></i> 
                            <span>
                                Save
                            </span>   
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php after_js() ?>
    <?php include_once "./../includes/footer.php"; ?>
    <script src="./../asset/js/message.js"></script>
    <script>
        let frmScheduleRequesterForm = document.getElementById('frmScheduleRequesterForm');

        // Load the current schedule settings
        function load_schedule_requester_form() {
            $.ajax({
                url: '/public/api/api_endpoint.php?schedule-requester_form',
                type: 'GET',
                success: function(response) {
                    console.log("Response GET:", response);
                    if (response.status === 'success') {
                        let schedule = response.data;
                        document.getElementById('schedule_requester_enable').checked = schedule.enable == 1;
                        document.getElementById('schedule_requester_time_start').value = schedule.time_start;
                        document.getElementById('schedule_requester_time_end').value = schedule.time_end;
        
                        // Validate and process `schedule.everyday`
                        if (schedule.everyday && schedule.everyday.trim() !== '') {
                            let days = schedule.everyday.split(';');
                            days.forEach(day => {
                                console.log(`Checking day: ${day}`);
                                let checkbox = document.getElementById(day);
                                if (checkbox) {
                                    checkbox.checked = true;
                                } else {
                                    console.error(`Checkbox with id "${day}" not found`);
                                }
                            });
                        } else {
                            console.warn("No days found in `schedule.everyday`");
                        }
                    } else {
                        console.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }

        load_schedule_requester_form();
        frmScheduleRequesterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let schedule_requester_enable = formData.get('schedule_requester_enable') ? formData.get('schedule_requester_enable') : 0;
            let schedule_requester_time_start = formData.get('schedule_requester_time_start');
            let schedule_requester_time_end = formData.get('schedule_requester_time_end');
            let days = formData.getAll('days[]').join(';');

            console.log('Schedule Requester Form Data:', {
                schedule_requester_enable,
                schedule_requester_time_start,
                schedule_requester_time_end,
                days
            });

            let notify_scheduler_requester = document.getElementById('notify-scheduler-requester');
            let notify_scheduler_requester_message = document.getElementById('notify-scheduler-requester-message');
            
            $.ajax({
                url: './../api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify({
                    enable: schedule_requester_enable,
                    time_start: schedule_requester_time_start,
                    time_end: schedule_requester_time_end,
                    repeat: 'daily',
                    everyday: days,
                    method: 'schedule-update-requester_form'
                }),
                success: function(response) {
                    console.log("Response:" + response);
                    notify_scheduler_requester.classList.remove('alert-success', 'alert-danger', 'alert-info');
                    if (response.status === 'success') {
                        notify_scheduler_requester.classList.add('alert-success');
                        notify_scheduler_requester_message.innerHTML = response.message;
                        notify_scheduler_requester.classList.remove('d-none');

                        setTimeout(() => {
                            // notify_scheduler_requester.classList.add('d-none');
                            window.location.reload();
                        }, 2000);
                    } else {
                        notify_scheduler_requester.classList.add('alert-danger');
                        notify_scheduler_requester_message.innerHTML = response.message;
                        notify_scheduler_requester.classList.remove('d-none');
                        // message_error(frmScheduleRequesterForm, response.message);
                        setTimeout(() => {
                            notify_scheduler_requester.classList.add('d-none');
                        }, 2000); 
                    }
                },
            })
            
            console.log('Ready');



        });
    </script>
</body>
</html>