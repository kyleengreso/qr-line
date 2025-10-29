
function logOut() {
    // Try to log out from the Python API first (so server-side session/cookie there is cleared).
    // Use credentials so browser sends any cookies for the API origin.
    $.ajax({
        url: 'http://127.0.0.1:5000/api/logout',
        type: 'POST',
        contentType: 'application/json',
        xhrFields: { withCredentials: true },
        success: function(apiResp) {
            console.log('API logout response:', apiResp);
            // Regardless of API response, clear the local token cookie and redirect.
            clearLocalTokenAndRedirect();
        },
        error: function(xhr, status, error) {
            // Still attempt to clear local cookie and redirect even if API logout failed
            console.warn('API logout failed or returned error:', status, error, xhr.responseText);
            clearLocalTokenAndRedirect();
        }
    });
}

function clearLocalTokenAndRedirect() {
    $.ajax({
        url: './auth/clear_token.php',
        type: 'POST',
        dataType: 'json',
        success: function(resp) {
            console.log('Local token cleared:', resp);
            window.location.href = './../auth/login.php';
        },
        error: function(xhr, status, error) {
            console.error('Failed to clear local token cookie:', error);
            // Still redirect to login page
            window.location.href = './../auth/login.php';
        }
    });
}

// Employee Logout Notify

// DOM document ready no jquery
document.addEventListener("DOMContentLoaded", function () {
    let logOutNotify = document.getElementById('logOutNotify');
    
    let btnLogout1 = document.getElementById("btn-logout-1");
    if (btnLogout1) {
        btnLogout1.addEventListener("click", function () {
            logOutNotify.classList.remove('d-none');
            setTimeout(() => {
                logOut();
            }, 2000);
        });
    };
    
    let btnLogout2 = document.getElementById("btn-logout-2");
    if (btnLogout2) {
        btnLogout2.addEventListener("click", function () {
            logOutNotify.classList.remove('d-none');
            setTimeout(() => {
                logOut();
            }, 2000);
        });
    }

});

// RealTimeClock
let rtClock = document.getElementById("rtClock");
if (rtClock) {
    setInterval(function () {
        var date = new Date();
        rtClock.innerHTML = date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    }, 1000);
}
