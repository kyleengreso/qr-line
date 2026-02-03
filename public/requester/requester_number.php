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
    <style>
        .queue-circle{width:320px;height:320px;border-radius:50%;border:5px solid rgb(255,110,55);display:flex;flex-direction:column;align-items:center;justify-content:center;background:#fff;box-shadow:0 0 15px rgba(0,0,0,.2)}
        @media(min-width:640px){.queue-circle{width:400px;height:400px}}
    </style>
    <?php
    $requester_token = isset($_SESSION['requester_token']) ? $_SESSION['requester_token'] : (isset($_GET['requester_token']) ? $_GET['requester_token'] : '');
    $web_domain = $_SERVER['HTTP_HOST'];
    $web_resource = $_SERVER['REQUEST_URI'];
    $website = $web_domain . $web_resource . '?requester_token=' . $requester_token;
    ?>
</head>
<body class="bg bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Your Queue</h1>
            <p class="text-gray-500 text-sm">Your queue status</p>
        </div>
        
        <!-- Status Badge -->
        <div class="flex justify-center mb-6">
            <div id="this_requester_status_alert" class="px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                Status: <span class="font-bold" id="this_requester_status_info">Loading...</span>
            </div>
        </div>
        
        <!-- Queue Circle -->
        <div class="flex justify-center mb-6">
            <div class="queue-circle text-center p-5">
                <img src="./../asset/images/logo_blk.png" alt="logo" class="w-16 -mt-4 mb-2">
                <div class="flex justify-between items-center w-4/5 gap-4">
                    <div class="flex flex-col items-center">
                        <p class="text-lg font-bold mb-1">Number:</p>
                        <p class="text-5xl font-bold" id="queueNumber">N/A</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <p class="text-lg font-bold mb-1">Counter:</p>
                        <p class="text-5xl font-bold" id="counterNumber">N/A</p>
                    </div>
                </div>
                <p class="text-xl font-bold mt-4">Current number: <strong><span id="currentQueueNumber">N/A</span></strong></p>
            </div>
        </div>
        
        <!-- Action Button -->
        <div class="flex justify-center">
            <a href="#" id="btnCancelRequestModal" class="px-8 py-3 bg-[rgb(255,110,55)] text-white font-semibold rounded-lg hover:bg-[rgb(230,60,20)] transition">Cancel Request</a>
        </div>
        
        <!-- Footer -->
        <p class="text-center text-gray-400 text-sm mt-8">&copy; <?php echo project_year()?> <?php echo $project_name?></p>
    </div>

    <!-- Cancel Modal -->
    <div id="requestCancelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 font-bold text-lg">Cancel Transaction?</div>
            <div class="p-6 text-gray-700">Do you want to cancel your current transaction?</div>
            <div class="flex justify-end gap-3 px-6 pb-6">
                <button type="button" onclick="closeModal('requestCancelModal')" class="px-5 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">Close</button>
                <button type="button" id="btnCancelRequest" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Connection Alert -->
    <div id="connectionAlert" class="hidden fixed bottom-4 right-4 z-50 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg shadow-lg">
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 border-2 border-yellow-600 border-t-transparent rounded-full animate-spin"></span>
            <span>Connecting...</span>
        </div>
    </div>

<?php after_js()?>
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

let this_requester_status_alert = document.getElementById("this_requester_status_alert");
let this_requester_status_info = document.getElementById("this_requester_status_info");
let connectionAlert = document.getElementById("connectionAlert");
let connectionTimeout = null;

function showConnectionAlert() {
    if (connectionTimeout) clearTimeout(connectionTimeout);
    connectionTimeout = setTimeout(function() {
        connectionAlert.classList.remove('hidden');
    }, 20000);
}

function hideConnectionAlert() {
    if (connectionTimeout) { clearTimeout(connectionTimeout); connectionTimeout = null; }
    connectionAlert.classList.add('hidden');
}

function fetchYourQuery() {
    const token = new URLSearchParams(window.location.search).get('requester_token');
    if (!token) {
        alert("Token was not assigned");
        window.location.href = `${realHost}/public/requester/requester_form.php`;
    }
    if (!(endpointHost && endpointHost.length > 0)) {
        alert('Service unavailable. Please try again later.');
        return;
    }
    showConnectionAlert();
    $.ajax({
        url: endpointHost.replace(/\/$/, '') + '/api/requester?token_number=' + encodeURIComponent(token),
        type: 'GET',
        xhrFields: { withCredentials: true },
        success: function(response) {
            hideConnectionAlert();
            if (response && response.status === 'success') {
                renderRequesterState(response);
            } else {
                alert(response ? (response.message || 'Failed to load status') : 'Failed to load status');
            }
        },
        error: function() {
            hideConnectionAlert();
            $('#queueNumber').text('0');
            alert('Network error. Please try again.');
        }
    });
}

function renderRequesterState(response) {
    const statusVal = response.requester_status || (response.data && response.data.status) || 'N/A';
    this_requester_status_alert.className = 'mb-4 px-4 py-2 rounded text-sm font-semibold';
    if (statusVal === "pending" || statusVal === "serve") {
        this_requester_status_alert.classList.add('bg-blue-100', 'text-blue-800');
    } else if (statusVal === "completed") {
        this_requester_status_alert.classList.add('bg-green-100', 'text-green-800');
    } else if (statusVal === "missed") {
        this_requester_status_alert.classList.add('bg-yellow-100', 'text-yellow-800');
    } else if (statusVal === "cancelled") {
        this_requester_status_alert.classList.add('bg-red-100', 'text-red-800');
    } else {
        this_requester_status_alert.classList.add('bg-gray-100', 'text-gray-800');
    }
    this_requester_status_info.textContent = String(statusVal).toUpperCase();

    const actionButton = document.getElementById('btnCancelRequestModal');
    if (statusVal === "completed" || statusVal === "cancelled") {
        actionButton.textContent = 'Exit';
        actionButton.className = 'px-8 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition';
        actionButton.onclick = function() { window.location.href = '/public/requester/requester_form.php'; };
    } else {
        actionButton.textContent = 'Cancel Request';
        actionButton.className = 'px-8 py-3 bg-[rgb(255,110,55)] text-white font-semibold rounded-lg hover:bg-[rgb(230,60,20)] transition';
        actionButton.onclick = function(e) { e.preventDefault(); openModal('requestCancelModal'); };
    }

    const qNum = response.queueNumber || (response.data && response.data.queue_number) || 'N/A';
    const cNum = response.counterNumber || (response.data && response.data.counter_number) || 'N/A';
    const curQ = response.currentQueueNumber || 'N/A';
    $('#queueNumber').text(qNum);
    $('#counterNumber').text(cNum);
    $('#currentQueueNumber').text(curQ);
    try {
        if (typeof soundManager !== 'undefined' && soundManager && soundManager.onStateUpdated) {
            soundManager.onStateUpdated(qNum, curQ);
        }
    } catch (e) {}
}

let btnCancelRequest = document.getElementById("btnCancelRequest");
if (btnCancelRequest) {
    btnCancelRequest.addEventListener("click", function () {
        const token = new URLSearchParams(window.location.search).get('requester_token');
        var data = { token_number: token };
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
                    closeModal('requestCancelModal');
                    alert(response.message);
                    fetchYourQuery();
                    setTimeout(function() { window.location.href = '/public/requester/requester_form.php'; }, 900);
                } else {
                    alert(response ? (response.message || 'Cancellation failed') : 'Cancellation failed');
                }
            },
            error: function () { alert('Network error. Please try again.'); }
        });
    });
}

(function(){
    function NotificationSoundManager() {
        this.audioContext = null;
        this.customUrl = '/public/asset/audio/notify.wav';
        this.prevMatched = false;
        this.enabled = true;
        this.initAudio();
    }
    NotificationSoundManager.prototype.initAudio = function() {
        try { var AC = window.AudioContext || window.webkitAudioContext; if (AC) this.audioContext = new AC(); } catch (e) { this.audioContext = null; }
    };
    NotificationSoundManager.prototype.setCustomSound = function(url) { this.customUrl = url; };
    NotificationSoundManager.prototype.playBeepOnce = function() {
        if (!this.enabled) return;
        if (this.customUrl) { var a = new Audio(this.customUrl); a.play().catch(function(){}); return; }
        if (!this.audioContext) this.initAudio();
        if (!this.audioContext) return;
        var ctx = this.audioContext, o = ctx.createOscillator(), g = ctx.createGain();
        o.type = 'sine'; o.frequency.value = 880; g.gain.value = 0.25;
        o.connect(g); g.connect(ctx.destination); o.start();
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
                if (!this.prevMatched) { this.playThreeTimes(); this.prevMatched = true; }
            } else { this.prevMatched = false; }
        } catch (e) {}
    };
    NotificationSoundManager.prototype.toggleEnabled = function() { this.enabled = !this.enabled; return this.enabled; };
    window.soundManager = new NotificationSoundManager();
})();

fetchYourQuery();
setInterval(function() { fetchYourQuery(); }, 5000);
</script>
</body>
</html>
