// Check if user_id exists in localStorage and show/hide the logout button accordingly
if (localStorage.getItem("user_id")) {
    document.getElementById("divbtnLO").style.display = "block";
    document.getElementById("btnLogOut").style.display = "block";
} else {
    document.getElementById("divbtnLO").style.display = "none";
    document.getElementById("btnLogOut").style.display = "none";
}

// Add event listener for the logout button
document.getElementById("btnLogOut").addEventListener("click", function() {
    $.ajax({
        url: "./../api/api_authenticate.php",
        type: "POST",
        data: JSON.stringify({
            username: "OK",
            password: "OK",
            auth_method: "logout"
        }),
        contentType: "application/json",
        dataType: "json",
        success: function(response) {
            if (response.status === "success") {
                localStorage.clear();
                document.getElementById("divbtnLO").style.display = "none";
                document.getElementById("btnLogOut").style.display = "none";
                window.location.href = "./../auth/login.php";
            } else {
                console.log("Error:", response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
});