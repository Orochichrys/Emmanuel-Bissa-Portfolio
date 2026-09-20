<?php

/**
 * Redirige vers une URL spécifiée et arrête l'exécution de la page.
 */
function redirectTo($url) {
    header("Location: {$url}");
    exit();
}

/**
 * Nettoie et sécurise les données saisies par l'utilisateur.
 */
function security($data) {
    if (is_null($data)) return "";
    $temp = trim($data);
    $temp = strip_tags($temp);
    $temp = stripslashes($temp);
    return $temp;
}

/**
 * Affiche les alertes d'erreur et de succès stockées en session puis les réinitialise.
 */
function displayAlerts() {
    if (isset($_SESSION["error"])) {
        echo '<div class="alert-error">' . htmlspecialchars($_SESSION["error"]) . '</div>';
        unset($_SESSION["error"]);
    }

    if (isset($_SESSION["success"])) {
        echo '<div class="alert-success">' . htmlspecialchars($_SESSION["success"]) . '</div>';
        unset($_SESSION["success"]);
    }
}

/**
 * S'assure qu'un dossier et ses parents existent et applique 0777 en PHP.
 */
function ensureUploadDir($targetDir) {
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }
    @chmod($targetDir, 0777);
    @chmod(dirname($targetDir), 0777);
}

/**
 * Gère l'upload d'un fichier et l'encode directement en Base64 Data URI.
 * Cela garantit que les images sont conservées en base de données même sur un hébergement à système de fichiers éphémère.
 */
function handleFileUpload($fileArray, $targetDir = null, $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']) {
    if (!isset($fileArray) || !isset($fileArray['error']) || $fileArray['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Aucun fichier fourni
    }

    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
        switch ($fileArray['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $_SESSION['error'] = "Le fichier téléchargé dépasse la taille maximale autorisée.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $_SESSION['error'] = "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $_SESSION['error'] = "Échec de l'écriture du fichier temporaire (permissions serveur).";
                break;
            default:
                $_SESSION['error'] = "Erreur lors du téléchargement du fichier (Code : " . $fileArray['error'] . ").";
                break;
        }
        return false;
    }

    $fileName = basename($fileArray['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExts)) {
        $_SESSION['error'] = "Format d'image non valide. Formats autorisés : " . implode(', ', $allowedExts);
        return false;
    }

    // Encodage direct en Base64 Data URI
    $tmpPath = $fileArray['tmp_name'];
    if (file_exists($tmpPath)) {
        $imageData = @file_get_contents($tmpPath);
        if ($imageData !== false) {
            $mimeType = 'image/' . ($fileExt === 'jpg' ? 'jpeg' : $fileExt);
            if (function_exists('mime_content_type')) {
                $detectedMime = @mime_content_type($tmpPath);
                if (!empty($detectedMime)) {
                    $mimeType = $detectedMime;
                }
            }
            $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            return "__base64__" . $base64;
        }
    }

    $_SESSION['error'] = "Impossible de lire le fichier image téléchargé.";
    return false;
}
?>