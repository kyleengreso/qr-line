<?php include "./../base.php" ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Stats | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg min-h-screen">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="pt-24 px-4 pb-12 flex justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-4xl w-full">
            <h1 class="text-3xl font-bold text-center mb-6"><?php echo $project_name ?> Stats</h1>
            <div class="bg-gray-100 rounded-xl p-6 text-center mb-4">
                <h3 class="text-4xl font-bold text-psu-orange" id="transactions-total">0</h3>
                <p class="text-gray-500">Total Transactions</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white border rounded-xl p-6 text-center">
                    <i class="bi bi-people text-4xl text-psu-orange mb-2 block"></i>
                    <h3 class="text-2xl font-bold" id="transactions-today">0</h3>
                    <p class="text-gray-500 text-sm">Today</p>
                </div>
                <div class="bg-white border rounded-xl p-6 text-center">
                    <i class="bi bi-people-fill text-4xl text-psu-orange mb-2 block"></i>
                    <h3 class="text-2xl font-bold" id="transactions-yesterday">0</h3>
                    <p class="text-gray-500 text-sm">Yesterday</p>
                </div>
                <div class="bg-white border rounded-xl p-6 text-center">
                    <i class="bi bi-calendar-fill text-4xl text-psu-orange mb-2 block"></i>
                    <h3 class="text-2xl font-bold" id="transactions-week">0</h3>
                    <p class="text-gray-500 text-sm">This Week</p>
                </div>
                <div class="bg-white border rounded-xl p-6 text-center">
                    <i class="bi bi-calendar text-4xl text-psu-orange mb-2 block"></i>
                    <h3 class="text-2xl font-bold" id="transactions-month">0</h3>
                    <p class="text-gray-500 text-sm">This Month</p>
                </div>
                <div class="bg-white border rounded-xl p-6 text-center">
                    <i class="bi bi-calendar-fill text-4xl text-psu-orange mb-2 block"></i>
                    <h3 class="text-2xl font-bold" id="transactions-year">0</h3>
                    <p class="text-gray-500 text-sm">This Year</p>
                </div>
            </div>
        </div>
    </div>
    <?php after_js()?>
    <?php include_once "./../includes/footer.php"; ?>
    <script>
    const endpointHost = window.endpointHost;
    function rtTransaction(){
        if(!endpointHost) return;
        $.ajax({url:endpointHost.replace(/\/$/,'')+'/api/dashboard/admin/public',type:'GET',dataType:'json',xhrFields:{withCredentials:true},
            success:function(r){
                var d=r&&r.data||{};
                if(r.status==='success'){
                    $('#transactions-today').text(d.transaction_today_total||0);
                    $('#transactions-total').text(d.transaction_total||0);
                    $('#transactions-yesterday').text(d.transction_yesterday_total||0);
                    $('#transactions-week').text(d.transaction_week_total||0);
                    $('#transactions-month').text(d.transaction_month_total||0);
                    $('#transactions-year').text(d.transaction_year_total||0);
                }
            }
        });
    }
    rtTransaction();
    </script>
</body>
</html>
