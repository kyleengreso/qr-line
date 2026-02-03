<?php
include_once __DIR__ . "/../base.php";
restrictEmployeeMode();
$payload = getDecodedTokenPayload();
$tok = is_array($payload) ? json_decode(json_encode($payload)) : (is_object($payload) ? $payload : null);
$id = isset($tok->id) ? (int)$tok->id : 0;
$username = isset($tok->username) ? $tok->username : '';
$counterNumber = isset($tok->counterNumber) ? (int)$tok->counterNumber : 0;
$priority = isset($tok->priority) ? $tok->priority : 'N';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Dashboard | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include "./../includes/navbar.php"; ?>

    <!-- Connection Modal -->
    <div id="connectionTimeoutModal" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 text-center shadow-xl">
            <div class="animate-spin w-10 h-10 border-4 border-[rgb(255,110,55)] border-t-transparent rounded-full mx-auto mb-4"></div>
            <h5 class="text-lg font-semibold text-gray-800">Connecting...</h5>
            <p class="text-gray-500 text-sm">Attempting to reconnect</p>
        </div>
    </div>

    <div class="pt-24 px-4 pb-12 max-w-5xl mx-auto">
        <!-- Status Alert -->
        <div id="logOutNotify" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg"><?php echo $username?> has logged out successfully</div>
        <div id="cutOffNotification" class="hidden mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-green-100 text-green-700">
            <i class="bi bi-check-circle mr-1"></i> Operational
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Counter <span id="employee-counter-number"><?php echo $counterNumber ?></span></h1>
                <p class="text-gray-500 text-sm">Cashier Dashboard</p>
            </div>
            <div id="cutOffState" class="hidden px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                <i class="bi bi-pause-circle mr-1"></i> Cut-Off Active
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Left Panel -->
            <div>
                <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
                    <p class="text-gray-500 text-sm text-center mb-2">Currently Serving</p>
                    <div class="border-4 border-[rgb(255,110,55)] rounded-xl p-8 text-center mb-6">
                        <span id="queue-number" class="text-6xl font-bold text-gray-800">N/A</span>
                    </div>
                    <div class="flex justify-center gap-3 mb-4">
                        <button id="btn-counter-success" class="bg-[rgb(255,110,55)] text-white font-semibold px-8 py-3 rounded-lg hover:bg-[rgb(230,60,20)] transition">NEXT</button>
                        <button id="btn-counter-skip" class="bg-gray-500 text-white font-semibold px-8 py-3 rounded-lg hover:bg-gray-600 transition">SKIP</button>
                    </div>
                    <div class="flex justify-center">
                        <button id="employee-cut-off" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition font-medium">Cut-Off</button>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h6 class="font-semibold text-gray-800 mb-3">Auto Cut-Off</h6>
                    <div id="cutOff_trigger_notification" class="hidden mb-3 p-3 bg-blue-50 text-blue-700 rounded-lg text-sm">
                        <i class="bi bi-info-circle mr-1"></i> <span id="cutOff_trigger_message">1 queue remain.</span>
                    </div>
                    <div id="frmCutOff_trigger">
                        <label class="block text-sm text-gray-600 mb-2">Action after queues</label>
                        <select id="cut_off_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                            <option value="null">No action</option>
                            <option value="1">After this queue</option>
                            <option value="3">After 3 queues</option>
                            <option value="5">After 5 queues</option>
                            <option value="10">After 10 queues</option>
                        </select>
                    </div>
                    <div id="frmCutOff_trigger_message" class="hidden mt-3 p-3 bg-yellow-50 text-yellow-700 rounded-lg text-sm">
                        <i class="bi bi-exclamation-triangle mr-1"></i> Resume to enable auto cut-off
                    </div>
                </div>
            </div>

            <!-- Right Panel - Transactions Table -->
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h6 class="font-semibold text-gray-800 mb-4">Queue List</h6>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="table-transactions-student">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3 font-medium text-gray-600">#</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Email</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                    <small class="text-gray-500" id="transactions-count"></small>
                    <div id="transactions-pagination" class="flex gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <script>
    const serverTokenFallback = <?php echo json_encode($_COOKIE['token'] ?? ''); ?>;
    const this_counter_priority = "<?php echo $priority; ?>";
    const counterNumber = <?php echo $counterNumber; ?>;
    const employeeId = <?php echo $id; ?>;

    function getAuthTokenValue() {
        try { const m=document.cookie.match('(?:^|; )token=([^;]*)'); if(m) return decodeURIComponent(m[1]); } catch(e){}
        return serverTokenFallback||null;
    }

    (function(){
        window.API_BASE = window.endpointHost ? window.endpointHost.replace(/\/+$/,'') + '/api' : '';
        if(window.jQuery && $.ajaxSetup) $.ajaxSetup({xhrFields:{withCredentials:true},beforeSend:function(xhr){
            hideConnectionTimeout();
            const t=getAuthTokenValue(); if(t) xhr.setRequestHeader('Authorization','Bearer '+t);
        }});
    })();

    function showConnectionTimeout(){ document.getElementById('connectionTimeoutModal').classList.remove('hidden'); document.getElementById('connectionTimeoutModal').style.display='flex'; }
    function hideConnectionTimeout(){ document.getElementById('connectionTimeoutModal').style.display='none'; }

    // Fetch current queue
    function fetchTransaction(){
        $.ajax({url:window.API_BASE+'/cashier',type:'GET',cache:false,success:function(r){
            let el=document.getElementById('queue-number');
            if(r.status==='success'&&r.data&&r.data.queue_number!=null) el.innerHTML=r.data.queue_number;
            else el.innerHTML='No queue';
        }});
    }

    // Next/Skip buttons
    document.getElementById('btn-counter-success').addEventListener('click',function(e){
        e.preventDefault(); const btn=this,txt=btn.innerHTML; btn.disabled=true; btn.innerHTML='Processing...';
        $.ajax({url:window.API_BASE+'/cashier',type:'POST',contentType:'application/json',
            data:JSON.stringify({method:'cashier-success',idemployee:employeeId}),
            complete:function(){btn.disabled=false;btn.innerHTML=txt;},
            success:function(r){if(r.status==='success'){queue_remain_get();fetchCutOff();fetchTransaction();fetchStudentTransaction();}}
        });
    });

    document.getElementById('btn-counter-skip').addEventListener('click',function(e){
        e.preventDefault(); const btn=this,txt=btn.innerHTML; btn.disabled=true; btn.innerHTML='Processing...';
        $.ajax({url:window.API_BASE+'/cashier',type:'POST',contentType:'application/json',
            data:JSON.stringify({method:'cashier-missed',idemployee:employeeId}),
            complete:function(){btn.disabled=false;btn.innerHTML=txt;},
            success:function(r){if(r.status==='success'){queue_remain_get();fetchCutOff();fetchTransaction();fetchStudentTransaction();}}
        });
    });

    // Student transactions table
    let studentTransactions=[],studentTxnPage=1;const studentPageSize=10;
    function renderStudentTransactionsPage(){
        const tbody=document.querySelector('#table-transactions-student tbody');tbody.innerHTML='';
        if(!studentTransactions.length){document.getElementById('transactions-count').innerText='';updatePagination();return;}
        const total=studentTransactions.length,pages=Math.ceil(total/studentPageSize);
        if(studentTxnPage>pages)studentTxnPage=pages;
        const start=(studentTxnPage-1)*studentPageSize,end=Math.min(start+studentPageSize,total);
        for(let i=start;i<end;i++){const t=studentTransactions[i];tbody.innerHTML+=`<tr><td>${t.queue_number||''}</td><td>${t.email||''}</td><td>${t.payment||''}</td></tr>`;}
        document.getElementById('transactions-count').innerText=`${start+1}–${end} of ${total}`;
        updatePagination(pages);
    }
    function updatePagination(pages){
        pages=pages||1;const c=document.getElementById('transactions-pagination');c.innerHTML='';
        c.innerHTML=`<button class="px-3 py-1 border rounded ${studentTxnPage<=1?'opacity-50':'hover:bg-gray-100'}" ${studentTxnPage<=1?'disabled':''} onclick="studentTxnPage--;renderStudentTransactionsPage()">Prev</button>
        <span class="px-2">${studentTxnPage}/${pages}</span>
        <button class="px-3 py-1 border rounded ${studentTxnPage>=pages?'opacity-50':'hover:bg-gray-100'}" ${studentTxnPage>=pages?'disabled':''} onclick="studentTxnPage++;renderStudentTransactionsPage()">Next</button>`;
    }
    function fetchStudentTransaction(){
        $.ajax({url:window.API_BASE+'/dashboard/cashier',type:'GET',cache:false,success:function(r){
            studentTransactions=Array.isArray(r.data)?r.data:[];studentTxnPage=1;renderStudentTransactionsPage();
        }});
    }

    // Cut-off
    var operational=false;
    function fetchCutOff(){
        $.ajax({url:window.API_BASE+'/cashier?employeeCutOff=true&id='+employeeId,type:'GET',success:function(r){
            if(r.status==='success'){
                const notif=document.getElementById('cutOffNotification');
                const state=document.getElementById('cutOffState');
                const cutBtn=document.getElementById('employee-cut-off');
                if(r.cut_off_state==1){
                    operational=false;
                    notif.className='mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-red-100 text-red-700';
                    notif.innerHTML='<i class="bi bi-pause-circle mr-1"></i> Cut-Off Active';
                    state.classList.remove('hidden');
                    cutBtn.className='bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition font-medium';
                    cutBtn.innerText='Resume';
                    document.getElementById('btn-counter-success').disabled=true;document.getElementById('btn-counter-skip').disabled=true;
                    document.getElementById('frmCutOff_trigger').classList.add('hidden');
                    document.getElementById('frmCutOff_trigger_message').classList.remove('hidden');
                }else{
                    operational=true;
                    notif.className='hidden mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-green-100 text-green-700';
                    state.classList.add('hidden');
                    cutBtn.className='bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition font-medium';
                    cutBtn.innerText='Cut-Off';
                    document.getElementById('btn-counter-success').disabled=false;document.getElementById('btn-counter-skip').disabled=false;
                    document.getElementById('frmCutOff_trigger').classList.remove('hidden');
                    document.getElementById('frmCutOff_trigger_message').classList.add('hidden');
                }
            }
        }});
    }

    document.getElementById('employee-cut-off').addEventListener('click',function(e){
        e.preventDefault();const btn=this,txt=btn.innerHTML;btn.disabled=true;btn.innerHTML='Processing...';
        $.ajax({url:window.API_BASE+'/cashier',type:'POST',contentType:'application/json',
            data:JSON.stringify({method:'employee-cut-off',id:employeeId}),
            complete:function(){btn.disabled=false;btn.innerHTML=txt;},
            success:function(r){if(r.status==='success')fetchCutOff();}
        });
    });

    // Queue remain
    function queue_remain_get(){
        $.ajax({url:window.API_BASE+'/cashier?counter_queue_remain=true&counter_number='+counterNumber,type:'GET',success:function(r){
            if(r.status==='success'){
                if(r.queue_remain!=null){
                    document.getElementById('cutOff_trigger_notification').classList.remove('hidden');
                    document.getElementById('cutOff_trigger_message').innerText=r.queue_remain+' queue remain.';
                    document.getElementById('cut_off_select').value=r.queue_remain;
                }else{
                    document.getElementById('cutOff_trigger_notification').classList.add('hidden');
                    document.getElementById('cut_off_select').value='null';
                }
            }
        }});
    }
    function queue_remain_set(v){
        $.ajax({url:window.API_BASE+'/cashier',type:'PATCH',contentType:'application/json',
            data:JSON.stringify({counter_number:counterNumber,queue_remain:v}),
            success:function(r){if(r.status==='success')queue_remain_get();}
        });
    }
    document.getElementById('cut_off_select').addEventListener('change',function(){
        const v=this.value;queue_remain_set(v==='null'?null:parseInt(v,10));
    });

    // Daemon loop
    async function daemon(){
        await fetchCutOff();queue_remain_get();
        if(operational){fetchTransaction();fetchStudentTransaction();}
        hideConnectionTimeout();
        setTimeout(daemon,500);
    }
    daemon();
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>
