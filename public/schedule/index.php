<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
if (!isset($token) || !$token) $token = null;
$id = isset($token->id) ? $token->id : null;
$username = isset($token->username) ? $token->username : null;
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
    <script>window.phpToken = <?php echo isset($_COOKIE['token']) ? "'" . addslashes($_COOKIE['token']) . "'" : 'null'; ?>;</script>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>
    <div class="min-h-screen pt-24 pb-32 flex justify-center px-4">
        <div class="w-full max-w-2xl">
            <div id="logOutNotify" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg text-left">
                <span><?php echo $username ?> has logged out successfully</span>
            </div>
            <div class="bg-white shadow rounded-full px-6 py-2 mb-4">
                <nav class="text-sm">
                    <a href="/public/admin" class="text-gray-700 hover:text-[rgb(255,110,55)]">Dashboard</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-500">Settings</span>
                </nav>
            </div>

            <!-- Transaction Limit Card -->
            <div class="bg-white shadow rounded-2xl mb-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 font-medium">Transaction: Limit Rate/day</div>
                <form id="frmTransactionLimitForm" class="p-6">
                    <div class="mb-4 p-4 bg-blue-50 text-blue-800 rounded-lg flex items-start gap-2">
                        <i class="bi bi-info-circle-fill mt-0.5"></i>
                        <span>This feature will set limit transaction requests per day.</span>
                    </div>
                    <div id="notify-transaction-limit" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg flex items-start gap-2">
                        <i class="bi bi-info-circle-fill mt-0.5"></i>
                        <span id="notify-transaction-limit-message">Transaction limit set successfully.</span>
                    </div>
                    <label class="flex items-center gap-3 mb-6 cursor-pointer">
                        <input type="checkbox" name="transaction_limit_enable" id="transaction_limit_enable" value="1" class="w-5 h-5 rounded border-gray-300 text-[rgb(255,110,55)] focus:ring-[rgb(255,110,55)]">
                        <span>Enable</span>
                    </label>
                    <div class="mb-6">
                        <label for="transaction_limit" class="block text-sm text-gray-600 mb-2">Transaction Limit:</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-hourglass-top"></i></span>
                            <input type="number" name="transaction_limit" id="transaction_limit" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent" placeholder="Transaction Limit" value="10">
                        </div>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-[rgb(255,110,55)] text-white rounded-lg hover:bg-[rgb(230,60,20)] transition-colors">
                        <i class="bi bi-check-lg mr-1"></i> Save
                    </button>
                </form>
            </div>

            <!-- Schedule Card -->
            <div class="bg-white shadow rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 font-medium">Schedule: Time Range</div>
                <form id="frmScheduleRequesterForm" class="p-6">
                    <div class="mb-4 p-4 bg-blue-50 text-blue-800 rounded-lg flex items-start gap-2">
                        <i class="bi bi-info-circle-fill mt-0.5"></i>
                        <span>This schedule is used for what time will open the requester form.</span>
                    </div>
                    <div id="notify-scheduler-requester" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg flex items-start gap-2">
                        <i class="bi bi-info-circle-fill mt-0.5"></i>
                        <span id="notify-scheduler-requester-message">Schedule set successfully.</span>
                    </div>
                    <label class="flex items-center gap-3 mb-6 cursor-pointer">
                        <input type="checkbox" name="schedule_requester_enable" id="schedule_requester_enable" value="1" class="w-5 h-5 rounded border-gray-300 text-[rgb(255,110,55)] focus:ring-[rgb(255,110,55)]">
                        <span>Enable</span>
                    </label>
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-2">Time Range</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-hourglass-top"></i></span>
                                <input type="time" name="schedule_requester_time_start" id="schedule_requester_time_start" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent" value="08:00">
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-hourglass-bottom"></i></span>
                                <input type="time" name="schedule_requester_time_end" id="schedule_requester_time_end" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent" value="17:00">
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-2">Days of the Week:</label>
                        <div class="border border-[rgb(255,110,55)] rounded-lg p-4">
                            <div class="flex flex-wrap justify-between gap-2">
                                <?php $days = ['sun'=>'Sun','mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat'];
                                foreach($days as $val=>$label): ?>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="days[]" id="<?php echo $val?>" value="<?php echo $val?>" class="w-4 h-4 rounded border-gray-300 text-[rgb(255,110,55)] focus:ring-[rgb(255,110,55)]">
                                    <span class="text-sm"><?php echo $label?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-[rgb(255,110,55)] text-white rounded-lg hover:bg-[rgb(230,60,20)] transition-colors">
                        <i class="bi bi-check-lg mr-1"></i> Save
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php after_js() ?>
    <?php include_once "./../includes/footer.php"; ?>
    <script src="./../asset/js/message.js"></script>
    <script>
        const endpointHost = window.endpointHost;
        const authToken = (typeof window.phpToken === 'string' && window.phpToken.length > 0) ? window.phpToken : '';

        function attachAuthHeader(xhr) {
            if (!authToken) return;
            try { xhr.setRequestHeader('Authorization', 'Bearer ' + authToken); } catch (e) {}
        }

        function handleAuthError(xhr, notifyCtx) {
            if (!xhr) return false;
            if (xhr.status === 401) { window.location.href = '/public/auth/login.php'; return true; }
            if (xhr.status === 403) {
                if (notifyCtx && notifyCtx.element && notifyCtx.messageEl) {
                    notifyCtx.element.classList.remove('bg-green-100','text-green-800');
                    notifyCtx.element.classList.add('bg-red-100','text-red-800');
                    notifyCtx.messageEl.innerHTML = 'Administrator access required.';
                    notifyCtx.element.classList.remove('hidden');
                } else { alert('Administrator access required.'); }
                return true;
            }
            return false;
        }

        function load_schedule_requester_form() {
            if (!(endpointHost && endpointHost.length > 0)) return;
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/schedule/requester_form',
                type: 'GET',
                beforeSend: attachAuthHeader,
                xhrFields: { withCredentials: true },
                success: function(response) {
                    if (response.status === 'success') {
                        let schedule = response.data;
                        document.getElementById('schedule_requester_enable').checked = schedule.enable == 1;
                        document.getElementById('schedule_requester_time_start').value = schedule.time_start;
                        document.getElementById('schedule_requester_time_end').value = schedule.time_end;
                        if (!schedule.everyday || schedule.everyday.trim() === '') {
                            ['sun','mon','tue','wed','thu','fri','sat'].forEach(id => {
                                const cb = document.getElementById(id);
                                if (cb) cb.checked = true;
                            });
                        } else {
                            let raw = schedule.everyday;
                            try {
                                const parsed = JSON.parse(raw);
                                if (Array.isArray(parsed)) {
                                    parsed.forEach(d => { const cb = document.getElementById(String(d).toLowerCase()); if (cb) cb.checked = true; });
                                } else if (parsed && typeof parsed === 'object') {
                                    Object.keys(parsed).forEach(k => { if (parsed[k]) { const cb = document.getElementById(String(k).toLowerCase()); if (cb) cb.checked = true; } });
                                }
                            } catch (e) {
                                raw.split(/[,;\s]+/).filter(Boolean).forEach(day => { const cb = document.getElementById(String(day).toLowerCase()); if (cb) cb.checked = true; });
                            }
                        }
                    }
                },
                error: function(xhr) {
                    if (handleAuthError(xhr)) return;
                    if (xhr && xhr.status === 404) {
                        document.getElementById('schedule_requester_enable').checked = true;
                        document.getElementById('schedule_requester_time_start').value = '08:00';
                        document.getElementById('schedule_requester_time_end').value = '17:00';
                        ['mon','tue','wed','thu','fri'].forEach(id => { const cb = document.getElementById(id); if (cb) cb.checked = true; });
                    }
                }
            });
        }

        function load_transaction_limiter() {
            if (!(endpointHost && endpointHost.length > 0)) return;
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/transaction_limiter',
                type: 'GET',
                beforeSend: attachAuthHeader,
                xhrFields: { withCredentials: true },
                success: function(response) {
                    if (response && response.status === 'success') {
                        const data = response.data || {};
                        const limit = (typeof data.transaction_limit !== 'undefined' && data.transaction_limit !== null) ? data.transaction_limit : document.getElementById('transaction_limit').value;
                        document.getElementById('transaction_limit').value = limit;
                        const enable = (typeof data.transaction_limit_enable !== 'undefined' && data.transaction_limit_enable !== null) ? (parseInt(data.transaction_limit_enable, 10) !== 0) : false;
                        document.getElementById('transaction_limit_enable').checked = enable;
                    }
                },
                error: function(xhr) {
                    if (handleAuthError(xhr)) return;
                    if (xhr && xhr.status === 404) {
                        document.getElementById('transaction_limit').value = 10;
                        document.getElementById('transaction_limit_enable').checked = false;
                    }
                }
            });
        }

        load_schedule_requester_form();
        load_transaction_limiter();

        document.getElementById('frmTransactionLimitForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let transaction_limit_enable = formData.get('transaction_limit_enable') ? 1 : 0;
            let transaction_limit = formData.get('transaction_limit') || 0;
            let notify = document.getElementById('notify-transaction-limit');
            let notify_message = document.getElementById('notify-transaction-limit-message');
            if (!(endpointHost && endpointHost.length > 0)) return;
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/transaction_limiter',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ transaction_limit: parseInt(transaction_limit, 10), transaction_limit_enable: transaction_limit_enable }),
                beforeSend: attachAuthHeader,
                xhrFields: { withCredentials: true },
                success: function(response) {
                    notify.classList.remove('bg-green-100','text-green-800','bg-red-100','text-red-800');
                    if (response && response.status === 'success') {
                        notify.classList.add('bg-green-100','text-green-800');
                        notify_message.innerHTML = response.message;
                        notify.classList.remove('hidden');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        notify.classList.add('bg-red-100','text-red-800');
                        notify_message.innerHTML = response?.message || 'Failed to update';
                        notify.classList.remove('hidden');
                        setTimeout(() => notify.classList.add('hidden'), 2000);
                    }
                },
                error: function(xhr) {
                    if (handleAuthError(xhr, { element: notify, messageEl: notify_message })) return;
                    notify.classList.remove('bg-green-100','text-green-800');
                    notify.classList.add('bg-red-100','text-red-800');
                    notify_message.innerHTML = 'Network or server error';
                    notify.classList.remove('hidden');
                    setTimeout(() => notify.classList.add('hidden'), 2000);
                }
            });
        });

        document.getElementById('frmScheduleRequesterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let schedule_requester_enable = formData.get('schedule_requester_enable') ? formData.get('schedule_requester_enable') : 0;
            let schedule_requester_time_start = formData.get('schedule_requester_time_start');
            let schedule_requester_time_end = formData.get('schedule_requester_time_end');
            let days = formData.getAll('days[]').join(';');
            let notify = document.getElementById('notify-scheduler-requester');
            let notify_message = document.getElementById('notify-scheduler-requester-message');
            if (!(endpointHost && endpointHost.length > 0)) return;
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/schedule/requester_form',
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ enable: schedule_requester_enable, time_start: schedule_requester_time_start, time_end: schedule_requester_time_end, repeat: 'daily', everyday: days }),
                beforeSend: attachAuthHeader,
                xhrFields: { withCredentials: true },
                success: function(response) {
                    notify.classList.remove('bg-green-100','text-green-800','bg-red-100','text-red-800');
                    if (response && response.status === 'success') {
                        notify.classList.add('bg-green-100','text-green-800');
                        notify_message.innerHTML = response.message;
                        notify.classList.remove('hidden');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        notify.classList.add('bg-red-100','text-red-800');
                        notify_message.innerHTML = response?.message || 'Failed to update';
                        notify.classList.remove('hidden');
                        setTimeout(() => notify.classList.add('hidden'), 2000);
                    }
                },
                error: function(xhr) {
                    if (handleAuthError(xhr, { element: notify, messageEl: notify_message })) return;
                    notify.classList.remove('bg-green-100','text-green-800');
                    notify.classList.add('bg-red-100','text-red-800');
                    notify_message.innerHTML = 'Network or server error';
                    notify.classList.remove('hidden');
                    setTimeout(() => notify.classList.add('hidden'), 2000);
                }
            });
        });
    </script>
</body>
</html>
