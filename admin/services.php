<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editService = [
    'id' => '',
    'title' => '',
    'description' => '',
    'display_order' => 0
];

// Mode Édition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editService = $fetched;
        } else {
            $_SESSION["error"] = "Service introuvable.";
            redirectTo("services.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $title = security($_POST["title"] ?? "");
        $description = security($_POST["description"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($title) && !empty($description)) {
            $sql = "INSERT INTO services (title, description, display_order, last_update) VALUES (:title, :desc, :order, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "title" => $title,
                "desc" => $description,
                "order" => $display_order
            ]);

            $_SESSION["success"] = "Service ajouté avec succès !";
            redirectTo("services.php");
        } else {
            $_SESSION["error"] = "Le titre et la description sont obligatoires.";
            redirectTo("services.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 2. MODIFICATION (UPDATE)
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"]) && isset($_POST["id"])) {
    try {
        $id = (int) $_POST["id"];
        $title = security($_POST["title"] ?? "");
        $description = security($_POST["description"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($title) && !empty($description)) {
            $sql = "UPDATE services SET title = :title, description = :desc, display_order = :order, last_update = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "title" => $title,
                "desc" => $description,
                "order" => $display_order,
                "id" => $id
            ]);

            $_SESSION["success"] = "Service mis à jour avec succès !";
            redirectTo("services.php");
        } else {
            $_SESSION["error"] = "Le titre et la description sont obligatoires.";
            redirectTo("services.php?action=edit&id=" . $id);
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];
        $sql = "DELETE FROM services WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Service supprimé avec succès !";
        redirectTo("services.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES
try {
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM services")->fetchColumn();
    $services = $pdo->query("SELECT * FROM services ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier le Service" : "Gestion des Services Proposés"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="services.php" method="POST" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editService['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Titre du service :</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($editService['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description :</label>
                <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($editService['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editService['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="services.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Ajouter le service</button>
            <?php endif; ?>
        </form>

        <h3>Liste des services</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service["title"]); ?></td>
                            <td><?php echo htmlspecialchars(mb_strimwidth($service["description"], 0, 60, "...")); ?></td>
                            <td><?php echo htmlspecialchars($service["display_order"]); ?></td>
                            <td>
                                <a href="services.php?action=edit&id=<?php echo $service["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="services.php?action=delete&id=<?php echo $service["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">Aucun service enregistré.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer ce service ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>