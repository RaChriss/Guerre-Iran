<?php
/**
 * Template Chronologie
 */

// Récupération des événements groupés par année
$evenements = dbFetchAll(
    "SELECT *, EXTRACT(YEAR FROM date_evenement) as annee
     FROM evenements
     WHERE actif = TRUE
     ORDER BY date_evenement DESC"
);

// Grouper par année
$parAnnee = [];
foreach ($evenements as $evt) {
    $annee = $evt['annee'];
    if (!isset($parAnnee[$annee])) {
        $parAnnee[$annee] = [];
    }
    $parAnnee[$annee][] = $evt;
}

$metaTitle = 'Chronologie des événements';
$metaDescription = 'Retrouvez la chronologie complète des événements liés à la situation en Iran.';

include INCLUDES_PATH . '/header.php';
?>

<div class="container">
    <header class="page-header">
        <h1>Chronologie des événements</h1>
        <p class="page-description">
            Suivez l'évolution de la situation à travers les événements majeurs.
        </p>
    </header>

    <?php if (empty($evenements)): ?>
        <div class="empty-state">
            <p>Aucun événement enregistré pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($parAnnee as $annee => $events): ?>
                <div class="timeline-year">
                    <h2 class="timeline-year-title"><?= $annee ?></h2>

                    <div class="timeline-events">
                        <?php foreach ($events as $evt): ?>
                            <article class="timeline-event">
                                <div class="timeline-event-marker"></div>

                                <div class="timeline-event-date">
                                    <time datetime="<?= $evt['date_evenement'] ?>">
                                        <?= formatDate($evt['date_evenement'], 'd F Y') ?>
                                    </time>
                                </div>

                                <div class="timeline-event-content">
                                    <?php if ($evt['image']): ?>
                                        <div class="timeline-event-image">
                                            <img src="<?= imageUrl($evt['image']) ?>" alt="<?= e($evt['alt_image'] ?: $evt['titre']) ?>"
                                                loading="lazy">
                                        </div>
                                    <?php endif; ?>

                                    <h3><?= e($evt['titre']) ?></h3>

                                    <?php if ($evt['description']): ?>
                                        <div class="timeline-event-description">
                                            <?= markdownToHtml($evt['description']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($evt['source']): ?>
                                        <p class="timeline-event-source">
                                            Source : <?= e($evt['source']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>