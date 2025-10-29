
function logOut() {
    // Navigate to server-side logout which will call the API and clear local cookie.
    window.location.href = '/public/auth/logout.php';
}

function clearLocalTokenAndRedirect() {
    // Not used anymore — local clearing handled by server-side logout.php
    window.location.href = '/public/auth/logout.php';
}

// Employee Logout Notify

// DOM document ready no jquery
document.addEventListener("DOMContentLoaded", function () {
    let logOutNotify = document.getElementById('logOutNotify');
    
    let btnLogout1 = document.getElementById("btn-logout-1");
    if (btnLogout1) {
        btnLogout1.addEventListener("click", function () {
            if (logOutNotify) {
                logOutNotify.classList.remove('d-none');
                setTimeout(() => {
                    logOut();
                }, 2000);
            } else {
                // If the page doesn't include the logout notification element,
                // just perform logout immediately.
                logOut();
            }
        });
    };
    
    let btnLogout2 = document.getElementById("btn-logout-2");
    if (btnLogout2) {
        btnLogout2.addEventListener("click", function () {
            if (logOutNotify) {
                logOutNotify.classList.remove('d-none');
                setTimeout(() => {
                    logOut();
                }, 2000);
            } else {
                logOut();
            }
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
