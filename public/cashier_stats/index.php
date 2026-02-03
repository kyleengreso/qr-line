<?php include "./../base.php" ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Counter Stats | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg min-h-screen">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="pt-24 px-4 pb-12 flex justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-3xl w-full">
            <h1 class="text-2xl font-bold text-center mb-6">Counter Stats</h1>
            <div class="bg-gray-100 rounded-xl p-6 text-center mb-6">
                <span class="block text-lg font-semibold" id="requester_number_label">Current Number</span>
                <span class="text-4xl font-bold text-psu-orange" id="requester_number_latest">--</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4" id="counters-list"></div>
        </div>
    </div>
    <?php after_js()?>
    <?php include_once "./../includes/footer.php"; ?>
    <script>
    const endpointHost = window.endpointHost;
    function fetchCounters(){
        if(!endpointHost) return;
        $.ajax({url:endpointHost.replace(/\/$/,'')+'/api/counter_current_number',type:'GET',
            success:function(r){
                const list=document.getElementById('counters-list');list.innerHTML='';
                if(r.status==='success'){
                    document.getElementById('requester_number_label').className='block text-lg font-semibold';
                    document.getElementById('requester_number_label').textContent='Current Number';
                    document.getElementById('requester_number_latest').textContent=r.requester||'--';
                    (r.counters||[]).forEach(c=>{
                        list.innerHTML+=`<div class="bg-white border rounded-xl p-4 flex justify-between items-center">
                            <span class="font-bold">Counter ${c.counterNumber}</span>
                            <span class="font-bold text-psu-orange">${c.queue_number||'No Queue'}</span>
                        </div>`;
                    });
                }else{
                    document.getElementById('requester_number_label').className='block text-lg font-semibold text-red-500';
                    document.getElementById('requester_number_label').textContent='Notice';
                    document.getElementById('requester_number_latest').textContent=r.message||'Error';
                }
            }
        });
    }
    fetchCounters();
    setInterval(fetchCounters,5000);
    </script>
</body>
</html>
