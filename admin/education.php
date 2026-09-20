<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editEducation = [
    'id' => '',
    'school_name' => '',
    'sector' => '',
    'year' => '',
    'description' => '',
    'display_order' => 0
];

// Vérification du mode édition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM educations WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editEducation = $fetched;
        } else {
            $_SESSION["error"] = "Formation introuvable.";
            redirectTo("education.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. GESTION DE L'AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $school_name = security($_POST["school_name"] ?? "");
        $sector = security($_POST["sector"] ?? "");
        $year = security($_POST["year"] ?? "");
        $description = security($_POST["description"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($school_name) && !empty($sector) && !empty($year) && !empty($description)) {
            // Vérification de l'ordre d'affichage
            $sqlCheck = "SELECT COUNT(*) FROM educations WHERE display_order = :order";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute(['order' => $display_order]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION["error"] = "Cet ordre d'affichage est déjà utilisé.";
                redirectTo("education.php");
            }

            $sql = "INSERT INTO educations (school_name, sector, year, description, display_order, last_update)
                    VALUES (:name, :sector, :year, :desc, :order, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $school_name,
                "sector" => $sector,
                "year" => $year,
                "desc" => $description,
                "order" => $display_order
            ]);

            $_SESSION["success"] = "Formation ajoutée avec succès !";
            redirectTo("education.php");
        } else {
            $_SESSION["error"] = "Un ou plusieurs champs obligatoires sont vides.";
            redirectTo("education.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 2. GESTION DE LA MODIFICATION (UPDATE)
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"]) && isset($_POST["id"])) {
    try {
        $id = (int) $_POST["id"];
        $school_name = security($_POST["school_name"] ?? "");
        $sector = security($_POST["sector"] ?? "");
        $year = security($_POST["year"] ?? "");
        $description = security($_POST["description"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($school_name) && !empty($sector) && !empty($year) && !empty($description)) {
            // Vérification de l'ordre d'affichage (exclure l'enregistrement actuel)
            $sqlCheck = "SELECT COUNT(*) FROM educations WHERE display_order = :order AND id != :id";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute(['order' => $display_order, 'id' => $id]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION["error"] = "Cet ordre d'affichage est déjà utilisé par une autre formation.";
                redirectTo("education.php?action=edit&id=" . $id);
            }

            $sql = "UPDATE educations 
                    SET school_name = :name, sector = :sector, year = :year, description = :desc, display_order = :order, last_update = NOW()
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $school_name,
                "sector" => $sector,
                "year" => $year,
                "desc" => $description,
                "order" => $display_order,
                "id" => $id
            ]);

            $_SESSION["success"] = "Formation mise à jour avec succès !";
            redirectTo("education.php");
        } else {
            $_SESSION["error"] = "Un ou plusieurs champs obligatoires sont vides.";
            redirectTo("education.php?action=edit&id=" . $id);
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. GESTION DE LA SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];
        $sql = "DELETE FROM educations WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Formation supprimée avec succès !";
        redirectTo("education.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES DE LA PAGE
try {
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM educations")->fetchColumn();
    $educations = $pdo->query("SELECT * FROM educations ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éducation - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier la Formation" : "Gestion du Parcours Scolaire"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="education.php" method="POST" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editEducation['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="school_name">Nom de l'école / Université :</label>
                <input type="text" name="school_name" id="school_name" value="<?php echo htmlspecialchars($editEducation['school_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="sector">Filière / Domaine d'étude :</label>
                <input type="text" name="sector" id="sector" value="<?php echo htmlspecialchars($editEducation['sector']); ?>" required>
            </div>

            <div class="form-group">
                <label for="year">Année(s) :</label>
                <input type="text" name="year" id="year" placeholder="Ex: 2024 - 2026" value="<?php echo htmlspecialchars($editEducation['year']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description :</label>
                <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($editEducation['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editEducation['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="education.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Ajouter la formation</button>
            <?php endif; ?>
        </form>

        <h3>Formations enregistrées</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>École</th>
                    <th>Filière</th>
                    <th>Année</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($educations)): ?>
                    <?php foreach ($educations as $education): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($education["school_name"]); ?></td>
                            <td><?php echo htmlspecialchars($education["sector"]); ?></td>
                            <td><?php echo htmlspecialchars($education["year"]); ?></td>
                            <td><?php echo htmlspecialchars($education["display_order"]); ?></td>
                            <td>
                                <a href="education.php?action=edit&id=<?php echo $education["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="education.php?action=delete&id=<?php echo $education["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">Aucune formation enregistrée pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer cette formation ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>