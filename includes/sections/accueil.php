<?php
try {
    $infoStmt = $pdo->query("SELECT * FROM informations ORDER BY id ASC LIMIT 1");
    $infoData = $infoStmt->fetch(PDO::FETCH_ASSOC);

    $jobsStmt = $pdo->query("SELECT title FROM job_titles ORDER BY display_order ASC, id ASC");
    $jobTitles = $jobsStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $infoData = [];
    $jobTitles = [];
}

$description = !empty($infoData['description']) 
    ? $infoData['description'] 
    : "Passionné par l'informatique, je maîtrise plusieurs technologies de développement...";

$profileImage = (!empty($infoData['profile_image']))
    ? $infoData['profile_image'] 
    : "assets/img/default_img.jpg";

$linkedin = $infoData['linkedin_link'] ?? "";
$github = $infoData['github_link'] ?? "";
$facebook = $infoData['facebook_link'] ?? "";
$instagram = $infoData['instagram_link'] ?? "";
$twitter = $infoData['twitter_link'] ?? "";

// Génération dynamique des mots clés d'animation si présents en BDD
$keyframesWords = "";
$keyframesTyping = "";
$animationDuration = 20;

if (!empty($jobTitles)) {
    $total = count($jobTitles);
    $animationDuration = max(5, $total * 5);
    $step = 100 / $total;
    
    $coverSteps = [];
    $openSteps = [];

    foreach ($jobTitles as $index => $title) {
        $start = ($index === 0) ? 0 : (int)round($index * $step) + 1;
        $end = ($index === $total - 1) ? 100 : (int)round(($index + 1) * $step);
        $escapedTitle = str_replace(["\\", "'"], ["\\\\", "\\'"], $title);
        $keyframesWords .= "{$start}%, {$end}% { content: ' " . $escapedTitle . "'; }\n";

        $sliceStart = $index * $step;
        $coverSteps[] = (int)round($sliceStart + 0.20 * $step) . "%";
        $openSteps[] = (int)round($sliceStart + 0.40 * $step) . "%";
        $openSteps[] = (int)round($sliceStart + 0.60 * $step) . "%";
        $coverSteps[] = (int)round($sliceStart + 0.80 * $step) . "%";
        $coverSteps[] = ($index === $total - 1) ? "100%" : (int)round(($index + 1) * $step) . "%";
    }

    $uniqueCover = array_values(array_unique($coverSteps));
    $uniqueOpen = array_values(array_unique($openSteps));

    $keyframesTyping .= implode(", ", $uniqueOpen) . " { width: 0; }\n";
    $keyframesTyping .= implode(", ", $uniqueCover) . " { width: calc(100% + 8px); }\n";
}
?>

<?php if (!empty($keyframesWords)): ?>
<style>
.text-animation span::before {
    animation: words <?php echo $animationDuration; ?>s infinite;
}
.text-animation span::after {
    animation: cursor 0.6s infinite, typing <?php echo $animationDuration; ?>s steps(14) infinite;
}
@keyframes words {
    <?php echo $keyframesWords; ?>
}
@keyframes typing {
    <?php echo $keyframesTyping; ?>
}
</style>
<?php endif; ?>

<section class="accueil" id="accueil">
    <div class="accueil-content">
      <h1>Hi, It's <span><?php echo htmlspecialchars($firstname ?? 'Emmanuel'); ?></span></h1>
      <h3 class="text-animation">I'm a<span></span></h3>
      <p><?php echo nl2br(htmlspecialchars($description)); ?></p>

      <div class="social-icons">
        <?php if (!empty($linkedin)): ?>
          <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" title="LinkedIn"><i class="bx bxl-linkedin"></i></a>
        <?php endif; ?>
        <?php if (!empty($github)): ?>
          <a href="<?php echo htmlspecialchars($github); ?>" target="_blank" title="GitHub"><i class="bx bxl-github"></i></a>
        <?php endif; ?>
        <?php if (!empty($facebook)): ?>
          <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" title="Facebook"><i class="bx bxl-facebook"></i></a>
        <?php endif; ?>
        <?php if (!empty($instagram)): ?>
          <a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" title="Instagram"><i class="bx bxl-instagram-alt"></i></a>
        <?php endif; ?>
        <?php if (!empty($twitter)): ?>
          <a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank" title="Twitter / X"><i class="bx bxl-twitter"></i></a>
        <?php endif; ?>
      </div>

      <div class="btn-group">
        <a class="btn" href="#contact">Hire</a>
        <a class="btn" href="#contact">Contact</a>
      </div>
    </div>

    <div class="accueil-img">
      <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="<?php echo htmlspecialchars(($firstname ?? 'Emmanuel') . ' ' . ($lastname ?? 'Bissa')); ?>" />
    </div>
</section>
