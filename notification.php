<?php
session_start();
require 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT r.*, u.pseudo, u.photo_profil 
    FROM shared_reves sr
    JOIN reves r ON sr.reve_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE sr.receiver_id = :receiver_id
    ORDER BY sr.created_at DESC
");
$stmt->execute([':receiver_id' => $user_id]);
$shared_reves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Rêves Partagés avec Vous</h1>
        <div class="list-group">
            <?php if (empty($shared_reves)): ?>
                <div class="alert alert-info">Aucun rêve partagé.</div>
            <?php else: ?>
                <?php foreach ($shared_reves as $reve): ?>
                    <div class="list-group-item">
                        <h5><?php echo htmlspecialchars($reve['titre']); ?></h5>
                        <p><?php echo htmlspecialchars($reve['texte']); ?></p>
                        <small>
                            Partagé par <?php echo htmlspecialchars($reve['pseudo']); ?> 
                            <img src="<?php echo htmlspecialchars($reve['photo_profil']); ?>" alt="Photo de profil" style="width:30px;height:30px;border-radius:50%;">
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
</body>
</html>
