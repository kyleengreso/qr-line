<?php
include "./../base.php"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Counter Stats | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
        <div class="container d-flex justify-content-center before-footer" style="margin-top: 100px;transform:scale(1)">
        <div class="card shadow-sm p-4" style="max-width: 800px; width: 100%;">
            <div class="w-100">
                <h4 class="text-center fw-bold fs-2">Counter Stats</h4>
            </div>
            <div class="w-100">
                <div class="w-100 w-md-50">
                    <div class="card">
                        <span class="fs-5 fw-bold text-center" id="requester_number_label">
                            Current Number
                        </span>
                        <span class="fs-1 fw-bold text-center" id="requester_number_latest">
                            
                        </span>
                    </div>
                </div>
                <div class="row" id="counters-list">
                    <div class="col-6 p-0 m-0">
                        <div class="card m-2 p-2">
                            <div class="row p-0 m-0">
                                <div class="fw-bold text-start col-6 mb-2">
                                    Counter 
                                </div>
                                <div class="fw-bold text-end col-6">
                                    1
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/user_form.js"></script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
<script>
    let counters_list = document.getElementById("counters-list");
    let requester_number_label = document.getElementById("requester_number_label");
    let requester_number_latest = document.getElementById("requester_number_latest");
    function fetchCounters() {
        let params = new URLSearchParams({
            counter_current_number: true
        })
        if (!(typeof endpointHost !== 'undefined' && endpointHost && endpointHost.length > 0)) {
            console.warn('API host not configured; cashier stats unavailable');
            return;
        }
        $.ajax({
            url: endpointHost.replace(/\/$/, '') + '/api/counter_current_number',
            type: 'GET',
            success: function(response) {
                counters_list.innerHTML = "";
                if (response.status == "success") {
                    requester_number_label.classList.remove('text-danger');
                    requester_number_label.textContent = "Current Number";
                    requester_number_latest.textContent = response.requester;
                    console.log(response.counters);
                    let counters = response.counters;
                    counters.forEach(counter => {
                        console.log(counter);
                    counters_list.innerHTML += `
                    <div class="col-6 p-0 m-0">
                        <div class="card m-2 p-2">
                            <div class="row p-0 m-0">
                                <div class="fw-bold text-start col-6">
                                    Counter ${counter.counterNumber}
                                </div>
                                <div class="fw-bold text-end col-6">
                                    ${counter.queue_number ?counter.queue_number: "No Queue"}
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    });
                } else {
                    console.log(response);
                    requester_number_label.classList.add('text-danger');
                    requester_number_label.textContent = "Notice";
                    requester_number_latest.textContent = response.message;

                }
            },
            error: function(response) {
                console.log(response);
            }
        })
    }
    fetchCounters();

    setInterval(() => {
        fetchCounters();
    }, 5000);
</script>
</html>
