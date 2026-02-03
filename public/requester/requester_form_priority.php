<?php
include "./../base.php";
@include_once __DIR__ . '/../includes/config.php';
@include_once __DIR__ . '/../includes/api_client.php';

try {
    $api = get_api_client();
    $response = $api->get('/api/schedule/requester_form');
    $schedule = isset($response['data']) ? $response['data'] : null;
} catch (Exception $e) {
    error_log("API Error in requester_form_priority.php: " . $e->getMessage());
    $schedule = null;
}

$schedule_present = true;
$schedule_day_announcment = "Open now";
$time_start = 'Always Open';
$time_end = 'Always Open';
$time_now = date("h:i:s A");

if ($schedule) {
    $enabled = (int)($schedule['enable'] ?? 0);
    $db_time_start = $schedule['time_start'] ?? null;
    $db_time_end = $schedule['time_end'] ?? null;

    if ($db_time_start) {
        $time_start = date("g:i A", strtotime($db_time_start));
    } else {
        $time_start = 'N/A';
    }

    if ($db_time_end) {
        $time_end = date("g:i A", strtotime($db_time_end));
    } else {
        $time_end = 'N/A';
    }

    if ($enabled === 1) {
        $now_ts = time();
        $start_ts = $db_time_start ? strtotime(date('Y-m-d') . ' ' . $db_time_start) : null;
        $end_ts = $db_time_end ? strtotime(date('Y-m-d') . ' ' . $db_time_end) : null;

        $everyday_raw = $schedule['everyday'] ?? null;
        $allowed_today = true;

        if ($everyday_raw !== null && trim($everyday_raw) !== '') {
            $today_short = strtolower(date('D'));
            $parsed = json_decode($everyday_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                if (array_values($parsed) === $parsed) {
                    $allowed_today = in_array($today_short, array_map('strtolower', $parsed));
                } else {
                    $allowed_today = !empty($parsed[strtolower($today_short)]) || !empty($parsed[$today_short]);
                }
            } else {
                $tokens = preg_split('/[;,\s]+/', $everyday_raw, -1, PREG_SPLIT_NO_EMPTY);
                $tokens = array_map('strtolower', $tokens);
                $map = [
                    'sun' => ['sun','sunday'],
                    'mon' => ['mon','monday'],
                    'tue' => ['tue','tues','tuesday'],
                    'wed' => ['wed','wednesday'],
                    'thu' => ['thu','thur','thursday'],
                    'fri' => ['fri','friday'],
                    'sat' => ['sat','saturday']
                ];
                $allowed_today = false;
                foreach ($map as $short => $variants) {
                    if ($today_short === $short) {
                        foreach ($variants as $v) {
                            if (in_array($v, $tokens)) {
                                $allowed_today = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        if ($allowed_today && $start_ts !== null && $end_ts !== null && $now_ts >= $start_ts && $now_ts <= $end_ts) {
            $schedule_present = true;
            $schedule_day_announcment = "Open now";
        } else {
            $schedule_present = false;
            if (!$allowed_today) {
                $next = null;
                if (isset($everyday_raw) && trim((string)$everyday_raw) !== '') {
                    $week = ['sun','mon','tue','wed','thu','fri','sat'];
                    $allowed = [];
                    $parsed = json_decode($everyday_raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                        if (array_values($parsed) === $parsed) {
                            $allowed = array_map('strtolower', $parsed);
                        } else {
                            foreach ($parsed as $k => $v) {
                                if ($v) { $allowed[] = strtolower($k); }
                            }
                        }
                    } else {
                        $tokens = preg_split('/[;,\s]+/', $everyday_raw, -1, PREG_SPLIT_NO_EMPTY);
                        $allowed = array_map('strtolower', $tokens);
                    }
                    $todayIndex = (int)date('w');
                    for ($i = 1; $i <= 7; $i++) {
                        $idx = ($todayIndex + $i) % 7;
                        $short = $week[$idx];
                        if (in_array($short, $allowed, true)) {
                            $next = ucfirst($short);
                            break;
                        }
                    }
                }
                $schedule_day_announcment = $next
                    ? "Schedule is closed today — next open on {$next}"
                    : "Schedule is closed for today";
            } elseif ($start_ts !== null) {
                $schedule_day_announcment = "Come back later at " . date('g:i A', $start_ts);
            } else {
                $schedule_day_announcment = "Schedule is closed for today";
            }
        }
    } else {
        $schedule_present = true;
        $schedule_day_announcment = "Schedule disabled — form available all day.";
        $time_start = 'Always Open';
        $time_end = 'Always Open';
    }
} else {
    $schedule_present = true;
    $schedule_day_announcment = "Open now";
    $time_start = 'Always Open';
    $time_end = 'Always Open';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Priority Form | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="bg-[rgb(255,110,55)] px-8 py-6 text-center">
                <img src="./../asset/images/logo.png" alt="<?php echo $project_name?>" class="mx-auto w-16 mb-3">
                <h1 class="text-xl font-bold text-white">Priority Queue</h1>
                <p class="text-white/80 text-sm">For seniors, PWD, pregnant & with infant</p>
            </div>
            <div class="p-8">
                <?php if ($schedule_present) :?>
                <form method="post" id="frmUserForm">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-person-fill"></i></span>
                            <input type="text" id="name" name="name" placeholder="Enter your name" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="transaction-history-priority" class="block text-sm font-medium text-gray-700 mb-1">Priority Type</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-star-fill"></i></span>
                            <select name="transaction-history-priority" id="transaction-history-priority" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none bg-white appearance-none">
                                <option value="none">Select priority type</option>
                                <option value="pregnant">Pregnant</option>
                                <option value="elderly">Senior Citizen / Elderly</option>
                                <option value="disability">Person with Disability (PWD)</option>
                            </select>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><i class="bi bi-chevron-down"></i></span>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="transaction-history-payment" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-cash"></i></span>
                            <select name="transaction-history-payment" id="transaction-history-payment" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none bg-white appearance-none">
                                <option value="null">Select transaction type</option>
                                <option value="registrar">Registrar</option>
                                <option value="assessment">Assessment</option>
                            </select>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><i class="bi bi-chevron-down"></i></span>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3 bg-[rgb(255,110,55)] text-white font-semibold rounded-lg hover:bg-[rgb(230,60,20)] transition">Submit Request</button>
                    <p class="text-gray-500 text-sm text-center mt-4">
                        Not a priority customer? <a href="./requester_form.php" class="text-[rgb(255,110,55)] hover:underline font-medium">Use Standard Form</a>
                    </p>
                </form>
                <?php else :?>
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-clock text-red-500 text-2xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-800 mb-2">Schedule Closed</h5>
                    <p class="text-gray-500 mb-6"><?php echo $schedule_day_announcment?></p>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-left space-y-2">
                        <div class="flex justify-between"><span class="text-gray-500">Current Time:</span><span class="font-medium text-gray-800"><?php echo $time_now ?></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Opens at:</span><span class="font-medium text-gray-800"><?php echo $time_start ?></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Closes at:</span><span class="font-medium text-gray-800"><?php echo $time_end ?></span></div>
                    </div>
                </div>
                <?php endif ;?>
            </div>
        </div>
        <p class="text-center text-gray-500 text-sm mt-6">&copy; <?php echo project_year()?> <?php echo $project_name?></p>
    </div>
<?php after_js()?>
<script>
function msg_clear(f){f.find('.msg').remove();}
function msg_info(f,m){msg_clear(f);f.prepend('<div class="msg mb-4 p-3 bg-blue-100 text-blue-800 rounded-lg text-sm">'+m+'</div>');}
function msg_ok(f,m){msg_clear(f);f.prepend('<div class="msg mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">'+m+'</div>');}
function msg_err(f,m){msg_clear(f);f.prepend('<div class="msg mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">'+m+'</div>');}
function msg_warn(f,m){msg_clear(f);f.prepend('<div class="msg mb-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-sm">'+m+'</div>');}

function sumbitUserForm(user) {
    var form = $('#frmUserForm');
    msg_info(form, 'Processing...');
    if (!(endpointHost && endpointHost.length > 0)) {
        msg_err(form, 'Service is unavailable. Please try again later.');
        return;
    }
    var $btn = $('#frmUserForm button[type=submit]');
    var _orig = $btn.html();
    $.ajax({
        url: endpointHost.replace(/\/$/, '') + '/api/requester',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(user),
        xhrFields: { withCredentials: true },
        beforeSend: function() {
            $btn.prop('disabled', true).addClass('opacity-60').html('<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span> Processing...');
        },
        complete: function() {
            $btn.prop('disabled', false).removeClass('opacity-60').html(_orig);
        },
        success: function(response) {
            if (response && response.status === 'success') {
                msg_ok(form, response.message || 'Success');
                localStorage.setItem('requester_token', response.token_number);
                var requester_token = localStorage.getItem('requester_token');
                setTimeout(function() {
                    window.location.href = "./requester_number_priority.php?requester_token=" + requester_token;
                }, 800);
            } else {
                msg_err(form, (response && response.message) || 'Submission failed');
            }
        },
        error: function(xhr) {
            if (xhr && xhr.status === 409) {
                let payload = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                const infoMsg = payload && payload.message ? payload.message : 'You already have an active queue today.';
                if (payload && payload.queue_number) {
                    msg_warn(form, infoMsg + ` (Queue #${payload.queue_number})`);
                } else {
                    msg_warn(form, infoMsg);
                }
                if (payload && payload.token_number) {
                    localStorage.setItem('requester_token', payload.token_number);
                    setTimeout(function() {
                        window.location.href = "./requester_number_priority.php?requester_token=" + payload.token_number;
                    }, 1200);
                }
                return;
            }
            if (xhr && (xhr.status === 423 || xhr.status === 403)) {
                let payload = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                const infoMsg = payload && payload.message ? payload.message : 'Priority requester form is currently unavailable.';
                msg_warn(form, infoMsg);
                return;
            }
            msg_err(form, 'Network error. Please try again.');
        }
    });
}

var payment = null;
var priority = null;

$('#transaction-history-priority').change(function() {
    priority = $(this).val();
    if (priority == 'none') priority = null;
});

$('#transaction-history-payment').change(function() {
    payment = $(this).val();
    if (payment == 'null') payment = null;
});

$('#frmUserForm').submit(function(e) {
    e.preventDefault();
    if (payment === null) {
        msg_err($('#frmUserForm'), 'Please select transaction type');
        return;
    }
    var user = {
        name: $('#name').val(),
        email: $('#email').val(),
        payment: payment,
        priority: priority,
        website: `${realHost}/public/requester/requester_number_priority.php`
    };
    sumbitUserForm(user);
});
</script>
</body>
</html>
