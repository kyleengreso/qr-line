<?php
include_once __DIR__ . "/../base.php";
@include_once __DIR__ . '/../includes/config.php';
requireAdmin();
$token = $_COOKIE['token'] ?? null;
if ($token) { $token = decryptToken($token, $master_key ?? ''); $token = json_decode(json_encode($token)); }
$username = isset($token->username) ? $token->username : null;
$id = isset($token->id) ? (int)$token->id : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Dashboard | <?php echo $project_name; ?></title>
    <?php head_css()?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>window.phpToken = <?php echo isset($_COOKIE['token']) ? "'" . addslashes($_COOKIE['token']) . "'" : "null"; ?>;</script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include "./../includes/navbar.php"; ?>
    <div class="pt-24 px-4 pb-12 max-w-7xl mx-auto">
        <div id="logOutNotify" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg"><?php echo $username?> has logged out successfully</div>
        <div id="dashboardStatus" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg"><span id="dashboardStatusMsg">You've been cut off.</span></div>
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-500 text-sm">Transaction statistics overview</p>
            </div>
            <div id="cutOffNotification" class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-green-700">
                <i class="bi bi-check-circle mr-1"></i> Operational
            </div>
        </div>

        <!-- Primary Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-500 text-sm font-medium">Today</span>
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded flex items-center justify-center"><i class="bi bi-calendar-day"></i></span>
                </div>
                <div class="text-3xl font-bold text-gray-800" id="transactions-today">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-500 text-sm font-medium">Pending</span>
                    <span class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded flex items-center justify-center"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <div class="text-3xl font-bold text-gray-800" id="transactions-pending">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-500 text-sm font-medium">Completed</span>
                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded flex items-center justify-center"><i class="bi bi-check-lg"></i></span>
                </div>
                <div class="text-3xl font-bold text-gray-800" id="transactions-completed">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-500 text-sm font-medium">Cancelled</span>
                    <span class="w-8 h-8 bg-red-100 text-red-600 rounded flex items-center justify-center"><i class="bi bi-x-lg"></i></span>
                </div>
                <div class="text-3xl font-bold text-gray-800" id="transactions-cancelled">0</div>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="text-gray-500 text-sm font-medium mb-2">Student Transactions Today</div>
                <div class="text-2xl font-bold text-gray-800" id="transactions-student-today">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="text-gray-500 text-sm font-medium mb-2">Total Transactions</div>
                <div class="text-2xl font-bold text-[rgb(255,110,55)]" id="transactions-total">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 col-span-2 lg:col-span-1">
                <div class="text-gray-500 text-sm font-medium mb-2">Yesterday</div>
                <div class="text-2xl font-bold text-gray-800" id="transactions-yesterday">0</div>
            </div>
        </div>

        <!-- Period Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-lg p-5 text-center">
                <div class="text-gray-500 text-xs uppercase font-medium mb-1">This Week</div>
                <div class="text-xl font-bold text-gray-800" id="transactions-week">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 text-center">
                <div class="text-gray-500 text-xs uppercase font-medium mb-1">This Month</div>
                <div class="text-xl font-bold text-gray-800" id="transactions-month">0</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5 text-center">
                <div class="text-gray-500 text-xs uppercase font-medium mb-1">This Year</div>
                <div class="text-xl font-bold text-gray-800" id="transactions-year">0</div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white border border-gray-200 rounded-lg mb-6">
            <div class="px-5 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i class="bi bi-graph-up text-[rgb(255,110,55)]"></i>
                    <span class="font-semibold text-gray-800">Transactions Overview</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="dateRange-select" class="border border-gray-300 text-gray-700 px-3 py-1.5 rounded text-sm focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        <option value="day">Today</option>
                        <option value="week">This Week</option>
                        <option value="last-week">Last Week</option>
                        <option value="month">This Month</option>
                        <option value="last-30-days">Last 30 Days</option>
                        <option value="last-3-months">Last 3 months</option>
                        <option value="last-12-months">Last 12 months</option>
                    </select>
                    <button id="btnRefreshChart" class="px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50 text-gray-600"><i class="bi bi-arrow-clockwise"></i></button>
                    <button id="btnExportChart" class="px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50 text-gray-600"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="p-5"><canvas id="transaction-chart" class="w-full" style="height:320px"></canvas></div>
        </div>

        <!-- Generate Report -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center gap-2">
                <i class="bi bi-file-earmark-pdf text-[rgb(255,110,55)]"></i>
                <span class="font-semibold text-gray-800">Generate Report</span>
            </div>
            <div class="p-5">
                <div id="generateReportNotify" class="hidden mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">Specify the month and year to generate</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Month</label>
                        <select id="month" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none"><option value="">--</option></select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Year</label>
                        <select id="year" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none"><option value="">----</option></select>
                    </div>
                    <div class="flex items-end">
                        <button id="btnGenerateReport" class="w-full bg-[rgb(255,110,55)] text-white font-medium py-2 rounded hover:bg-[rgb(230,60,20)] transition">Generate PDF</button>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button id="btnQuickThisMonth" class="px-4 py-1.5 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50 transition">This month</button>
                    <button id="btnQuickLastMonth" class="px-4 py-1.5 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50 transition">Last month</button>
                </div>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <script>
    var currentUsername = "<?php echo isset($username) ? htmlentities($username) : ''; ?>";
    const endpointHost = window.endpointHost;

    // Populate month/year dropdowns
    var months=['January','February','March','April','May','June','July','August','September','October','November','December'];
    var ddYear=$('#year'), ddMonth=$('#month'), now=new Date();
    for(var y=2020;y<=now.getFullYear()+5;y++) ddYear.append('<option value="'+y+'">'+y+'</option>');
    for(var m=1;m<=12;m++) ddMonth.append('<option value="'+m+'">'+months[m-1]+'</option>');
    ddMonth.val(String(now.getMonth()+1)); ddYear.val(String(now.getFullYear()));

    $('#btnQuickThisMonth').on('click',function(){ ddMonth.val(String(now.getMonth()+1)); ddYear.val(String(now.getFullYear())); });
    $('#btnQuickLastMonth').on('click',function(){ var m=now.getMonth(),y=now.getFullYear(); if(m===0){m=12;y--;} ddMonth.val(String(m)); ddYear.val(String(y)); });

    $('#btnGenerateReport').click(function(){
        var m=$('#month').val(), y=$('#year').val();
        if(!m||!y){ $('#generateReportNotify').removeClass('hidden'); setTimeout(function(){$('#generateReportNotify').addClass('hidden');},5000); return; }
        if(!endpointHost) return;
        var url=endpointHost.replace(/\/$/,'')+'/api/report/monthly?year='+y+'&month='+m;
        if(currentUsername) url+='&user='+encodeURIComponent(currentUsername);
        window.open(url,'_blank');
    });

    // Dashboard stats
    function rtTransaction(){
        if(!endpointHost) return;
        $.ajax({url:endpointHost.replace(/\/$/,'')+'/api/dashboard/admin',type:'GET',xhrFields:{withCredentials:true},
            headers:window.phpToken?{'Authorization':'Bearer '+window.phpToken}:{},
            success:function(r){ var s=r&&r.data||{};
                $('#transactions-today').text(s.transaction_today_total||0);
                $('#transactions-pending').text(s.transaction_today_pending||0);
                $('#transactions-completed').text(s.transaction_today_completed||0);
                $('#transactions-cancelled').text(s.transaction_today_cancelled||0);
                $('#transactions-student-today').text(s.transaction_today_student||0);
                $('#transactions-total').text(s.transaction_total||0);
                $('#transactions-yesterday').text(s.transction_yesterday_total||0);
                $('#transactions-week').text(s.transaction_week_total||0);
                $('#transactions-month').text(s.transaction_month_total||0);
                $('#transactions-year').text(s.transaction_year_total||0);
            }
        });
    }
    rtTransaction();

    // Chart
    var transactionChart=null, dataRange='day';
    function fmtHr(h){var i=parseInt(h,10);return(i%12||12)+' '+(i>=12?'PM':'AM');}
    function fmtDate(d){try{var dt=new Date(d.includes('GMT')?d:d+'T00:00:00');return dt.toLocaleDateString('en-US',{month:'short',day:'numeric'});}catch(e){return d;}}
    function fmtMonth(m){try{if(m.includes('GMT')){var dt=new Date(m);return dt.toLocaleDateString('en-US',{month:'short',year:'2-digit'});}var p=m.split('-');return new Date(p[0],p[1]-1).toLocaleDateString('en-US',{month:'short',year:'2-digit'});}catch(e){return m;}}

    function getChartData(){
        var resp={stats:[]};
        if(!endpointHost) return resp;
        $.ajax({url:endpointHost.replace(/\/$/,'')+'/api/transaction_stats?data_range='+dataRange,type:'GET',async:false,xhrFields:{withCredentials:true},
            headers:window.phpToken?{'Authorization':'Bearer '+window.phpToken}:{},
            success:function(r){if(r&&r.status==='success')resp=r;}
        });
        return resp;
    }

    function initChart(data){
        var labels=[],vals=[];
        if(data&&data.stats&&data.stats.length){
            if(data.stats[0].hour){labels=data.stats.map(s=>fmtHr(s.hour));vals=data.stats.map(s=>s.total_transactions);}
            else if(data.stats[0].date){labels=data.stats.map(s=>fmtDate(s.date));vals=data.stats.map(s=>s.total_transactions);}
            else if(data.stats[0].month){labels=data.stats.map(s=>fmtMonth(s.month));vals=data.stats.map(s=>s.total_transactions);}
        }else{labels=['No Data'];vals=[0];}
        var ctx=document.getElementById('transaction-chart').getContext('2d');
        var grad=ctx.createLinearGradient(0,0,0,400);grad.addColorStop(0,'rgba(255,110,55,.25)');grad.addColorStop(1,'rgba(255,110,55,0)');
        transactionChart=new Chart(ctx,{type:'line',data:{labels:labels,datasets:[{label:'Transactions',tension:.35,backgroundColor:grad,borderColor:'rgb(255,110,55)',pointRadius:0,data:vals,fill:true}]},
            options:{maintainAspectRatio:false,scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#eef1f5'}}}}});
    }
    initChart(getChartData());

    function updateChart(){
        var data=getChartData(),labels=[],vals=[];
        if(data&&data.stats&&data.stats.length){
            if(data.stats[0].hour){labels=data.stats.map(s=>fmtHr(s.hour));vals=data.stats.map(s=>s.total_transactions);}
            else if(data.stats[0].date){labels=data.stats.map(s=>fmtDate(s.date));vals=data.stats.map(s=>s.total_transactions);}
            else if(data.stats[0].month){labels=data.stats.map(s=>fmtMonth(s.month));vals=data.stats.map(s=>s.total_transactions);}
        }
        transactionChart.data.labels=labels;transactionChart.data.datasets[0].data=vals;transactionChart.update();
    }

    $('#dateRange-select').change(function(){dataRange=$(this).val();updateChart();});
    $('#btnRefreshChart').click(updateChart);
    $('#btnExportChart').click(function(){var c=document.getElementById('transaction-chart'),a=document.createElement('a');a.download='transactions.png';a.href=c.toDataURL('image/png');a.click();});

    // Cut-off state
    var operational=true;
    function fetchCutOff(){
        if(!endpointHost) return;
        $.ajax({url:endpointHost.replace(/\/$/,'')+'/api/dashboard/admin/cut-off',type:'GET',xhrFields:{withCredentials:true},
            headers:window.phpToken?{'Authorization':'Bearer '+window.phpToken}:{},
            success:function(r){
                if(r.status==='success'){
                    if(r.cut_off_state==1){operational=false;$('#cutOffNotification').removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-700').html('<i class="bi bi-exclamation-circle mr-1"></i> Cut Off');}
                    else{operational=true;$('#cutOffNotification').removeClass('bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').html('<i class="bi bi-check-circle mr-1"></i> Operational');}
                }
            }
        });
    }
    fetchCutOff();

    setInterval(function(){if(operational){updateChart();rtTransaction();}},5000);
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>
