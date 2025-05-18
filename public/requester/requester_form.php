<?php
include "./../base.php";

$sql_cmd = "SELECT * FROM scheduler WHERE schedule_key = 'requester_form'";
$stmt = $conn->prepare($sql_cmd);
$stmt->execute();
$result = $stmt->get_result();

$schedule = $result->fetch_assoc();
if (!$schedule) {

}

$schedule_present = $schedule['enable'];
$time_start = date("H:i:s", strtotime($schedule['time_start']));
$time_end = date("H:i:s", strtotime($schedule['time_end']));
$time_now = date("H:i:s");
$everyday = explode(";", $schedule['everyday']);
$day_of_week = strtolower(date("D"));
$schedule_present = false;
foreach ($everyday as $day) {
    if ($day == $day_of_week) {
        $schedule_present = true;
        $schedule_day_announcment = "Come back later at";
        break;
    } else {
        $schedule_day_announcment = "Schedule is closed for today";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>QR Form | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 100px;min-height: 600px;">
        <div class="card shadow-sm p-4" style="max-width: 400px;width: 100%;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <?php if ($time_now > $time_start && $time_now < $time_end && $schedule_present) :?>
            <h4 class="text-center fw-bold">QR FORM</h4>
            <p class="text-center text-muted">PLEASE FILL UP</p>
            <form method="post" id="frmUserForm">
                <div class="input-group mb-2">
                    <div class="input-group-text"><i class="bi bi-person-fill"></i></div>
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Your Name" required>
                        <label for="name" class="form-label">Name</label>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <div class="input-group-text"><i class="bi bi-envelope-fill"></i></div>
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email Address" required>
                        <label for="email" class="form-label">Email</label>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-text"><i class="bi bi-cash"></i></div>
                    <div class="form-floating">
                        <select class="form-select"  name="transaction-history-payment" id="transaction-history-payment">
                            <option value="null">Payment</option>
                            <option value="registrar">Registrar</option>
                            <option value="assessment">Assessment</option>
                        </select>
                        <label for="email" class="form-label">Payment</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
            <?php else :?>
                <div class="text-center fw-bold">
                    <h5 class="text-danger">SCHEDULE CLOSED</h5>
                    <p class="text-muted"><?php echo $schedule_day_announcment?></p>
                </div>

                <div class="w-100">
                    <p>
                        Current Time: <span class="text-danger fw-bold"><?php echo $time_now ?></span><br>
                    </p>
                    <p>
                        Schedule Start: <span class="text-danger fw-bold"><?php echo $time_start ?></span><br>
                    </p>
                    <p>
                        Schedule End: <span class="text-danger fw-bold"><?php echo $time_end ?></span><br>
                    </p>
                </div>
            <?php endif ;?>
        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script>
        function sumbitUserForm(user) {
            var form = $('#frmUserForm');
            message_info(form, 'Processing...');
            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify(user),
                success: function(response) {
                    console.log(response.data);
                    if (response.status === 'success') {
                        message_success(form, response.message);
                        localStorage.setItem('requester_token', response.token_number);
                        var requester_token = localStorage.getItem('requester_token');
                        setTimeout(function() {
                            window.location.href = "./requester_number.php?requester_token=" + requester_token;
                        }, 1000);
                    } else {
                        message_error(form, response.message);
                    }
                },
                error: function() {
                    $('#user_number').text('0');
                }
            });
        }

        var payment = null;
        $('#transaction-history-payment').change(function() {
            payment = $(this).val();
            if (payment == 'null') {
                payment = null;
                // console.log(payment);
            }
            console.log(payment);
        });

        $('#frmUserForm').submit(function(e) {
            e.preventDefault();

            if (payment === null) {
                message_error($('#frmUserForm'), 'Please select payment type');
                return;
            }
            var user = {
                method : "requester_form",
                name: $('#name').val(),
                email: $('#email').val(),
                payment: payment,
                website: `${realHost}/public/requester/requester_number.php`
            };
            console.log(user);
            sumbitUserForm(user);
        });

    </script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
