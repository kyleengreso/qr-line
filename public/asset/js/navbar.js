
let btnLogout = document.getElementById("btn-logout");
if (btnLogout) {
    btnLogout.addEventListener("click", function () {
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
}