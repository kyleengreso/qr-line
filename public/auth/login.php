<?php
include_once __DIR__ . '/../base.php';
restrictCheckLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Login | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="bg-[rgb(255,110,55)] px-8 py-6 text-center">
                <img src="./../asset/images/logo.png" alt="<?php echo $project_name?>" class="mx-auto w-16 mb-3">
                <h1 class="text-xl font-bold text-white"><?php echo $project_name?></h1>
                <p class="text-white/80 text-sm">Sign in to your account</p>
            </div>
            <div class="p-8">
                <form method="POST" id="frmLogIn" novalidate>
                    <?php if (session_status() == PHP_SESSION_NONE) session_start();
                    if (isset($_SESSION['auth_notice'])) { echo '<div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-sm">' . htmlentities($_SESSION['auth_notice']) . '</div>'; unset($_SESSION['auth_notice']); } ?>
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" id="username" placeholder="Enter username" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password" placeholder="Enter password" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3 bg-[rgb(255,110,55)] text-white font-semibold rounded-lg hover:bg-[rgb(230,60,20)] transition">Sign In</button>
                    <div class="mt-6 text-center text-sm">
                        <a class="text-[rgb(255,110,55)] hover:underline" href="forgot_password.php">Forgot Password?</a>
                        <?php if ($enable_register_employee) : ?>
                        <span class="mx-2 text-gray-300">|</span>
                        <a class="text-[rgb(255,110,55)] hover:underline" href="register.php">Request Account</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-center text-gray-500 text-sm mt-6">&copy; <?php echo project_year()?> <?php echo $project_name?></p>
    </div>
    <?php after_js()?>
    <script>
    const endpointHost = window.endpointHost;
    $(document).ready(function() {
        function msg_ok(m){ $('#frmLogIn').find('.msg').remove(); $('#frmLogIn').prepend('<div class="msg mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">'+m+'</div>'); }
        function msg_err(m){ $('#frmLogIn').find('.msg').remove(); $('#frmLogIn').prepend('<div class="msg mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">'+m+'</div>'); }
        function parseJwt(t){ try{var p=t.split('.')[1];while(p.length%4)p+='=';return JSON.parse(atob(p.replace(/-/g,'+').replace(/_/g,'/')));}catch(e){return null;}}

        $('#frmLogIn').on('submit',function(e){
            e.preventDefault();
            var u=$('#username').val(),p=$('#password').val();
            if(!(endpointHost&&endpointHost.length>0)) return msg_err('Authentication service unavailable');
            $.ajax({
                url:endpointHost.replace(/\/$/,'')+'/api/login',type:'POST',
                data:JSON.stringify({username:u,password:p,device_name:navigator.userAgent}),
                contentType:'application/json',dataType:'json',xhrFields:{withCredentials:true},
                success:function(r){
                    if(r.status==='success'){
                        msg_ok(r.message);
                        var token=r.data&&r.data.token,role=r.data&&r.data.role;
                        if(token){
                            var payload=parseJwt(token);if(payload&&!role)role=payload.role_type||payload.role||null;
                            $.ajax({url:'/public/includes/system_auth.php?action=set_token',type:'POST',
                                data:JSON.stringify({token:token,role:role}),contentType:'application/json',dataType:'json',
                                success:function(){
                                    try{document.cookie='token='+encodeURIComponent(token)+';path=/;max-age='+(86400*30);}catch(e){}
                                    if(role==='admin')window.location.href='/public/admin/index.php';
                                    else if(role==='employee')window.location.href='/public/employee/index.php';
                                    else window.location.reload();
                                },error:function(){window.location.reload();}
                            });
                        }else setTimeout(function(){window.location.reload();},1000);
                    }else msg_err(r.message||'Login failed');
                },
                error:function(xhr){
                    var m='Login failed';
                    try{if(xhr.responseJSON&&xhr.responseJSON.message)m=xhr.responseJSON.message;}catch(e){}
                    if(xhr.status===401)m='Invalid username or password';
                    else if(xhr.status===0)m='Cannot reach server';
                    msg_err(m);
                }
            });
        });
    });
    </script>
</body>
</html>