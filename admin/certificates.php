<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editCertificate = [
    'id' => '',
    'title' => '',
    'image_path' => '',
    'display_order' => 0
];

$uploadDir = __DIR__ . "/../Certificats/";

// Mode Édition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM certificats WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editCertificate = $fetched;
        } else {
            $_SESSION["error"] = "Certificat introuvable.";
            redirectTo("certificates.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $title = security($_POST["title"] ?? "");
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        $uploadedName = handleFileUpload($_FILES["image_path"] ?? null, $uploadDir);
        if ($uploadedName === false) {
            redirectTo("certificates.php");
        }

        if (empty($uploadedName)) {
            $_SESSION["error"] = "Veuillez sélectionner une image pour le certificat.";
            redirectTo("certificates.php");
        }

        if (strpos($uploadedName, "__base64__") === 0) {
            $imagePath = str_replace("__base64__", "", $uploadedName);
        } else if (strpos($uploadedName, "data:") === 0) {
            $imagePath = $uploadedName;
        } else if (strpos($uploadedName, "__fallback__") === 0) {
            $imagePath = str_replace("__fallback__", "", $uploadedName);
        } else {
            $imagePath = "Certificats/" . $uploadedName;
        }

        if (!empty($title) && !empty($imagePath)) {
            $sql = "INSERT INTO certificats (title, image_path, display_order, last_update) VALUES (:title, :image_path, :order, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "title" => $title,
                "image_path" => $imagePath,
                "order" => $display_order
            ]);

            $_SESSION["success"] = "Certificat ajouté avec succès !";
            redirectTo("certificates.php");
        } else {
            $_SESSION["error"] = "Tous les champs sont obligatoires.";
            redirectTo("certificates.php");
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
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;
        $imagePath = $_POST["current_image_path"] ?? "";

        if (isset($_FILES["image_path"]) && $_FILES["image_path"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $uploadedName = handleFileUpload($_FILES["image_path"], $uploadDir);
            if ($uploadedName === false) {
                redirectTo("certificates.php?action=edit&id=" . $id);
            }
            if (!empty($uploadedName)) {
                if (strpos($uploadedName, "__base64__") === 0) {
                    $imagePath = str_replace("__base64__", "", $uploadedName);
                } else if (strpos($uploadedName, "data:") === 0) {
                    $imagePath = $uploadedName;
                } else if (strpos($uploadedName, "__fallback__") === 0) {
                    $imagePath = str_replace("__fallback__", "", $uploadedName);
                } else {
                    $imagePath = "Certificats/" . $uploadedName;
                }
            }
        }

        if (!empty($title) && !empty($imagePath)) {
            $sql = "UPDATE certificats SET title = :title, image_path = :image_path, display_order = :order, last_update = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "title" => $title,
                "image_path" => $imagePath,
                "order" => $display_order,
                "id" => $id
            ]);

            $_SESSION["success"] = "Certificat mis à jour avec succès !";
            redirectTo("certificates.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];

        // Récupérer le chemin de l'image pour la supprimer du serveur
        $stmtFile = $pdo->prepare("SELECT image_path FROM certificats WHERE id = :id");
        $stmtFile->execute(["id" => $id]);
        $filePath = $stmtFile->fetchColumn();

        if ($filePath && strpos($filePath, 'data:') !== 0 && file_exists(__DIR__ . "/../" . $filePath)) {
            @unlink(__DIR__ . "/../" . $filePath);
        }

        $sql = "DELETE FROM certificats WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Certificat supprimé avec succès !";
        redirectTo("certificates.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES
try {
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM certificats")->fetchColumn();
    $certificates = $pdo->query("SELECT * FROM certificats ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificats - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier le Certificat" : "Gestion des Certificats"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="certificates.php" method="POST" enctype="multipart/form-data" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editCertificate['id']); ?>">
                <input type="hidden" name="current_image_path" value="<?php echo htmlspecialchars($editCertificate['image_path']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Titre du certificat :</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($editCertificate['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="image_path">Image du certificat <?php echo $isEditMode ? "(Laissez vide pour conserver l'image actuelle)" : ""; ?> :</label>
                <input type="file" name="image_path" id="image_path" accept="image/*" <?php echo $isEditMode ? "" : "required"; ?>>
                <?php if ($isEditMode && !empty($editCertificate['image_path'])): ?>
                    <div style="margin-top: 5px;">
                        <?php $certImgSrc = (strpos($editCertificate['image_path'], 'data:') === 0) ? $editCertificate['image_path'] : '../' . htmlspecialchars($editCertificate['image_path']); ?>
                        <img src="<?php echo $certImgSrc; ?>" alt="Aperçu" style="max-height: 80px; border-radius: 4px; border: 1px solid #333;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editCertificate['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="certificates.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Ajouter le certificat</button>
            <?php endif; ?>
        </form>

        <h3>Liste des certificats</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Aperçu</th>
                    <th>Titre</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($certificates)): ?>
                    <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <td>
                                <?php $tblImgSrc = (strpos($certificate["image_path"], 'data:') === 0) ? $certificate["image_path"] : '../' . htmlspecialchars($certificate["image_path"]); ?>
                                <img src="<?php echo $tblImgSrc; ?>" alt="<?php echo htmlspecialchars($certificate["title"]); ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: 3px;">
                            </td>
                            <td><?php echo htmlspecialchars($certificate["title"]); ?></td>
                            <td><?php echo htmlspecialchars($certificate["display_order"]); ?></td>
                            <td>
                                <a href="certificates.php?action=edit&id=<?php echo $certificate["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="certificates.php?action=delete&id=<?php echo $certificate["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">Aucun certificat enregistré.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer ce certificat ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>