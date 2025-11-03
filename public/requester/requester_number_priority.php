<?php
include './../base.php';
@include_once __DIR__ . '/../includes/config.php';
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
    <div class="container d-flex justify-content-center align-items-center" style="margin-top:100px;margin-bottom:100px;flex-direction:column">
        <div class="container d-flex justify-content-center align-items-center" style="flex-direction:column">
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
                <a class="btn btn-primary text-white fw-bold" id="btnCancelRequestModal" data-bs-toggle="modal" data-bs-target="#requestCancelModal">Cancel Request</a>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="btnCancelRequest">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php after_js()?>
    <script>
    var endpointHost = "<?php echo isset($endpoint_server) ? rtrim($endpoint_server, '/') : ''; ?>";
        let this_requester_status_alert = document.getElementById("this_requester_status_alert");
        let this_requester_status_info = document.getElementById("this_requester_status_info");

        function fetchYourQuery() {
            const token = new URLSearchParams(window.location.search).get('requester_token');

            if (!token) {
                alert("Token was not assigned");
                window.location.href = `${realHost}/public/requester/requester_form.php`;
            }

            // ONLY Flask API (no PHP fallback)
            if (!(endpointHost && endpointHost.length > 0)) {
                alert('Service unavailable. Please try again later.');
                return;
            }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/requester?token_number=' + encodeURIComponent(token),
                type: 'GET',
                xhrFields: { withCredentials: true },
                success: function(response) {
                    if (response && response.status === 'success') {
                        renderRequesterState(response);
                    } else {
                        alert(response && response.message ? response.message : 'Failed to load status');
                    }
                },
                error: function() { alert('Network error. Please try again.'); }
            });
        }

        function renderRequesterState(response) {
            const statusVal = response.requester_status || (response.data && response.data.status) || 'N/A';
            if (statusVal === "pending" || statusVal === "serve") {
                this_requester_status_alert.classList.remove('alert-success', 'alert-danger', 'alert-warning');
                this_requester_status_alert.classList.add('alert-info');
            } else if (statusVal === "completed") {
                this_requester_status_alert.classList.remove('alert-info', 'alert-danger', 'alert-warning');
                this_requester_status_alert.classList.add('alert-success');
            } else if (statusVal === "missed") {
                this_requester_status_alert.classList.remove('alert-success', 'alert-danger', 'alert-info');
                this_requester_status_alert.classList.add('alert-warning');
            } else if (statusVal === "cancelled") {
                this_requester_status_alert.classList.remove('alert-success', 'alert-info', 'alert-warning');
                this_requester_status_alert.classList.add('alert-danger');
            }
            this_requester_status_info.textContent = String(statusVal).toUpperCase();

            // Update button based on status (priority flow redirects to priority form)
            const actionButton = document.getElementById('btnCancelRequestModal');
            if (statusVal === "completed" || statusVal === "cancelled") {
                actionButton.textContent = 'Exit';
                actionButton.classList.remove('btn-primary');
                actionButton.classList.add('btn-success');
                actionButton.removeAttribute('data-bs-toggle');
                actionButton.removeAttribute('data-bs-target');
                actionButton.href = '/public/requester/requester_form_priority.php';
            } else {
                actionButton.textContent = 'Cancel Request';
                actionButton.classList.remove('btn-success');
                actionButton.classList.add('btn-primary');
                actionButton.setAttribute('data-bs-toggle', 'modal');
                actionButton.setAttribute('data-bs-target', '#requestCancelModal');
                actionButton.href = '#';
            }

            const qNum = response.queueNumber || (response.data && response.data.queue_number) || 'N/A';
            const cNum = response.counterNumber || (response.data && response.data.counter_number) || 'N/A';
            const curQ = response.currentQueueNumber || 'N/A';
            $('#queueNumber').text(qNum);
            $('#counterNumber').text(cNum);
            $('#currentQueueNumber').text(curQ);
            // Trigger sound notification when the requester's number matches the current queue
            try {
                if (typeof soundManager !== 'undefined' && soundManager && soundManager.onStateUpdated) {
                    soundManager.onStateUpdated(qNum, curQ);
                }
            } catch (e) {
                console.error('Sound manager error', e);
            }
        }

        // PHP fallback removed intentionally

        let btnCancelRequest = document.getElementById("btnCancelRequest");
        if (btnCancelRequest) {
            btnCancelRequest.addEventListener("click", function () {
                // get token from url
                const token = new URLSearchParams(window.location.search).get('requester_token');
                console.log(token);
                var data = { token_number: token };
                // ONLY Flask PATCH (no PHP fallback)
                if (!(endpointHost && endpointHost.length > 0)) {
                    alert('Service unavailable. Please try again later.');
                    return;
                }
                $.ajax({
                    url: endpointHost.replace(/\/$/, '') + '/api/requester',
                    type: "PATCH",
                    data: JSON.stringify(data),
                    contentType: 'application/json; charset=utf-8',
                    xhrFields: { withCredentials: true },
                    success: function (response) {
                        if (response && response.status === 'success') {
                            try {
                                var modalEl = document.getElementById('requestCancelModal');
                                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                                if (!modalInstance) modalInstance = new bootstrap.Modal(modalEl);
                                modalInstance.hide();
                            } catch (e) {}
                            alert(response.message);
                            fetchYourQuery();
                            setTimeout(function() { window.location.href = '/public/requester/requester_form_priority.php'; }, 900);
                        } else {
                            alert(response && response.message ? response.message : 'Cancellation failed');
                        }
                    },
                    error: function () {
                        alert('Network error. Please try again.');
                    }
                });
            });
        }
        // PHP cancel fallback removed intentionally
        // Notification sound manager: plays a beep 3 times when the requester's number
        // matches the current queue number. Uses Web Audio API with an Audio fallback
        // so you can replace with a custom SFX URL via soundManager.setCustomSound(url).
        (function(){
            function NotificationSoundManager() {
                this.audioContext = null;
                // Default custom sound path - replace with your SFX file
                this.customUrl = '/public/asset/audio/notify.wav';
                this.prevMatched = false;
                this.enabled = true;
                this.initAudio();
            }

            NotificationSoundManager.prototype.initAudio = function() {
                try {
                    var AC = window.AudioContext || window.webkitAudioContext;
                    if (AC) this.audioContext = new AC();
                } catch (e) {
                    this.audioContext = null;
                }
            };

            NotificationSoundManager.prototype.setCustomSound = function(url) {
                this.customUrl = url;
            };

            NotificationSoundManager.prototype.playBeepOnce = function() {
                if (!this.enabled) return;
                if (this.customUrl) {
                    var a = new Audio(this.customUrl);
                    a.play().catch(function(){});
                    return;
                }
                if (!this.audioContext) this.initAudio();
                if (!this.audioContext) return;
                var ctx = this.audioContext;
                var o = ctx.createOscillator();
                var g = ctx.createGain();
                o.type = 'sine';
                o.frequency.value = 880;
                g.gain.value = 0.25;
                o.connect(g);
                g.connect(ctx.destination);
                o.start();
                g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.25);
                o.stop(ctx.currentTime + 0.25);
            };

            NotificationSoundManager.prototype.playThreeTimes = function() {
                this.playBeepOnce();
                var self = this;
                setTimeout(function() { self.playBeepOnce(); }, 400);
                setTimeout(function() { self.playBeepOnce(); }, 800);
            };

            NotificationSoundManager.prototype.onStateUpdated = function(qNum, curQ) {
                try {
                    if (!this.enabled) return;
                    if (qNum == null || curQ == null) { this.prevMatched = false; return; }
                    if (String(qNum) === String(curQ) && String(qNum) !== 'N/A') {
                        if (!this.prevMatched) {
                            this.playThreeTimes();
                            this.prevMatched = true;
                        }
                    } else {
                        this.prevMatched = false;
                    }
                } catch (e) {}
            };

            NotificationSoundManager.prototype.toggleEnabled = function() { this.enabled = !this.enabled; return this.enabled; };

            window.soundManager = new NotificationSoundManager();
        })();

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
