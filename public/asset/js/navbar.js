
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
function startClock(el) {
    if (!el) return;
    function tick() {
        const date = new Date();
        el.textContent = date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    }
    tick();
    setInterval(tick, 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    const clockEl = document.getElementById('current-time') || document.getElementById('rtClock');
    startClock(clockEl);

});
