<?php
try {
    $educationsStmt = $pdo->query("SELECT * FROM educations ORDER BY display_order ASC, id ASC");
    $educationsList = $educationsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $educationsList = [];
}
?>
<section class="education" id="education">
    <h2 class="heading">Education</h2>

    <div class="timeline-items">
      <?php if (!empty($educationsList)): ?>
        <?php 
          $lastIndex = count($educationsList) - 1;
          foreach ($educationsList as $index => $edu): 
            $title = htmlspecialchars($edu["sector"]) . " - " . htmlspecialchars($edu["school_name"]);
            if ($index === $lastIndex && stripos($title, '(actuel)') === false) {
                $title .= " (Actuel)";
            }
        ?>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-date"><?php echo htmlspecialchars($edu["year"]); ?></div>
            <div class="timeline-content">
              <h3><?php echo $title; ?></h3>
              <p><?php echo nl2br(htmlspecialchars($edu["description"])); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: #ccc; font-size: 1.6rem;">Aucune formation renseignée pour le moment.</p>
      <?php endif; ?>
    </div>
</section>
