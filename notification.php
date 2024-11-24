<?php
$host = 'host';
$db = 'dbname';
$user = 'username';
$pass = 'password';
/* Il est recommandé d'utiliser une variable de connexion dans un fichier séparé pour une meilleure organisation. */
/*Ce fichier a été créé pour un projet personnel, et aucune garantie de sécurité n'est assurée.*/
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    session_start();
    $current_user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM shared_reves WHERE receiver_id = :receiver_id");
        $stmt->execute(['receiver_id' => $current_user_id]);
        echo json_encode(['success' => true]);
        exit; 
    }

    $stmt = $pdo->prepare("
        SELECT sr.*, r.titre, r.image_reve, u.pseudo, u.photo_profil
        FROM shared_reves sr
        JOIN reves r ON sr.reve_id = r.id
        JOIN users u ON sr.sender_id = u.id
        WHERE sr.receiver_id = :receiver_id
        ORDER BY sr.created_at DESC
    ");
    $stmt->execute(['receiver_id' => $current_user_id]);
    $shared_dreams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_follow = $pdo->prepare("
        SELECT f.*, u.pseudo AS follower_pseudo, u.photo_profil AS follower_photo
        FROM friends f
        JOIN users u ON f.user_id = u.id
        WHERE f.friend_id = :friend_id
        ORDER BY f.followed_at DESC
    ");
    $stmt_follow->execute(['friend_id' => $current_user_id]);
    $followers = $stmt_follow->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="format-detection" content="telephone=no">

    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#222032" media="(prefers-color-scheme: dark)">

    <title>DreamShare.fr – Partage et découverte de rêves | Raconte et explore des rêves fascinants</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/remixicon.min.css">
    <link rel="stylesheet" href="assets/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/vendors/zuck_stories/zuck.min.css">
    <link rel="manifest" href="_manifest.json" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div id="wrapper">
        <div id="content">
            <header class="default heade-sticky">
                <div class="un-title-page go-back">
                    <a href="homepage.php" class="icon">
                        <i class="ri-arrow-drop-left-line"></i>
                    </a>
                    <h1>Notifications</h1>
                </div>
                <div class="un-block-right">
                    <div class="un-notification">
                        <a href="#" aria-label="activity" id="delete-notifications">
                            <i class="ri-delete-bin-fill"></i>
                        </a>
                        <?php if (count($shared_dreams) > 0): ?>
                            <span class="bull-activity"></span>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            
            <div class="space-sticky"></div>
            
            <section class="margin-t-20 un-activity-page">
                <div class="content-activity">
                    <div class="head">
                        <div class="title">Nouveaux Partages</div>
                    </div>
                    <div class="body">
                        <ul class="nav flex-column">
                        <?php foreach ($shared_dreams as $dream): ?>
<li class="nav-item">
    <a href="dreams.php?id=<?php echo $dream['reve_id']; ?>" class="nav-link">
        <div class="item-user-img">
            <div class="wrapper-image">
                <picture>
                    <source srcset="<?= htmlspecialchars($dream['photo_profil']); ?>" type="image/webp">
                    <img class="avt-img" src="<?= htmlspecialchars($dream['photo_profil']); ?>" alt="Profil de <?= htmlspecialchars($dream['pseudo']); ?>">
                </picture>
            </div>
            <div class="txt-user">
                <h5 class="text-overflow"><span class="color-text"><?= htmlspecialchars($dream['pseudo']); ?> a partagé un rêve</span></h5>
                <h5 class="size-13 weight-600"><?= htmlspecialchars($dream['titre']); ?></h5>
                
                <?php if (!empty($dream['note'])): ?>
                    <h5 class="text-overflow"><span class="color-text">Note : <?= htmlspecialchars($dream['note']); ?></span></h5>
                <?php endif; ?>

                <p><?= date('d M Y, H:i', strtotime($dream['created_at'])); ?></p>
            </div>
        </div>
        <div class="other-option">
            <picture>
                <source srcset="<?= htmlspecialchars($dream['image_reve']); ?>" type="image/webp">
                <img class="img-activiy-sm" src="<?= htmlspecialchars($dream['image_reve']); ?>" alt="Image du rêve">
            </picture>
        </div>
    </a>
</li>
<?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="head mt-4">
                        <div class="title">Nouveaux Amis</div>
                    </div>
                    <div class="body">
                        <ul class="nav flex-column">
                        <?php foreach ($followers as $follower): ?>
<li class="nav-item">
    <div class="nav-link">
        <div class="item-user-img">
            <div class="wrapper-image">
                <a href="profil.php?id=<?= htmlspecialchars($follower['user_id']); ?>">
                    <picture>
                        <source srcset="<?= htmlspecialchars($follower['follower_photo']); ?>" type="image/webp">
                        <img class="avt-img" src="<?= htmlspecialchars($follower['follower_photo']); ?>" alt="Profil de <?= htmlspecialchars($follower['follower_pseudo']); ?>">
                    </picture>
                </a>
            </div>
            <div class="txt-user">
                <h5 class="text-overflow"><span class="color-text"><?= htmlspecialchars($follower['follower_pseudo']); ?> vous suit </span></h5>
                <p><?= date('d M Y, H:i', strtotime($follower['followed_at'])); ?></p>
            </div>
        </div>
    </div>
</li>
<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        $('#delete-notifications').on('click', function(e) {
            e.preventDefault(); 

            if (confirm('Voulez-vous vraiment supprimer toutes les notifications ?')) {
                $.ajax({
                    url: '', 
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Une erreur est survenue lors de la suppression des notifications.');
                        }
                    },
                    error: function() {
                        alert('Une erreur est survenue lors de la demande.');
                    }
                });
            }
        });
    });
</script>

</body>
</html>

