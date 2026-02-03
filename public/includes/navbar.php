<?php
function token_prop($token, $prop) {
    if (is_object($token) && isset($token->{$prop})) return $token->{$prop};
    if (is_array($token) && isset($token[$prop])) return $token[$prop];
    return null;
}
$token_role_raw = token_prop($token, 'role_type');
if (!$token_role_raw && isset($_COOKIE['role_type'])) $token_role_raw = $_COOKIE['role_type'];
$token_role = is_string($token_role_raw) ? strtolower($token_role_raw) : null;
$is_admin = in_array($token_role, ['admin','administrator','superadmin'], true);
$is_employee = in_array($token_role, ['employee','cashier','attendant'], true);
$token_username = token_prop($token, 'username');
?>
<nav class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
                <?php if ($is_admin) : ?>
                <button id="sidebar-toggle" class="p-2 text-gray-600 hover:text-[rgb(255,110,55)] hover:bg-gray-100 rounded-md transition">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <?php endif; ?>
                <a href="/public" class="flex items-center gap-3">
                    <img src="./../asset/images/logo_blk.png" alt="Logo" class="h-9 w-9">
                    <span class="hidden sm:block font-semibold text-gray-800 text-lg"><?php echo $project_name_full; ?></span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($token_username) : ?>
                <div class="hidden md:flex items-center gap-2 text-gray-600">
                    <i class="bi bi-person-circle text-lg"></i>
                    <span class="text-sm font-medium"><?php echo htmlentities($token_username); ?></span>
                </div>
                <?php endif; ?>
                <div class="hidden md:flex items-center gap-2 text-gray-500 text-sm" id="navbar-clock">
                    <i class="bi bi-clock"></i>
                    <span id="current-time" class="font-medium"></span>
                </div>
                <button id="btn-logout-1" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-[rgb(255,110,55)] hover:bg-[rgb(230,60,20)] rounded-md transition">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="hidden sm:inline">Logout</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<?php if ($is_admin) : ?>
<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden transition-opacity"></div>
<aside id="sidebar" class="fixed top-0 left-0 h-full w-72 bg-white border-r border-gray-200 z-50 -translate-x-full transition-transform duration-300 flex flex-col shadow-xl">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 h-16 border-b border-gray-200">
        <div class="flex items-center gap-2">
            <img src="./../asset/images/logo_blk.png" alt="Logo" class="h-8 w-8">
            <span class="font-bold text-gray-800 text-lg"><?php echo $project_name?></span>
        </div>
        <button id="sidebar-close" class="p-1 text-gray-400 hover:text-gray-600 rounded">
            <i class="bi bi-x-lg text-xl"></i>
        </button>
    </div>
    <!-- User Info -->
    <div class="px-4 py-4 border-b border-gray-100 bg-gray-50">
        <div class="flex items-center gap-3">
            <div class="relative">
                <img src="./../asset/images/user_icon.png" alt="User" class="w-12 h-12 rounded-full border-2 border-[rgb(255,110,55)]">
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
            </div>
            <div>
                <p class="font-semibold text-gray-800"><?php echo htmlentities($token->username); ?></p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
    </div>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/public/admin') !== false): ?>
    <!-- Cut Off Button -->
    <div class="px-4 py-3 border-b border-gray-100">
        <button id="employee-cut-off" class="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-md hover:bg-red-50 transition">
            <i class="bi bi-power"></i>Cut Off
        </button>
    </div>
    <?php endif; ?>
    <!-- Navigation -->
    <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-1 px-3">
            <li>
                <a href="/public/admin" class="nav-link flex items-center gap-3 px-3 py-2.5 text-gray-700 rounded-md hover:bg-[rgb(255,110,55)] hover:text-white transition">
                    <i class="bi bi-house-door text-lg w-5 text-center"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/public/employees" class="nav-link flex items-center gap-3 px-3 py-2.5 text-gray-700 rounded-md hover:bg-[rgb(255,110,55)] hover:text-white transition">
                    <i class="bi bi-people text-lg w-5 text-center"></i>
                    <span class="font-medium">Employees</span>
                </a>
            </li>
            <li>
                <a href="/public/counters" class="nav-link flex items-center gap-3 px-3 py-2.5 text-gray-700 rounded-md hover:bg-[rgb(255,110,55)] hover:text-white transition">
                    <i class="bi bi-diagram-3 text-lg w-5 text-center"></i>
                    <span class="font-medium">Counters</span>
                </a>
            </li>
            <li>
                <a href="/public/transaction_history" class="nav-link flex items-center gap-3 px-3 py-2.5 text-gray-700 rounded-md hover:bg-[rgb(255,110,55)] hover:text-white transition">
                    <i class="bi bi-clock-history text-lg w-5 text-center"></i>
                    <span class="font-medium">Transactions</span>
                </a>
            </li>
            <li>
                <a href="/public/schedule" class="nav-link flex items-center gap-3 px-3 py-2.5 text-gray-700 rounded-md hover:bg-[rgb(255,110,55)] hover:text-white transition">
                    <i class="bi bi-calendar-check text-lg w-5 text-center"></i>
                    <span class="font-medium">Schedule</span>
                </a>
            </li>
        </ul>
    </nav>
    <!-- Footer -->
    <div class="border-t border-gray-200 p-4">
        <button id="btn-logout-2" class="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100 transition">
            <i class="bi bi-box-arrow-right"></i>Logout
        </button>
        <p class="text-center text-xs text-gray-400 mt-3">&copy; <?php echo project_year()?> <?php echo $project_name?></p>
    </div>
</aside>
<script>
(function(){
    const sb=document.getElementById('sidebar'),ov=document.getElementById('sidebar-overlay'),tg=document.getElementById('sidebar-toggle'),cl=document.getElementById('sidebar-close');
    function open(){sb.classList.remove('-translate-x-full');ov.classList.remove('hidden');}
    function close(){sb.classList.add('-translate-x-full');ov.classList.add('hidden');}
    tg&&tg.addEventListener('click',open);cl&&cl.addEventListener('click',close);ov&&ov.addEventListener('click',close);
    document.querySelectorAll('.nav-link').forEach(l=>{
        if(location.pathname.includes(l.getAttribute('href'))){
            l.classList.remove('text-gray-700');
            l.classList.add('bg-[rgb(255,110,55)]','text-white');
        }
    });
})();
</script>
<?php endif; ?>
<script src="./../asset/js/navbar.js"></script>
