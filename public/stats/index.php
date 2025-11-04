<?php
include "./../base.php"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Stats | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
        <div class="container d-flex justify-content-center before-footer" style="margin-top: 100px;transform:scale(0.9)">
        <div class="card shadow-sm p-4" style="max-width: 1000px; width: 100%;">
            <div class="w-100">
                <h4 class="text-center fw-bold fs-1"><?php echo $project_name ?> Stats</h4>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="text-center bg-white p-4 border border-2 shadow-sm rounded">
                        <h3 class="fs-2 fw-bold" id="transactions-total"></h3>
                        <p class="text-muted mb-0">Total Transactions</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-people fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold" id="transactions-today"></h3>
                            <p class="text-muted mb-0">Transaction Today</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-people-fill fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold" id="transactions-yesterday"></h3>
                            <p class="text-muted mb-0">Transaction Yesterday</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-calendar-fill fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold" id="transactions-week"></h3>
                            <p class="text-muted mb-0">This week</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-calendar fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold" id="transactions-month"></h3>
                            <p class="text-muted mb-0">This Month</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-calendar-fill fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold" id="transactions-year"></h3>
                            <p class="text-muted mb-0">This Year</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
<script>
    // Monitor
    const endpointHost = "<?php echo isset($endpoint_server) ? rtrim($endpoint_server, '/') : ''; ?>";
    function rtTransaction() {
        if (!(typeof endpointHost !== 'undefined' && endpointHost && endpointHost.length > 0)) {
        console.warn('API host not configured; stats unavailable');
        return;
    }
    $.ajax({
        url: endpointHost.replace(/\/$/, '') + '/api/dashboard/admin/public',
        type: 'GET',
        dataType: 'json',
        xhrFields: { withCredentials: true },
        crossDomain: true,
        beforeSend: function(xhr) {
            <?php if (isset($_COOKIE['token']) && $_COOKIE['token']): ?>
            try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch (ex) { console.error(ex); }
            <?php endif; ?>
        },
        success: function(response) {
            const d = response && response.data ? response.data : {};
            if (response.status === 'success') {
                $('#transactions-today').text(typeof d.transaction_today_total !== 'undefined' ? d.transaction_today_total : 'N/A');
                $('#transactions-total').text(typeof d.transaction_total !== 'undefined' ? d.transaction_total : 'N/A');
                $('#transactions-yesterday').text(typeof d.transction_yesterday_total !== 'undefined' ? d.transction_yesterday_total : 'N/A');
                $('#transactions-week').text(typeof d.transaction_week_total !== 'undefined' ? d.transaction_week_total : 'N/A');
                $('#transactions-month').text(typeof d.transaction_month_total !== 'undefined' ? d.transaction_month_total : 'N/A');
                $('#transactions-year').text(typeof d.transaction_year_total !== 'undefined' ? d.transaction_year_total : 'N/A');
                // optional breakdowns (if you add UI elements later)
                // d.transaction_today_pending, d.transaction_today_completed, d.transaction_today_cancelled, d.transaction_today_student
            }
        },
        error: function(response) {
            console.log(response);
        }
    });
}
rtTransaction();
</script>
</html>
