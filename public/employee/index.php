<?php

include_once __DIR__ . "/../base.php";
restrictEmployeeMode();

// Ensure API endpoint host is available
include_once __DIR__ . "/../includes/config.php";

// Use normalized token payload and guard against missing fields
$payload = getDecodedTokenPayload();
$tok = null;
if (is_array($payload)) {
    $tok = json_decode(json_encode($payload)); // stdClass for object-style access
} elseif (is_object($payload)) {
    $tok = $payload;
}

$id = isset($tok->id) ? (int)$tok->id : 0;
$username = isset($tok->username) ? $tok->username : '';
$role_type = isset($tok->role_type) ? $tok->role_type : (isset($tok->role) ? $tok->role : '');
$email = isset($tok->email) ? $tok->email : '';
$counterNumber = isset($tok->counterNumber) ? (int)$tok->counterNumber : 0;
$priority = isset($tok->priority) ? $tok->priority : 'N';
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
                            <form class="" action="" id="frmCutOff_trigger">
                                <div class="alert alert-info text-start" id="cutOff_trigger_notification">
                                    <span id="cutOff_trigger_message">1 queue remain.</span>
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
                                <th scope="col">Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows are rendered by JS -->
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div>
                            <small class="text-muted" id="transactions-count">&nbsp;</small>
                        </div>
                        <div id="transactions-pagination" class="btn-group" role="group" aria-label="Transactions pagination">
                            <!-- Pagination controls injected by JS -->
                        </div>
                    </div>
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

        (function() {
            const endpointHost = "<?php echo isset($endpoint_server) ? rtrim($endpoint_server, '/') : '';?>";
            window.API_BASE = endpointHost + '/api';
        })();

        // Attempt to keep the displayed COUNTER in sync with the JWT without a full reload.
        // Strategy:
        // 1. Try to read the `token` cookie in JS and decode the JWT payload.
        // 2. If the cookie is HttpOnly (not readable), fall back to calling the
        //    local PHP helper `public/includes/system_auth.php?action=status` which
        //    returns the decoded payload server-side.
        // 3. Only update the DOM when the token (or decoded payload) changes.
        (function() {
            let lastTokenValue = null;

            function tryGetTokenFromCookie() {
                try {
                    const match = document.cookie.match('(?:^|; )token=([^;]*)');
                    return match ? decodeURIComponent(match[1]) : null;
                } catch (e) {
                    return null;
                }
            }

            function base64UrlDecode(str) {
                try {
                    str = str.replace(/-/g, '+').replace(/_/g, '/');
                    // Pad base64 string
                    while (str.length % 4) str += '=';
                    // atob returns a binary string; decodeURIComponent/escape handles UTF-8
                    const decoded = atob(str);
                    try {
                        // Percent-encode UTF-8 bytes then decode
                        return decodeURIComponent(Array.prototype.map.call(decoded, function(c) {
                            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                        }).join(''));
                    } catch (e) {
                        return decoded;
                    }
                } catch (e) {
                    return null;
                }
            }

            function decodeJwt(token) {
                if (!token) return null;
                const parts = token.split('.');
                if (parts.length < 2) return null;
                const payload = parts[1];
                const json = base64UrlDecode(payload);
                if (!json) return null;
                try {
                    return JSON.parse(json);
                } catch (e) {
                    return null;
                }
            }

            function updateCounterFromPayload(payload) {
                if (!payload) return;
                const el = document.getElementById('employee-counter-number');
                if (!el) return;
                // Accept multiple common claim names (legacy compat)
                const counter = (payload.counterNumber !== undefined && payload.counterNumber !== null)
                    ? payload.counterNumber
                    : (payload.counter_number !== undefined && payload.counter_number !== null)
                        ? payload.counter_number
                        : (payload.counter !== undefined && payload.counter !== null)
                            ? payload.counter
                            : null;

                if (counter !== null && counter !== undefined) {
                    // Only update when value differs to avoid unnecessary DOM churn
                    const asInt = parseInt(counter, 10);
                    if (!Number.isNaN(asInt) && el.innerText != asInt) {
                        el.innerText = asInt;
                    }
                }
            }

            async function refreshCounterFromToken() {
                // First try local cookie (may be HttpOnly)
                const token = tryGetTokenFromCookie();
                if (token) {
                    if (token === lastTokenValue) return; // unchanged
                    lastTokenValue = token;
                    const payload = decodeJwt(token);
                    if (payload) {
                        updateCounterFromPayload(payload);
                        return;
                    }
                }

                // Fallback: ask PHP to decode token server-side (same-origin)
                try {
                    const resp = await fetch('/public/includes/system_auth.php?action=status', { credentials: 'same-origin' });
                    if (resp.ok) {
                        const j = await resp.json();
                        if (j && j.decoded) {
                            // stringify to compare for changes
                            const sig = JSON.stringify(j.decoded);
                            if (sig === lastTokenValue) return;
                            lastTokenValue = sig;
                            updateCounterFromPayload(j.decoded);
                        }
                    }
                } catch (e) {
                    // ignore network errors — best-effort only
                }
            }

            // Kick off initial refresh and poll (lightweight) so UI updates when token changes.
            refreshCounterFromToken();
            setInterval(refreshCounterFromToken, 5000);
        })();

        // Ensure browser sends cookies (token) to Flask across origins
        if (window.jQuery && $.ajaxSetup) {
            $.ajaxSetup({
                xhrFields: { withCredentials: true },
                crossDomain: true
            });
        }
    
        function queue_remain_set(queue_remain) {
            $.ajax({
                url: window.API_BASE + "/cashier",
                type: 'PATCH',
                contentType: 'application/json',
                data: JSON.stringify({
                    counter_number : <?php echo $counterNumber?>,
                    queue_remain : queue_remain
                }),
                success: function (response) {
                    cutOff_trigger_notification.classList.remove('d-none');
                    notify_priority = true;
                    if (notify_priority && queue_remain != null) {
                        cutOff_trigger_message.innerText = "Queue remaining set to " + queue_remain;
                    } else if (notify_priority && queue_remain == null) {
                        cutOff_trigger_message.innerText = "Auto-cut off is disabled";
                    }
                    setTimeout(() => {
                        notify_priority = false;
                        cutOff_trigger_notification.classList.add('d-none');
                    },notify_priority_timer * 1000);
                    // Refresh current queue_remain after change
                    queue_remain_get();
                    console.log(response);
                }
            });
        }

        let cut_off_select = document.getElementById('cut_off_select');
        cut_off_select.addEventListener('change', function (e) {
            console.log(this.value);
            fetchCutOff();
            if (this.value === "null") {
                // Disable auto-cutoff
                queue_remain_set(null);
            } else {
                // Set numeric cutoff value
                const v = parseInt(this.value, 10);
                queue_remain_set(Number.isNaN(v) ? null : v);
            }
        });

        function queue_remain_get() {
            let param = new URLSearchParams({
                counter_queue_remain: true,
                counter_number: <?php echo htmlspecialchars($counterNumber); ?>
            });
            $.ajax({
                url: window.API_BASE + "/cashier?" + param.toString(),
                method: "GET",
                success: function(response) {
                    console.log("Response received:", response); // Log the response
                    queue_remain = response.queue_remain;
                    if (response.status === 'success') {
                        if (response.queue_remain != null) {
                            if (cutOff_trigger_notification.classList.contains('d-none')) {
                                cutOff_trigger_notification.classList.remove('d-none');
                            }
                            // Update the inner message span instead of replacing the container
                            if (typeof cutOff_trigger_message !== 'undefined' && cutOff_trigger_message) {
                                cutOff_trigger_message.innerText = response.queue_remain + " queue remain.";
                            } else {
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
            $.ajax({
                url: window.API_BASE + '/cashier',
                type: 'GET',
                cache: false,
                success: function(response) {
                    console.log("RECV:", response);
                    let queue_number = document.getElementById('queue-number');
                    if (
                        response.status === 'success' &&
                        response.data &&
                        typeof response.data.queue_number !== 'undefined' &&
                        response.data.queue_number !== null
                    ) {
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
                url: window.API_BASE + '/cashier',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    method: 'cashier-success',
                    idemployee: <?php echo $id?>,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        // Update remaining queue count immediately
                        queue_remain_get();
                        // Also refresh the current transaction and student list
                        fetchTransaction();
                        fetchStudentTransaction();
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
                url: window.API_BASE + '/cashier',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    method: 'cashier-missed',
                    idemployee: <?php echo $id?>,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        // Update remaining queue count immediately
                        queue_remain_get();
                        fetchTransaction();
                        fetchStudentTransaction();
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
        // Client-side pagination state
        let studentTransactions = [];
        let studentTxnPage = 1;
        const studentPageSize = 10; // rows per page

        function renderStudentTransactionsPage() {
            // Clear tbody
            const tbody = table_transactions_student.querySelector('tbody');
            while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

            if (!Array.isArray(studentTransactions) || studentTransactions.length === 0) {
                document.getElementById('transactions-count').innerText = '';
                updateTransactionsPagination();
                return;
            }

            const total = studentTransactions.length;
            const totalPages = Math.max(1, Math.ceil(total / studentPageSize));
            if (studentTxnPage > totalPages) studentTxnPage = totalPages;
            const start = (studentTxnPage - 1) * studentPageSize;
            const end = Math.min(start + studentPageSize, total);

            for (let i = start; i < end; i++) {
                const transaction = studentTransactions[i];
                const row = document.createElement('tr');
                const cell1 = document.createElement('td');
                const cell2 = document.createElement('td');
                const cell3 = document.createElement('td');
                cell1.innerText = transaction.queue_number || '';
                cell2.innerText = transaction.email || '';
                cell3.innerText = transaction.payment || '';
                row.appendChild(cell1);
                row.appendChild(cell2);
                row.appendChild(cell3);
                tbody.appendChild(row);
            }

            document.getElementById('transactions-count').innerText = `Showing ${start + 1}–${end} of ${total}`;
            updateTransactionsPagination(totalPages);
        }

        function updateTransactionsPagination(totalPages) {
            const container = document.getElementById('transactions-pagination');
            // If totalPages is not provided, compute from current data
            if (typeof totalPages === 'undefined') {
                totalPages = Math.max(1, Math.ceil((studentTransactions.length || 0) / studentPageSize));
            }
            container.innerHTML = '';

            const prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'btn btn-sm btn-outline-primary';
            prev.innerText = 'Prev';
            prev.disabled = studentTxnPage <= 1;
            prev.onclick = function() {
                if (studentTxnPage > 1) {
                    studentTxnPage--;
                    renderStudentTransactionsPage();
                }
            };

            const next = document.createElement('button');
            next.type = 'button';
            next.className = 'btn btn-sm btn-outline-primary';
            next.innerText = 'Next';
            next.disabled = studentTxnPage >= totalPages;
            next.onclick = function() {
                if (studentTxnPage < totalPages) {
                    studentTxnPage++;
                    renderStudentTransactionsPage();
                }
            };

            const pageInfo = document.createElement('span');
            pageInfo.className = 'mx-2 align-self-center';
            pageInfo.innerText = `Page ${studentTxnPage} / ${totalPages}`;

            container.appendChild(prev);
            container.appendChild(pageInfo);
            container.appendChild(next);
        }

        function fetchStudentTransaction() {
            $.ajax({
                url: window.API_BASE + '/dashboard/cashier',
                type: 'GET',
                cache: false,
                success: function(response) {
                    let transactions = response.data || [];
                    if (!Array.isArray(transactions)) transactions = [];
                    studentTransactions = transactions;
                    studentTxnPage = 1; // reset to first page on refresh
                    renderStudentTransactionsPage();
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
                url: window.API_BASE + '/cashier?' + params,
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
                    url: window.API_BASE + '/cashier',
                    type: 'POST',
                    contentType: 'application/json',
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
                    url: window.API_BASE + '/cashier',
                    type: 'POST',
                    contentType: 'application/json',
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
