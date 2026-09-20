<?php
require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/functions.php";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title><?php echo htmlspecialchars(($firstname ?? 'Emmanuel') . ' ' . ($lastname ?? 'Bissa')); ?> Portfolio</title>

  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/index.css?v=<?php echo time(); ?>"/>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
  
  <?php include "includes/header.php"; ?>

  <!-- Section Accueil -->
  <?php include "includes/sections/accueil.php"; ?>

  <!-- Section Education -->
  <?php include "includes/sections/education.php"; ?>

  <!-- Section Compétences -->
  <?php include "includes/sections/skills.php"; ?>

  <!-- Section Services -->
  <?php include "includes/sections/services.php"; ?>

  <!-- Section Projets -->
  <?php include "includes/sections/projets.php"; ?>

  <!-- Section Certificats -->
  <?php include "includes/sections/certificats.php"; ?>

  <!-- Section Contact -->
  <?php include "includes/sections/contact.php"; ?>

  <!-- Footer -->
  <?php include "includes/sections/footer.php"; ?>

  <!-- Project JavaScript -->
  <script src="assets/js/index.js"></script>
</body>

</html>