<?php
include "./../base.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>QR Form | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container about-hero">
        <div class="about-panel shadow-sm mb-4">
            <div class="row g-0 align-items-center about-header">
                <div class="col-12 d-flex justify-content-center py-3">
                    <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid">
                </div>
                <div class="col-12">
                    <div class="about-content">
                        <div class="about-title-row">
                            <h2 class="mb-1"><?php echo $project_name?></h2>
                            <a href="/" class="btn btn-outline-primary btn-sm">Home</a>
                        </div>
                        <p class="about-desc"><?php echo $project_description?></p>
                    </div>
                </div>
            </div>

            <hr class="my-3">

            <div class="mb-3 px-1">
                <h5 class="mb-3">Members</h5>
                <div class="row">
                    <?php
                        $contributors = [
                            ['login'=>'aceday','name'=>'Mark Cedie Buday','avatar'=>'https://avatars.githubusercontent.com/u/68708541','url'=>'https://github.com/aceday'],
                            ['login'=>'kyleengreso','name'=>'Kyle Engreso','avatar'=>'https://avatars.githubusercontent.com/u/125529632','url'=>'https://github.com/kyleengreso'],
                            ['login'=>'NaldCapuno','name'=>'Ronald Jason Capuno','avatar'=>'https://avatars.githubusercontent.com/u/120568365','url'=>'https://github.com/NaldCapuno'],
                            ['login'=>'PaelMacalinao','name'=>'John Rafael Macalinao','avatar'=>'https://avatars.githubusercontent.com/u/141973593','url'=>'https://github.com/PaelMacalinao'],
                            ['name'=>'Dale Gabriel Alie'],
                            ['name'=>'Darwin Velasco'],
                            ['name'=>'John Rafael Macalinao'],
                            ['name'=>'John Whynne Jeresano'],
                            ['name'=>'Ken Roger Domingo'],
                            ['name'=>'Laurence Tabang'],
                            ['name'=>'John Lester Balmaceda'],
                            ['name'=>'Michael John Austria'],
                            ['name'=>'Mimi Apilan'],
                            ['name'=>'Rolando Coloquit Jr.'],
                            ['name'=>'Siradz Sahiddin'],
                        ];
                        shuffle($contributors);
                        $seen = [];
                        $unique = [];
                        foreach($contributors as $item){
                            $dn = !empty($item['name']) ? $item['name'] : (!empty($item['login']) ? $item['login'] : 'Member');
                            $key = mb_strtolower(trim($dn));
                            if(isset($seen[$key])){ continue; }
                            $seen[$key] = true;
                            $unique[] = $item;
                        }
                    ?>
                    <?php if(!empty($unique)): ?>
                        <ul class="list-group list-group-flush w-100 members-grid">
                        <?php foreach($unique as $c): ?>
                            <?php $displayName = !empty($c['name']) ? $c['name'] : (!empty($c['login']) ? $c['login'] : 'Member'); ?>
                            <li class="list-group-item member-item py-2 border-0">
                                <span class="text-dark"><strong><?= htmlspecialchars($displayName) ?></strong></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
