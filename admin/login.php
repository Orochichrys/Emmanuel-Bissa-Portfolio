<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT id,email,password_hash FROM admin WHERE email = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["email" => $email]);
    $response = $stmt->fetch(PDO::FETCH_ASSOC);

    if(empty($email) || empty($password)){
        $_SESSION["error"] = "Un ou tous les champs sont vides.";
        redirectTo("login.php");
    }
    else if (!empty($response) && password_verify($password, $response["password_hash"])) {
        $_SESSION["admin_id"] = $response["id"];
        redirectTo("index.php");
    } else {
        $_SESSION["error"] = "Email ou mot de passe incorrect.";
        redirectTo("login.php");
    }
} else {
    // http_response_code(405);
    // die("Erreur 405 : Méthode non autorisée. Seules les requêtes POST sont acceptées.");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Login</title>
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <main class="container">
        <form method="post" action="login.php" class="login-form">

            <div class="form-header">
                <i class="bx bx-briefcase-alt header-icon"></i>
                <h1>Portfolio Manager</h1>
                <p>connectez-vous pour gérer vos actifs</p>
            </div>

            <?php if (isset($_SESSION["error"])): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($_SESSION["error"]); ?>
                </div>
                <?php unset($_SESSION["error"]);?>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Address Email</label>
                <div class="input-wrapper">
                    <i class="bx bx-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="admin@exemple.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot De Passe</label>
                <div class="input-wrapper">
                    <i class="bx bx-lock"></i>
                    <input type="password" name="password" id="password" placeholder="......" required>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Connexion</button>
            </div>

        </form>
    </main>
</body>

</html>