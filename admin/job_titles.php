<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editJob = [
    'id' => '',
    'title' => '',
    'display_order' => 0
];

// Fonction d'aide pour s'assurer qu'au moins une ligne d'information existe pour la clé étrangère
function getInformationId(PDO $pdo) {
    $stmt = $pdo->query("SELECT id FROM informations LIMIT 1");
    $infoId = $stmt->fetchColumn();
    if (!$infoId) {
        $pdo->exec("INSERT INTO informations (description, last_update) VALUES ('', NOW())");
        return (int) $pdo->lastInsertId();
    }
    return (int) $infoId;
}

// Verification du mode edition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM job_titles WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editJob = $fetched;
        } else {
            $_SESSION["error"] = "Titre introuvable.";
            redirectTo("job_titles.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. GESTION DE L'AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $title = security($_POST["title"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($title)) {
            $infoId = getInformationId($pdo);

            $sql = "INSERT INTO job_titles (id_information, title, display_order) VALUES (:info_id, :title, :order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "info_id" => $infoId,
                "title" => $title,
                "order" => $display_order
            ]);

            $_SESSION["success"] = "Titre de poste ajouté avec succès !";
            redirectTo("job_titles.php");
        } else {
            $_SESSION["error"] = "Le titre du poste ne peut pas être vide.";
            redirectTo("job_titles.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 2. GESTION DE LA MODIFICATION (UPDATE)
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"]) && isset($_POST["id"])) {
    try {
        $id = (int) $_POST["id"];
        $title = security($_POST["title"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($title)) {
            $sql = "UPDATE job_titles SET title = :title, display_order = :order WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "title" => $title,
                "order" => $display_order,
                "id" => $id
            ]);

            $_SESSION["success"] = "Titre de poste mis à jour avec succès !";
            redirectTo("job_titles.php");
        } else {
            $_SESSION["error"] = "Le titre du poste ne peut pas être vide.";
            redirectTo("job_titles.php?action=edit&id=" . $id);
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. GESTION DE LA SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];
        $sql = "DELETE FROM job_titles WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Titre de poste supprimé avec succès !";
        redirectTo("job_titles.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES
try {
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM job_titles")->fetchColumn();
    $jobs = $pdo->query("SELECT * FROM job_titles ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Titles - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier le Titre de Poste" : "Gestion des Titres de Poste"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="job_titles.php" method="POST" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editJob['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Titre du poste :</label>
                <input type="text" name="title" id="title" placeholder="Ex: Développeur Web" value="<?php echo htmlspecialchars($editJob['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editJob['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="job_titles.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Ajouter le titre</button>
            <?php endif; ?>
        </form>

        <h3>Liste des titres enregistrés</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($job["id"]); ?></td>
                            <td><?php echo htmlspecialchars($job["title"]); ?></td>
                            <td><?php echo htmlspecialchars($job["display_order"]); ?></td>
                            <td>
                                <a href="job_titles.php?action=edit&id=<?php echo $job["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="job_titles.php?action=delete&id=<?php echo $job["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">Aucun titre enregistré.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer ce titre ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>