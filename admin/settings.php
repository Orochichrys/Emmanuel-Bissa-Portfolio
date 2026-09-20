<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

// Récupérer la clé Web3Forms existante
try {
    $stmtKey = $pdo->query("SELECT api_key FROM web3forms ORDER BY id ASC LIMIT 1");
    $web3Key = $stmtKey->fetchColumn() ?: "";
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}

// 1. MISE À JOUR DE LA CLÉ WEB3FORMS
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_web3forms"])) {
    try {
        $apiKey = security($_POST["api_key"] ?? "");

        $count = $pdo->query("SELECT COUNT(*) FROM web3forms")->fetchColumn();
        if ($count > 0) {
            $stmt = $pdo->prepare("UPDATE web3forms SET api_key = :key, last_update = NOW()");
            $stmt->execute(['key' => $apiKey]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO web3forms (api_key, last_update) VALUES (:key, NOW())");
            $stmt->execute(['key' => $apiKey]);
        }

        $_SESSION["success"] = "Clé d'API Web3Forms mise à jour avec succès !";
        redirectTo("settings.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 2. MODIFICATION DU MOT DE PASSE ADMINISTRATEUR
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_password"])) {
    try {
        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";
        $adminId = $_SESSION["admin_id"];

        if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
            if ($newPassword !== $confirmPassword) {
                $_SESSION["error"] = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
                redirectTo("settings.php");
            }

            // Récupération du mot de passe actuel en BDD
            $stmtAdmin = $pdo->prepare("SELECT password_hash FROM admin WHERE id = :id");
            $stmtAdmin->execute(['id' => $adminId]);
            $adminData = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

            if ($adminData && password_verify($currentPassword, $adminData["password_hash"])) {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmtUpdate = $pdo->prepare("UPDATE admin SET password_hash = :hash WHERE id = :id");
                $stmtUpdate->execute(['hash' => $newHash, 'id' => $adminId]);

                $_SESSION["success"] = "Mot de passe modifié avec succès !";
                redirectTo("settings.php");
            } else {
                $_SESSION["error"] = "Le mot de passe actuel est incorrect.";
                redirectTo("settings.php");
            }
        } else {
            $_SESSION["error"] = "Veuillez remplir tous les champs du mot de passe.";
            redirectTo("settings.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2>Paramètres de l'application</h2>

        <?php displayAlerts(); ?>

        <h3>1. Clé API Formulaire de contact (Web3Forms)</h3>
        <form action="settings.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="api_key">Clé d'API Web3Forms :</label>
                <input type="text" name="api_key" id="api_key" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" value="<?php echo htmlspecialchars($web3Key); ?>">
            </div>
            <button type="submit" name="update_web3forms" class="btn">Mettre à jour la clé</button>
        </form>

        <hr style="margin: 30px 0; border: 1px solid #222;">

        <h3>2. Modification du mot de passe Administrateur</h3>
        <form action="settings.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="current_password">Mot de passe actuel :</label>
                <input type="password" name="current_password" id="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit" name="change_password" class="btn">Changer le mot de passe</button>
        </form>
    </main>

</body>

</html>