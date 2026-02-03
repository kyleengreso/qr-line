<footer class="bg-white text-gray-700 mt-auto">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <img class="w-10 h-10" src="./../asset/images/logo.png" alt="">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo $project_name; ?></h2>
                </div>
                <p class="text-sm text-gray-500"><?php echo $project_description; ?></p>
            </div>
            <div>
                <h6 class="font-semibold uppercase mb-4 text-sm text-gray-800">Links</h6>
                <?php if (!isset($_COOKIE['token'])): ?>
                <a class="block text-gray-500 hover:text-[rgb(255,110,55)] mb-2" href="/public/auth/login.php">Login</a>
                <?php if ($enable_register_employee) : ?>
                <a class="block text-gray-500 hover:text-[rgb(255,110,55)] mb-2" href="/public/auth/register.php">Register</a>
                <?php endif; ?>
                <a class="block text-gray-500 hover:text-[rgb(255,110,55)] mb-2" href="/public/stats">Transaction Stats</a>
                <a class="block text-gray-500 hover:text-[rgb(255,110,55)] mb-2" href="/public/cashier_stats">Counter Stats</a>
                <?php endif; ?>
                <a class="block text-gray-500 hover:text-[rgb(255,110,55)] mb-2" href="/public/about">About</a>
            </div>
            <div>
                <h6 class="font-semibold uppercase mb-4 text-sm text-gray-800">Contact</h6>
                <p class="text-sm mb-2 text-gray-500"><i class="bi bi-geo-alt-fill mr-2"></i><?php echo $project_address?></p>
                <p class="text-sm mb-2 text-gray-500"><i class="bi bi-envelope-at-fill mr-2"></i><?php echo $project_email?></p>
                <p class="text-sm text-gray-500"><i class="bi bi-telephone-fill mr-2"></i><?php echo $project_phone?></p>
            </div>
            <div>
                <h6 class="font-semibold uppercase mb-4 text-sm text-gray-800">Follow us</h6>
                <div class="flex gap-3">
                    <a class="text-3xl text-gray-800 hover:text-[rgb(255,110,55)]" href="#!"><i class="bi bi-facebook"></i></a>
                    <a class="text-3xl text-gray-800 hover:text-[rgb(255,110,55)]" href="#!"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-[rgb(255,110,55)] text-center py-4 text-sm text-white">&copy; <?php echo project_year()?> <?php echo $project_name?>, All Rights Reserved.</div>
</footer>
