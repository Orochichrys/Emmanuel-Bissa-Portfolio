<?php
try {
    $certifsStmt = $pdo->query("SELECT * FROM certificats ORDER BY display_order ASC, id ASC");
    $certifsList = $certifsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $certifsList = [];
}
?>
<section class="certificats" id="certificats">
    <h2 class="heading">Certificats</h2>

    <div class="certificats-container">
      <?php if (!empty($certifsList)): ?>
        <?php foreach ($certifsList as $cert): ?>
          <div class="certificats-box">
            <div class="img">
              <img src="<?php echo htmlspecialchars($cert["image_path"]); ?>" alt="<?php echo htmlspecialchars($cert["title"]); ?>" />
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: #ccc; font-size: 1.6rem;">Aucun certificat enregistré.</p>
      <?php endif; ?>
    </div>
</section>
