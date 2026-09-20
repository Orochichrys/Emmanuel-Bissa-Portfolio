<?php
try {
    $skillsStmt = $pdo->query("SELECT * FROM skills ORDER BY display_order ASC, id ASC");
    $skillsList = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $skillsList = [];
}
?>
<section class="skills" id="skills">
    <h2 class="heading">Compétences</h2>

    <div class="skills-container">
      <?php if (!empty($skillsList)): ?>
        <?php foreach ($skillsList as $skill): ?>
          <div class="skill-item">
            <div class="skill-meta">
              <span><?php echo htmlspecialchars($skill["name"]); ?></span> 
              <span><?php echo (int) $skill["percentage"]; ?>%</span>
            </div>
            <div class="skill-track">
              <div class="skill-fill" style="--target: <?php echo (int) $skill["percentage"]; ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: #ccc; font-size: 1.6rem;">Aucune compétence enregistrée pour le moment.</p>
      <?php endif; ?>
    </div>
</section>
