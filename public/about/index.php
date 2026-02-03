<?php
include "./../base.php";
$contributors_2024_2025 = [
    ['login'=>'aceday','name'=>'Mark Cedie Buday','url'=>'https://github.com/aceday'],
    ['login'=>'kyleengreso','name'=>'Kyle Cedric Engreso','url'=>'https://github.com/kyleengreso'],
    ['login'=>'NaldCapuno','name'=>'Ronald Jason Capuno','url'=>'https://github.com/NaldCapuno'],
    ['login'=>'PaelMacalinao','name'=>'John Rafael Macalinao','url'=>'https://github.com/PaelMacalinao'],
    ['name'=>'Dale Gabriel Alie'],['name'=>'Darwin Velasco Jr.'],['name'=>'John Wynne Jeresano'],
    ['name'=>'Ken Roger Domingo'],['name'=>'Jay Mar Laurence Tabang'],['name'=>'John Lester Balmaceda'],
    ['name'=>'Michael John Austria'],['name'=>'Mary Mae Apilan'],['name'=>'Rolando Coloquit Jr.'],['name'=>'Siradz Sahiddin'],
];
shuffle($contributors_2024_2025);
$seen=[]; $unique=[];
foreach($contributors_2024_2025 as $c){ $k=mb_strtolower(trim($c['name']??$c['login']??'')); if(!isset($seen[$k])){ $seen[$k]=1; $unique[]=$c; }}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>About | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg min-h-screen">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="pt-24 pb-12 px-4 flex justify-center">
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden max-w-4xl w-full">
            <div class="md:flex">
                <div class="md:w-2/5 bg-psu-orange text-white p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-4 mb-6">
                            <img src="./../asset/images/logo.png" alt="<?php echo $project_name?>" class="w-14">
                            <div>
                                <h2 class="text-2xl font-bold"><?php echo $project_name?></h2>
                                <span class="inline-block bg-white text-psu-orange text-xs px-2 py-1 rounded uppercase font-semibold">Queue Management</span>
                            </div>
                        </div>
                        <p class="text-white/70 mb-6"><?php echo $project_description?></p>
                        <div class="space-y-2 text-sm">
                            <p><i class="bi bi-geo-alt-fill mr-2"></i>Tiniguiban Heights, Puerto Princesa City, Palawan</p>
                            <p><i class="bi bi-envelope-fill mr-2"></i>marcsysman@gmail.com</p>
                            <p><i class="bi bi-telephone-fill mr-2"></i>(+63)909-123-4567</p>
                        </div>
                    </div>
                    <a href="/" class="mt-6 inline-block bg-white text-psu-orange font-semibold py-2 px-4 rounded-lg hover:bg-gray-100 transition">Back to Home</a>
                </div>
                <div class="md:w-3/5 p-8">
                    <h5 class="uppercase text-gray-500 font-semibold text-sm mb-2">About the Project</h5>
                    <p class="text-gray-600 mb-6 text-sm">QR-Line streamlines queue management for Palawan State University by issuing QR-powered tickets, tracking counter performance, and surfacing live transaction statistics.</p>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="border border-psu-orange rounded-xl p-4">
                            <div class="flex items-center gap-2 text-psu-orange font-semibold mb-2"><i class="bi bi-clock-history text-xl"></i>Real-Time Tracking</div>
                            <p class="text-gray-500 text-sm">Live dashboards highlight daily throughput and counter loads.</p>
                        </div>
                        <div class="border border-psu-orange rounded-xl p-4">
                            <div class="flex items-center gap-2 text-psu-orange font-semibold mb-2"><i class="bi bi-qr-code text-xl"></i>QR-Based Tickets</div>
                            <p class="text-gray-500 text-sm">Secure QR tickets via email for quick counter check-ins.</p>
                        </div>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <h5 class="uppercase text-gray-500 font-semibold text-sm">Batch 2025-2026</h5>
                            <span class="bg-yellow-400 text-yellow-900 text-xs px-2 py-0.5 rounded font-semibold">Coming Soon</span>
                        </div>
                        <div class="border border-yellow-400 rounded-xl p-4 text-center bg-yellow-50">
                            <i class="bi bi-people text-4xl text-yellow-500 mb-2 block"></i>
                            <p class="text-gray-500 text-sm">New batch members will be announced soon.</p>
                        </div>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <h5 class="uppercase text-gray-500 font-semibold text-sm">Batch 2024-2025</h5>
                            <span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded font-semibold">Completed</span>
                        </div>
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <?php foreach($unique as $c): $dn=$c['name']??$c['login']??'Member'; ?>
                            <div class="border rounded-xl p-3">
                                <span class="font-semibold block mb-1"><?= htmlspecialchars($dn) ?></span>
                                <?php if(!empty($c['url'])): ?><a href="<?= htmlspecialchars($c['url']) ?>" class="text-psu-orange text-sm hover:underline" target="_blank">View profile</a>
                                <?php else: ?><span class="text-gray-400 text-sm">Contributor</span><?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="bg-orange-50 border border-psu-orange rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <h6 class="font-semibold text-psu-orange">Want to learn more?</h6>
                            <p class="text-gray-500 text-sm">Explore the requester portal or check the dashboard for live stats.</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="/public/requester" class="bg-psu-orange text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-psu-orange-dark transition">Requester Portal</a>
                            <a href="/public/stats" class="border border-psu-orange text-psu-orange px-4 py-2 rounded-lg text-sm font-semibold hover:bg-psu-orange hover:text-white transition">View Stats</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php after_js()?>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>
