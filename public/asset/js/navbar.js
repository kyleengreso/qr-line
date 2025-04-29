
function logOut() {
    var data = {
        method: "logout",
    }
    $.ajax({
        url: "../api/api_endpoint.php",
        type: "POST",
        data: JSON.stringify(data),
        success: function (response) {
            console.log(response);
            if (response.status) {
                // Redirect to the login page
                window.location.href = "./../auth/login.php";
            }
        },
        error: function (xhr, status, error) {
            console.error("Logout request failed:", error);
            alert("An error occurred while logging out. Please try again.");
        }
    });
}

let btnLogout1 = document.getElementById("btn-logout-1");
if (btnLogout1) {
    btnLogout1.addEventListener("click", function () {
        logOut();
    });
};

let btnLogout2 = document.getElementById("btn-logout-2");
if (btnLogout2) {
    btnLogout2.addEventListener("click", function () {
        logOut();
    });
}

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
