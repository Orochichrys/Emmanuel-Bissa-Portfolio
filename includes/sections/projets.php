<?php
try {
    $sqlProjects = "SELECT p.*, GROUP_CONCAT(l.langage SEPARATOR ',') AS languages 
                    FROM projets p 
                    LEFT JOIN langage_projet l ON p.id = l.id_projet 
                    GROUP BY p.id 
                    ORDER BY p.display_order ASC, p.id ASC";
    $projetsList = $pdo->query($sqlProjects)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $projetsList = [];
}
?>
<section class="projets" id="projets">
    <h2 class="heading">Projets</h2>

    <div class="projets-container">
      <?php if (!empty($projetsList)): ?>
        <?php foreach ($projetsList as $projet): ?>
          <div class="projet-box">
            <div class="projet-icon">
              <i class='<?php echo htmlspecialchars($projet["icon"] ?: "bx bx-laptop"); ?>'></i>
            </div>
            <div class="projet-content">
              <h3><?php echo htmlspecialchars($projet["title"]); ?></h3>
              <p><?php echo nl2br(htmlspecialchars($projet["description"])); ?></p>
              
              <?php if (!empty($projet["languages"])): ?>
                <div class="tech-stack">
                  <?php 
                    $langs = array_filter(array_map('trim', explode(',', $projet["languages"])));
                    foreach ($langs as $lang): 
                  ?>
                    <span><?php echo htmlspecialchars($lang); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="projet-links">
                <?php if (!empty($projet["code_source_link"])): ?>
                  <a href="<?php echo htmlspecialchars($projet["code_source_link"]); ?>" target="_blank" class="btn">Code Source</a>
                <?php endif; ?>
                <?php if (!empty($projet["action_link"])): ?>
                  <a href="<?php echo htmlspecialchars($projet["action_link"]); ?>" target="_blank" class="btn"><?php echo htmlspecialchars($projet["action_name"] ?: "Visiter"); ?></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; grid-column: 1 / -1; color: #ccc; font-size: 1.6rem;">Aucun projet publié pour le moment.</p>
      <?php endif; ?>
    </div>
</section>
