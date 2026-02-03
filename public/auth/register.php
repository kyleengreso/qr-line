<?php
include_once __DIR__ . '/../base.php';
restrictCheckLoggedIn();
if (!$enable_register_employee) { header("Location: /public/auth/login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Request Account | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="bg-[rgb(255,110,55)] px-8 py-6 text-center">
                <img src="./../asset/images/logo.png" alt="<?php echo $project_name?>" class="mx-auto w-16 mb-3">
                <h1 class="text-xl font-bold text-white"><?php echo $project_name?></h1>
                <p class="text-white/80 text-sm">Request an account</p>
            </div>
            <div class="p-8">
                <form method="POST" id="frmRegister">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" id="username" placeholder="Enter username" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="email" placeholder="Enter email" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password" placeholder="Enter password" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3 bg-[rgb(255,110,55)] text-white font-semibold rounded-lg hover:bg-[rgb(230,60,20)] transition">Request Account</button>
                    <p class="text-center mt-6 text-sm"><a class="text-[rgb(255,110,55)] hover:underline" href="login.php">Back to Login</a></p>
                </form>
            </div>
        </div>
        <p class="text-center text-gray-500 text-sm mt-6">&copy; <?php echo project_year()?> <?php echo $project_name?></p>
    </div>
    <?php after_js()?>
    <script>
    const endpointHost = window.endpointHost;
    $(document).ready(function() {
        function msg_ok(m){ $('#frmRegister').find('.msg').remove(); $('#frmRegister').prepend('<div class="msg mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">'+m+'</div>'); }
        function msg_err(m){ $('#frmRegister').find('.msg').remove(); $('#frmRegister').prepend('<div class="msg mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">'+m+'</div>'); }
        $('#frmRegister').on('submit',function(e){
            e.preventDefault();
            var u=$('#username').val(),p=$('#password').val(),cp=$('#confirm_password').val(),em=$('#email').val();
            if(p!==cp) return msg_err('Passwords do not match');
            if(!(endpointHost&&endpointHost.length>0)) return msg_err('Registration service unavailable');
            $.ajax({
                url:endpointHost.replace(/\/$/,'')+'/api/register', type:'POST',
                data:JSON.stringify({username:u,password:p,email:em}), contentType:'application/json', dataType:'json', xhrFields:{withCredentials:true},
                success:function(r){ if(r.status==='success'){ msg_ok(r.message); setTimeout(function(){window.location.href='./login.php';},1000); } else msg_err(r.message||'Registration failed'); },
                error:function(x){ msg_err(x.responseJSON&&x.responseJSON.message||'Registration failed'); }
            });
        });
    });
    </script>
</body>
</html>
