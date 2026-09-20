<?php
try {
    $servicesStmt = $pdo->query("SELECT * FROM services ORDER BY display_order ASC, id ASC");
    $servicesList = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $servicesList = [];
}
?>
<section class="services" id="services">
    <h2 class="heading">Services</h2>

    <div class="services-container">
      <?php if (!empty($servicesList)): ?>
        <?php foreach ($servicesList as $service): ?>
          <div class="service-box">
            <div class="service-info">
              <h4><?php echo htmlspecialchars($service["title"]); ?></h4>
              <p><?php echo nl2br(htmlspecialchars($service["description"])); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: #ccc; font-size: 1.6rem;">Aucun service disponible pour le moment.</p>
      <?php endif; ?>
    </div>
</section>
