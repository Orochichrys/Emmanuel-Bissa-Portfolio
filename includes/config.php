<?php

    $db_host = getenv('DB_HOST') ?: "localhost";
    $db_name = getenv('DB_NAME') ?: "portfolio";
    $db_user = getenv('DB_USER') ?: "admin";
    $db_pass = getenv('DB_PASS') ?: "admin";
    $db_port = getenv('DB_PORT') ?: "3306";
    $db_char = "utf8mb4";

    try {
        $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$db_char}", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des informations de l'administrateur (prénom et nom)
        try {
            $adminStmt = $pdo->query("SELECT firstname, lastname FROM admin ORDER BY id ASC LIMIT 1");
            $adminUser = $adminStmt->fetch(PDO::FETCH_ASSOC);
            $firstname = !empty($adminUser['firstname']) ? $adminUser['firstname'] : 'Emmanuel';
            $lastname = !empty($adminUser['lastname']) ? $adminUser['lastname'] : 'Bissa';
        } catch (Exception $ex) {
            $firstname = 'Emmanuel';
            $lastname = 'Bissa';
        }
    }
    catch(Exception $e){
        die("Erreur : " . $e->getMessage());
    }
?>