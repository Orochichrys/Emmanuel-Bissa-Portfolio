<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/very_session.php";

function countRows(PDO $pdo, string $table){
    $stmt = $pdo -> query("SELECT COUNT(*) FROM ".$table);
    return (int) $stmt -> fetchColumn();
}

try{
    $countProjects = countRows($pdo,"projets");
    $countServices = countRows($pdo,"services");
    $countCertificats = countRows($pdo,"certificats");
    $countEducations = countRows($pdo,"educations");
    $countSkills = countRows($pdo,"skills");
}
catch(Exception $e){
    die("Erreur :". $e -> getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time();?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php";?>

    <main>

        <div class="grid-container">
            <div class="item">
                <i class="bx bx-code-alt"></i>
                <h2>Projects</h2>
                <p><?php echo $countProjects?></p>
            </div>
            <div class="item">
                <i class="bx bx-bar-chart-alt-2"></i>
                <h2>Compétences</h2>
                <p><?php echo $countSkills?></p>
            </div>
            <div class="item">
                <i class="bx bx-wrench"></i>
                <h2>Services</h2>
                <p><?php echo $countServices?></p>
            </div>
            <div class="item">
                <i class="bx bx-certification"></i>
                <h2>Certificats</h2>
                <p><?php echo $countCertificats?></p>
            </div>
            <div class="item">
                <i class="bx bx-book"></i>
                <h2>Educations</h2>
                <p><?php echo $countEducations?></p>
            </div>
        </div>

    </main>

</body>

</html>