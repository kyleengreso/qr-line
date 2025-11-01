<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
// `base.php` already normalizes the token into $token (stdClass) if cookie exists
// Access defensively to avoid PHP notices when some claims are missing
if (!isset($token) || !$token) {
    $token = null;
}

$id = isset($token->id) ? $token->id : null;
$username = isset($token->username) ? $token->username : null;
// Prefer role from token, fallback to role_type cookie set during authentication
$role_type = isset($token->role_type) ? $token->role_type : (isset($_COOKIE['role_type']) ? $_COOKIE['role_type'] : null);
$email = isset($token->email) ? $token->email : null;
$counterNumber = isset($token->counterNumber) ? $token->counterNumber : null;
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
                            <input class="form-check-input" type="checkbox" name="transaction_limit_enable" id="transaction_limit_enable" value="1">
                            <label class="form-check-label" for="transaction_limit_enable">Enable</label>
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
        const endpointHost = "<?php echo isset($endpoint_server) ? $endpoint_server : (isset($endpoint_host) ? $endpoint_host : ''); ?>";
    </script>
    <script>
        let frmScheduleRequesterForm = document.getElementById('frmScheduleRequesterForm');
        let frmTransactionLimitForm = document.getElementById('frmTransactionLimitForm');

        // Load the current schedule settings
        function load_schedule_requester_form() {
            if (!(endpointHost && endpointHost.length > 0)) { return; }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/schedule/requester_form',
                type: 'GET',
                xhrFields: { withCredentials: true },
                success: function(response) {
                    console.log("Response GET:", response);
                    if (response.status === 'success') {
                        let schedule = response.data;
                        document.getElementById('schedule_requester_enable').checked = schedule.enable == 1;
                        document.getElementById('schedule_requester_time_start').value = schedule.time_start;
                        document.getElementById('schedule_requester_time_end').value = schedule.time_end;
        
                        // Validate and process `schedule.everyday`
                        // treat null/empty as "every day" and support JSON array/object or semicolon/comma separated strings
                        if (!schedule.everyday || schedule.everyday.trim() === '') {
                            // No restriction -> check all day boxes
                            ['sun','mon','tue','wed','thu','fri','sat'].forEach(id => {
                                const cb = document.getElementById(id);
                                if (cb) cb.checked = true;
                            });
                        } else {
                            let raw = schedule.everyday;
                            try {
                                const parsed = JSON.parse(raw);
                                if (Array.isArray(parsed)) {
                                    parsed.forEach(d => {
                                        const id = String(d).toLowerCase();
                                        const cb = document.getElementById(id);
                                        if (cb) cb.checked = true;
                                    });
                                } else if (parsed && typeof parsed === 'object') {
                                    Object.keys(parsed).forEach(k => {
                                        if (parsed[k]) {
                                            const id = String(k).toLowerCase();
                                            const cb = document.getElementById(id);
                                            if (cb) cb.checked = true;
                                        }
                                    });
                                }
                            } catch (e) {
                                // not JSON, try semicolon/comma separated
                                let days = raw.split(/[,;\s]+/).filter(Boolean);
                                days.forEach(day => {
                                    const id = String(day).toLowerCase();
                                    const checkbox = document.getElementById(id);
                                    if (checkbox) {
                                        checkbox.checked = true;
                                    } else {
                                        console.error(`Checkbox with id "${day}" not found`);
                                    }
                                });
                            }
                        }
                    } else {
                        console.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    // If no schedule is found yet (404), populate sensible defaults to keep UI usable
                    if (xhr && xhr.status === 404) {
                        document.getElementById('schedule_requester_enable').checked = true;
                        document.getElementById('schedule_requester_time_start').value = '08:00';
                        document.getElementById('schedule_requester_time_end').value = '17:00';

                        // Default business days Mon-Fri
                        const all = ['sun','mon','tue','wed','thu','fri','sat'];
                        all.forEach(id => {
                            const cb = document.getElementById(id);
                            if (cb) cb.checked = ['mon','tue','wed','thu','fri'].includes(id);
                        });
                    }
                }
            });
        }

        load_schedule_requester_form();
        load_transaction_limiter();
        
        // Load transaction limiter settings and populate the form
        function load_transaction_limiter() {
            if (!(endpointHost && endpointHost.length > 0)) { return; }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/transaction_limiter',
                type: 'GET',
                xhrFields: { withCredentials: true },
                success: function(response) {
                    console.log('Transaction limiter GET:', response);
                    if (response && response.status === 'success') {
                        const data = (response && response.data) || {};
                        // If a numeric value exists, populate the input
                        const limit = (typeof data.transaction_limit !== 'undefined' && data.transaction_limit !== null)
                            ? data.transaction_limit
                            : document.getElementById('transaction_limit').value;
                        document.getElementById('transaction_limit').value = limit;
                        // If the record exists, enable the checkbox. If not, leave as unchecked.
                        const enable = (typeof data.transaction_limit_enable !== 'undefined' && data.transaction_limit_enable !== null)
                            ? (parseInt(data.transaction_limit_enable, 10) !== 0)
                            : false;
                        document.getElementById('transaction_limit_enable').checked = enable;
                    } else {
                        console.error('Transaction limiter:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error (transaction_limiter):', status, error);
                    // If not found (404), set sensible defaults
                    if (xhr && xhr.status === 404) {
                        document.getElementById('transaction_limit').value = 10;
                        document.getElementById('transaction_limit_enable').checked = false;
                    }
                }
            });
        }

        // Submit handler for transaction limit form
    if (frmTransactionLimitForm) {
            frmTransactionLimitForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
        let transaction_limit_enable = formData.get('transaction_limit_enable') ? 1 : 0;
                let transaction_limit = formData.get('transaction_limit') || 0;

                console.log('Transaction Limit Form Data:', { transaction_limit_enable, transaction_limit });

                let notify = document.getElementById('notify-transaction-limit');
                let notify_message = document.getElementById('notify-transaction-limit-message');

                if (!(endpointHost && endpointHost.length > 0)) { return; }
                $.ajax({
                    url: endpointHost.replace(/\/$/, '') + '/api/transaction_limiter',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        transaction_limit: parseInt(transaction_limit, 10),
                        transaction_limit_enable: transaction_limit_enable
                    }),
                    xhrFields: { withCredentials: true },
                    success: function(response) {
                        notify.classList.remove('alert-success', 'alert-danger', 'alert-info');
                        if (response && response.status === 'success') {
                            notify.classList.add('alert-success');
                            notify_message.innerHTML = response.message;
                            notify.classList.remove('d-none');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1200);
                        } else {
                            notify.classList.add('alert-danger');
                            notify_message.innerHTML = response && response.message ? response.message : 'Failed to update';
                            notify.classList.remove('d-none');
                            setTimeout(() => {
                                notify.classList.add('d-none');
                            }, 2000);
                        }
                    },
                    error: function(xhr, status, error) {
                        notify.classList.add('alert-danger');
                        notify_message.innerHTML = 'Network or server error';
                        notify.classList.remove('d-none');
                        setTimeout(() => { notify.classList.add('d-none'); }, 2000);
                    }
                });
            });
        }
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
            
            if (!(endpointHost && endpointHost.length > 0)) { return; }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/schedule/requester_form',
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    enable: schedule_requester_enable,
                    time_start: schedule_requester_time_start,
                    time_end: schedule_requester_time_end,
                    repeat: 'daily',
                    everyday: days
                }),
                xhrFields: { withCredentials: true },
                success: function(response) {
                    console.log("Response:" + response);
                    notify_scheduler_requester.classList.remove('alert-success', 'alert-danger', 'alert-info');
                    if (response && response.status === 'success') {
                        notify_scheduler_requester.classList.add('alert-success');
                        notify_scheduler_requester_message.innerHTML = response.message;
                        notify_scheduler_requester.classList.remove('d-none');

                        setTimeout(() => {
                            // notify_scheduler_requester.classList.add('d-none');
                            window.location.reload();
                        }, 2000);
                    } else {
                        notify_scheduler_requester.classList.add('alert-danger');
                        notify_scheduler_requester_message.innerHTML = response && response.message ? response.message : 'Failed to update';
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