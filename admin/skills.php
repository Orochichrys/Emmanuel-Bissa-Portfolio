<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editSkill = [
    'id' => '',
    'name' => '',
    'percentage' => 80,
    'display_order' => 0
];

// Mode Édition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editSkill = $fetched;
        } else {
            $_SESSION["error"] = "Compétence introuvable.";
            redirectTo("skills.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $name = security($_POST["name"] ?? "");
        $percentage = isset($_POST["percentage"]) ? (int) security($_POST["percentage"]) : 80;
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if ($percentage < 0) $percentage = 0;
        if ($percentage > 100) $percentage = 100;

        if (!empty($name)) {
            $sql = "INSERT INTO skills (name, percentage, display_order, last_update) VALUES (:name, :percentage, :order, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $name,
                "percentage" => $percentage,
                "order" => $display_order
            ]);

            $_SESSION["success"] = "Compétence ajoutée avec succès !";
            redirectTo("skills.php");
        } else {
            $_SESSION["error"] = "Le nom de la compétence ne peut pas être vide.";
            redirectTo("skills.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 2. MODIFICATION (UPDATE)
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"]) && isset($_POST["id"])) {
    try {
        $id = (int) $_POST["id"];
        $name = security($_POST["name"] ?? "");
        $percentage = isset($_POST["percentage"]) ? (int) security($_POST["percentage"]) : 80;
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if ($percentage < 0) $percentage = 0;
        if ($percentage > 100) $percentage = 100;

        if (!empty($name)) {
            $sql = "UPDATE skills SET name = :name, percentage = :percentage, display_order = :order, last_update = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $name,
                "percentage" => $percentage,
                "order" => $display_order,
                "id" => $id
            ]);

            $_SESSION["success"] = "Compétence mise à jour avec succès !";
            redirectTo("skills.php");
        } else {
            $_SESSION["error"] = "Le nom de la compétence ne peut pas être vide.";
            redirectTo("skills.php?action=edit&id=" . $id);
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];
        $sql = "DELETE FROM skills WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Compétence supprimée avec succès !";
        redirectTo("skills.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES
try {
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM skills")->fetchColumn();
    $skills = $pdo->query("SELECT * FROM skills ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compétences - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier la Compétence" : "Gestion des Compétences"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="skills.php" method="POST" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editSkill['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Nom de la compétence (ex: HTML / CSS, REACT...) :</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($editSkill['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="percentage">Pourcentage de maîtrise (0 à 100%) :</label>
                <input type="number" name="percentage" id="percentage" min="0" max="100" value="<?php echo htmlspecialchars($editSkill['percentage']); ?>" required>
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editSkill['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="skills.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Ajouter la compétence</button>
            <?php endif; ?>
        </form>

        <h3>Liste des compétences</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Compétence</th>
                    <th>Niveau (%)</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($skills)): ?>
                    <?php foreach ($skills as $skill): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($skill["name"]); ?></td>
                            <td><strong><?php echo htmlspecialchars($skill["percentage"]); ?>%</strong></td>
                            <td><?php echo htmlspecialchars($skill["display_order"]); ?></td>
                            <td>
                                <a href="skills.php?action=edit&id=<?php echo $skill["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="skills.php?action=delete&id=<?php echo $skill["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">Aucune compétence enregistrée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer cette compétence ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>
