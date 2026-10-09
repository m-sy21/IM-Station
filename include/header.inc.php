<?php
/**
 * @file header.inc.php
 * @brief En-tête HTML commun — génère le DOCTYPE, head, header et nav de chaque page
 *
 * Fichier inclus en début de chaque page via require_once.
 * Attend les variables suivantes définies par la page appelante :
 * \$styleActif, \$styleOppose, \$css, \$imgSwitch, \$altSwitch,
 * \$pageTitle, \$pageDesc, \$pageActif, \$pageStyle (optionnel).
 *
 * @author Ines LAIFAOUI
 * @author Meriem SIMYA
 * @version 1.0
 * @date 2026
 * @copyright L2 Informatique — CY Cergy Paris Université
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <title><?= htmlspecialchars($pageTitle) ?> — i'm Station</title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'Prix des carburants en France — i\'m Station') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <meta name="theme-color" content="#0d1c45">
    <meta name="robots" content="index, follow">
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php if (!empty($pageStyle)): ?>
    <style>
        <?= $pageStyle ?>
    </style>
    <?php endif; ?>
</head>
<body>

<a class="skip-link" href="#main-content">Aller au contenu principal</a>

<header class="site-header">
    <a href="index.php?style=<?= urlencode($styleActif) ?>" class="site-logo-link" aria-label="i'm Station — accueil">
        <img src="images/logo.png"
             alt="i'm Station — Prix des carburants"
             class="logo-img-header">
    </a>
</header>

<nav class="menu" aria-label="Navigation principale">
    <?php
    $liens = [
        "bienvenue.php" => "À propos",
        "stats.php"     => "Statistiques",
    ];
    foreach ($liens as $fichier => $label):
        $actif = ($pageActif === $fichier) ? ' class="actif"' : '';
    ?>
    <a href="<?= $fichier ?>?style=<?= urlencode($styleActif) ?>"<?= $actif ?>><?= $label ?></a>
    <?php endforeach; ?>
    <div class="nav-right">
        <?php
        // Préserver tous les paramètres GET sauf 'style' (pour garder ville=, region=, etc.)
        $paramsGet = $_GET;
        $paramsGet['style'] = $styleOppose;
        $switchUrl = htmlspecialchars($pageActif . '?' . http_build_query($paramsGet));
        ?>
        <a href="<?= $switchUrl ?>"
           class="lien-style"
           title="<?= htmlspecialchars($altSwitch) ?>">
            <img src="<?= htmlspecialchars($imgSwitch) ?>"
                 alt="<?= htmlspecialchars($altSwitch) ?>"
                 class="img-style">
        </a>
    </div>
</nav>
