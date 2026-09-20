<?php
try {
    $footerInfoStmt = $pdo->query("SELECT * FROM informations ORDER BY id ASC LIMIT 1");
    $footerInfo = $footerInfoStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $footerInfo = [];
}

$fLinkedin = $footerInfo['linkedin_link'] ?? "";
$fGithub = $footerInfo['github_link'] ?? "";
$fFacebook = $footerInfo['facebook_link'] ?? "";
$fInstagram = $footerInfo['instagram_link'] ?? "";
$fTwitter = $footerInfo['twitter_link'] ?? "";
?>
<footer class="footer">
    <div class="social">
      <?php if (!empty($fLinkedin)): ?>
        <a href="<?php echo htmlspecialchars($fLinkedin); ?>" target="_blank" title="LinkedIn"><i class="bx bxl-linkedin"></i></a>
      <?php endif; ?>
      <?php if (!empty($fGithub)): ?>
        <a href="<?php echo htmlspecialchars($fGithub); ?>" target="_blank" title="GitHub"><i class="bx bxl-github"></i></a>
      <?php endif; ?>
      <?php if (!empty($fFacebook)): ?>
        <a href="<?php echo htmlspecialchars($fFacebook); ?>" target="_blank" title="Facebook"><i class="bx bxl-facebook"></i></a>
      <?php endif; ?>
      <?php if (!empty($fInstagram)): ?>
        <a href="<?php echo htmlspecialchars($fInstagram); ?>" target="_blank" title="Instagram"><i class="bx bxl-instagram-alt"></i></a>
      <?php endif; ?>
      <?php if (!empty($fTwitter)): ?>
        <a href="<?php echo htmlspecialchars($fTwitter); ?>" target="_blank" title="Twitter / X"><i class="bx bxl-twitter"></i></a>
      <?php endif; ?>
    </div>

    <ul class="list">
      <li>
        <a href="#">FAQ</a>
      </li>
      <li>
        <a href="#services">Services</a>
      </li>
      <li>
        <a href="#accueil">About Me</a>
      </li>
      <li>
        <a href="#contact">Contact</a>
      </li>
      <li>
        <a href="#certificats">Certificats</a>
      </li>
    </ul>
    <p class="copyright">© <span id="year"><?php echo date('Y'); ?></span> <?php echo htmlspecialchars(($firstname ?? 'Emmanuel') . ' ' . ($lastname ?? 'Bissa')); ?> | All Rights Reserved</p>
</footer>
