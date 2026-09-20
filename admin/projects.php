<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editProject = [
    'id' => '',
    'title' => '',
    'icon' => 'bx bx-laptop',
    'description' => '',
    'code_source_link' => '',
    'action_link' => '',
    'action_name' => 'Visiter',
    'languages' => '',
    'display_order' => 0
];

// Helper pour enregistrer les langages d'un projet
function saveProjectLanguages(PDO $pdo, int $projectId, string $languagesInput) {
    // Supprimer les anciens langages
    $stmtDel = $pdo->prepare("DELETE FROM langage_projet WHERE id_projet = :id");
    $stmtDel->execute(['id' => $projectId]);

    // Insérer les nouveaux langages
    $langs = array_filter(array_map('trim', explode(',', $languagesInput)));
    if (!empty($langs)) {
        $stmtIns = $pdo->prepare("INSERT INTO langage_projet (id_projet, langage) VALUES (:id_projet, :langage)");
        foreach ($langs as $lang) {
            if (!empty($lang)) {
                $stmtIns->execute([
                    'id_projet' => $projectId,
                    'langage' => security($lang)
                ]);
            }
        }
    }
}

// Mode Édition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editProject = $fetched;

            // Récupération des langages associés
            $stmtLangs = $pdo->prepare("SELECT langage FROM langage_projet WHERE id_projet = :id");
            $stmtLangs->execute(['id' => $editId]);
            $langsArr = $stmtLangs->fetchAll(PDO::FETCH_COLUMN);
            $editProject['languages'] = implode(', ', $langsArr);
        } else {
            $_SESSION["error"] = "Projet introuvable.";
            redirectTo("projects.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $title = security($_POST["title"] ?? "");
        $icon = security($_POST["icon"] ?? "bx bx-laptop");
        $description = security($_POST["description"] ?? "");
        $code_source_link = security($_POST["code_source_link"] ?? "");
        $action_link = security($_POST["action_link"] ?? "");
        $action_name = security($_POST["action_name"] ?? "Visiter");
        $languages = $_POST["languages"] ?? "";
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($title) && !empty($description) && !empty($code_source_link) && !empty($action_link)) {
            $sql = "INSERT INTO projets (icon, title, description, code_source_link, action_link, action_name, display_order, last_update)
                    VALUES (:icon, :title, :desc, :code_link, :action_link, :action_name, :order, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "icon" => $icon,
                "title" => $title,
                "desc" => $description,
                "code_link" => $code_source_link,
                "action_link" => $action_link,
                "action_name" => $action_name,
                "order" => $display_order
            ]);

            $projectId = (int) $pdo->lastInsertId();
            saveProjectLanguages($pdo, $projectId, $languages);

            $_SESSION["success"] = "Projet créé avec succès !";
            redirectTo("projects.php");
        } else {
            $_SESSION["error"] = "Veuillez remplir tous les champs obligatoires.";
            redirectTo("projects.php");
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
        $icon = security($_POST["icon"] ?? "bx bx-laptop");
        $description = security($_POST["description"] ?? "");
        $code_source_link = security($_POST["code_source_link"] ?? "");
        $action_link = security($_POST["action_link"] ?? "");
        $action_name = security($_POST["action_name"] ?? "Visiter");
        $languages = $_POST["languages"] ?? "";
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if (!empty($title) && !empty($description) && !empty($code_source_link) && !empty($action_link)) {
            $sql = "UPDATE projets 
                    SET icon = :icon, title = :title, description = :desc, code_source_link = :code_link, 
                        action_link = :action_link, action_name = :action_name, display_order = :order, last_update = NOW()
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "icon" => $icon,
                "title" => $title,
                "desc" => $description,
                "code_link" => $code_source_link,
                "action_link" => $action_link,
                "action_name" => $action_name,
                "order" => $display_order,
                "id" => $id
            ]);

            saveProjectLanguages($pdo, $id, $languages);

            $_SESSION["success"] = "Projet mis à jour avec succès !";
            redirectTo("projects.php");
        } else {
            $_SESSION["error"] = "Veuillez remplir tous les champs obligatoires.";
            redirectTo("projects.php?action=edit&id=" . $id);
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];
        $sql = "DELETE FROM projets WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Projet supprimé avec succès !";
        redirectTo("projects.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES
try {
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM projets")->fetchColumn();
    
    $sqlList = "SELECT p.*, GROUP_CONCAT(l.langage SEPARATOR ', ') AS languages 
                FROM projets p 
                LEFT JOIN langage_projet l ON p.id = l.id_projet 
                GROUP BY p.id 
                ORDER BY p.display_order ASC, p.id DESC";
    $projects = $pdo->query($sqlList)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier le Projet" : "Gestion des Projets"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="projects.php" method="POST" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editProject['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Titre du projet :</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($editProject['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="icon">Classe de l'icône Boxicons :</label>
                <input type="text" name="icon" id="icon" value="<?php echo htmlspecialchars($editProject['icon']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description :</label>
                <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($editProject['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="code_source_link">Lien du Code Source (GitHub...) :</label>
                <input type="url" name="code_source_link" id="code_source_link" value="<?php echo htmlspecialchars($editProject['code_source_link']); ?>" required>
            </div>

            <div class="form-group">
                <label for="action_link">Lien de démonstration / Action :</label>
                <input type="url" name="action_link" id="action_link" value="<?php echo htmlspecialchars($editProject['action_link']); ?>" required>
            </div>

            <div class="form-group">
                <label for="action_name">Nom du bouton d'action :</label>
                <input type="text" name="action_name" id="action_name" value="<?php echo htmlspecialchars($editProject['action_name']); ?>">
            </div>

            <div class="form-group">
                <label for="languages">Langages / Technologies (séparés par des virgules) :</label>
                <input type="text" name="languages" id="languages" placeholder="Ex: PHP, JavaScript, Tailwind CSS" value="<?php echo htmlspecialchars($editProject['languages']); ?>">
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editProject['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="projects.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Créer le projet</button>
            <?php endif; ?>
        </form>

        <h3>Projets publiés</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Icône</th>
                    <th>Titre</th>
                    <th>Langages</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><i class="<?php echo htmlspecialchars($project["icon"]); ?>"></i></td>
                            <td><?php echo htmlspecialchars($project["title"]); ?></td>
                            <td><?php echo htmlspecialchars($project["languages"] ?? "-"); ?></td>
                            <td><?php echo htmlspecialchars($project["display_order"]); ?></td>
                            <td>
                                <a href="projects.php?action=edit&id=<?php echo $project["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="projects.php?action=delete&id=<?php echo $project["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">Aucun projet publié.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer ce projet ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>