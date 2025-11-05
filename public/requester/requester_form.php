<?php
include "./../base.php";
@include_once __DIR__ . '/../includes/config.php';
@include_once __DIR__ . '/../includes/api_client.php';

try {
    $api = get_api_client();
    $response = $api->get('/api/schedule/requester_form');
    $schedule = isset($response['data']) ? $response['data'] : null;
} catch (Exception $e) {
    error_log("API Error in requester_form.php: " . $e->getMessage());
    $schedule = null;
}

// Defaults
$schedule_present = false;
$schedule_day_announcment = "Schedule is closed";
$time_now = date("h:i:s A");
$time_start = 'N/A';
$time_end = 'N/A';

if ($schedule) {
    $enabled = (int)($schedule['enable'] ?? 0);
    $db_time_start = $schedule['time_start'] ?? null;
    $db_time_end = $schedule['time_end'] ?? null;

    // compute timestamps for today
    $now_ts = time();
    $start_ts = $db_time_start ? strtotime(date('Y-m-d') . ' ' . $db_time_start) : null;
    $end_ts = $db_time_end ? strtotime(date('Y-m-d') . ' ' . $db_time_end) : null;

    // handle `everyday` formats: null (means every day), semicolon list, or JSON
    $everyday_raw = $schedule['everyday'] ?? null;
    $allowed_today = true; // default allow

    if ($everyday_raw !== null && trim($everyday_raw) !== '') {
        $today_short = strtolower(date('D')); // e.g. Sun, Mon -> sun

        // try JSON
        $parsed = json_decode($everyday_raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
            // Support formats: {"sun":true} or ["sun","mon"]
            if (array_values($parsed) === $parsed) {
                // numeric array
                $allowed_today = in_array($today_short, array_map('strtolower', $parsed));
            } else {
                $allowed_today = !empty($parsed[strtolower($today_short)]) || !empty($parsed[$today_short]);
            }
        } else {
            // assume semicolon or comma separated tokens
            $tokens = preg_split('/[;,\s]+/', $everyday_raw, -1, PREG_SPLIT_NO_EMPTY);
            $tokens = array_map('strtolower', $tokens);
            // accept common forms: mon, monday, Mon
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
                if (in_array($today_short, [$short, strtoupper($short)])) {
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

    if ($enabled === 1 && $allowed_today && $start_ts !== null && $end_ts !== null && $now_ts >= $start_ts && $now_ts <= $end_ts) {
        $schedule_present = true;
        $schedule_day_announcment = "Open now";
    } else {
        if ($enabled !== 1) {
            $schedule_day_announcment = "Schedule is disabled";
        } elseif (!$allowed_today) {
            $next = null;
            if ($everyday_raw !== null && trim($everyday_raw) !== '') {
                $week = ['sun','mon','tue','wed','thu','fri','sat'];
                $allowed = [];
                $parsed = json_decode($everyday_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    if (array_values($parsed) === $parsed) {
                        $allowed = array_map('strtolower', $parsed);
                    } else {
                        foreach ($parsed as $k => $v) {
                            if ($v) $allowed[] = strtolower($k);
                        }
                    }
                } else {
                    $tokens = preg_split('/[;,\s]+/', $everyday_raw, -1, PREG_SPLIT_NO_EMPTY);
                    $allowed = array_map('strtolower', $tokens);
                }
                $todayIndex = (int)date('w'); // 0 (Sun) - 6
                for ($i=1;$i<=7;$i++) {
                    $idx = ($todayIndex + $i) % 7;
                    $short = $week[$idx];
                    if (in_array($short, $allowed)) {
                        $next = ucfirst($short);
                        break;
                    }
                }
            }
            if ($next) {
                $schedule_day_announcment = "Schedule is closed today — next open on {$next}";
            } else {
                $schedule_day_announcment = "Schedule is closed for today";
            }
        } else {
            // time window
            if ($start_ts !== null) {
                $schedule_day_announcment = "Come back later at " . date('g:i A', $start_ts);
            } else {
                $schedule_day_announcment = "Schedule is closed for today";
            }
        }
    }

    // format times for display
    $time_start = $db_time_start ? date("g:i A", strtotime($db_time_start)) : 'N/A';
    $time_end = $db_time_end ? date("g:i A", strtotime($db_time_end)) : 'N/A';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>QR Form | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 100px;min-height: 600px;">
        <div class="card shadow-sm p-4" style="max-width: 400px;width: 100%;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <?php if ($schedule_present) :?>
            <h4 class="text-center fw-bold">QR FORM</h4>
            <p class="text-center text-muted">PLEASE FILL UP</p>
            <form method="post" id="frmUserForm">
                <div class="input-group mb-2">
                    <div class="input-group-text"><i class="bi bi-person-fill"></i></div>
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Your Name" required>
                        <label for="name" class="form-label">Name</label>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <div class="input-group-text"><i class="bi bi-envelope-fill"></i></div>
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email Address" required>
                        <label for="email" class="form-label">Email</label>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <div class="input-group-text"><i class="bi bi-sort-up-alt"></i></div>
                    <div class="form-floating">
                        <select class="form-select"  name="transaction-history-student" id="transaction-history-student">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                        <label for="transaction-history-pwd" class="form-label">Are you a student in this campus?</label>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-text"><i class="bi bi-cash"></i></div>
                    <div class="form-floating">
                        <select class="form-select"  name="transaction-history-payment" id="transaction-history-payment">
                            <option value="null">--</option>
                            <option value="registrar">Registrar</option>
                            <option value="assessment">Assessment</option>
                        </select>
                        <label for="email" class="form-label">Payment</label>
                    </div>
                </div>

                <p class="text-muted small mb-2">
                    Need priority service? If you're a senior citizen, PWD, pregnant, or with an infant, please use the
                    <a href="./requester_form_priority.php" class="text-decoration-none fw-semibold">Priority Form</a>.
                </p>

                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
            <?php else :?>
                <div class="text-center fw-bold">
                    <h5 class="text-danger">SCHEDULE CLOSED</h5>
                    <p class="text-muted"><?php echo $schedule_day_announcment?></p>
                </div>

                <div class="w-100">
                    <p>
                        Current Time: <span class="text-danger fw-bold"><?php echo $time_now ?></span><br>
                    </p>
                    <p>
                        Schedule Start: <span class="text-danger fw-bold"><?php echo $time_start ?></span><br>
                    </p>
                    <p>
                        Schedule End: <span class="text-danger fw-bold"><?php echo $time_end ?></span><br>
                    </p>
                </div>
            <?php endif ;?>
        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script>
    var endpointHost = "<?php echo isset($endpoint_server) ? rtrim($endpoint_server, '/') : ''; ?>";
        function sumbitUserForm(user) {
            var form = $('#frmUserForm');
            message_info(form, 'Processing...');
            // ONLY use Flask API via endpointHost; no PHP fallback
            if (!(endpointHost && endpointHost.length > 0)) {
                message_error(form, 'Service is unavailable. Please try again later.');
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
                    $btn.prop('disabled', true);
                    $btn.addClass('bg-warning text-dark');
                    $btn.html('<span class="spinner-border spinner-border-sm text-white" role="status" aria-hidden="true"></span> Processing...');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $btn.removeClass('bg-warning text-dark');
                    $btn.html(_orig);
                },
                success: function(response) {
                    if (response && response.status === 'success') {
                        message_success(form, response.message || 'Success');
                        localStorage.setItem('requester_token', response.token_number);
                        var requester_token = localStorage.getItem('requester_token');
                        setTimeout(function() {
                            window.location.href = "./requester_number.php?requester_token=" + requester_token;
                        }, 800);
                    } else {
                        message_error(form, (response && response.message) || 'Submission failed');
                    }
                },
                error: function() {
                    message_error(form, 'Network error. Please try again.');
                }
            });
        }

        // Note: PHP fallback removed intentionally for local-only Flask integration

        var payment = null;
        var student = null;

        $('#transaction-history-student').change(function() {
            student = $(this).val();
            if (student == '0') {
                student = null;
            }
        });

        $('#transaction-history-payment').change(function() {
            payment = $(this).val();
            if (payment == 'null') {
                payment = null;
            }
        });

        $('#frmUserForm').submit(function(e) {
            e.preventDefault();

            if (payment === null) {
                message_error($('#frmUserForm'), 'Please select payment type');
                return;
            }
            var user = {
                name: $('#name').val(),
                email: $('#email').val(),
                payment: payment,
                is_student: student,
                priority: 'none',
                website: `${realHost}/public/requester/requester_number.php`
            };
            console.log(user);
            sumbitUserForm(user);
        });

    </script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
                website: `${realHost}/public/requester/requester_number.php`
            };
            console.log(user);
            sumbitUserForm(user);
        });
    </script>

</body>
<?php include_once "./../includes/footer.php"; ?>
</html>realHost}/public/requester/requester_number.php`
            };
            console.log(user);
            sumbitUserForm(user);
        });

    </script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
