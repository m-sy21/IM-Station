<?php
/**
 * @file plan.php
 * @brief Plan du site — présentation de l'arborescence et des pages du projet
 *
 * Présente la structure complète du site i'm Station :
 * arborescence des fichiers, liste des pages et leurs fonctionnalités,
 * technologies utilisées et informations du projet.
 *
 * @author Ines LAIFAOUI
 * @author Meriem SIMYA
 * @version 1.0
 * @date 2026
 * @copyright L2 Informatique — CY Cergy Paris Université
 */
require_once 'include/util.inc.php';
require_once 'include/functions.inc.php';

$styleActif  = gerer_cookie_style();
$vars        = get_vars_style($styleActif);
$styleOppose = $vars['styleOppose'];
$css         = $vars['css'];
$imgSwitch   = $vars['imgSwitch'];
$altSwitch   = $vars['altSwitch'];

$pageTitle = 'Plan du site';
$pageDesc  = "Plan du site i'm Station — prix des carburants en France.";
$pageActif = 'plan.php';
$pageStyle = '
/* ── Plan style Apple ── */
.plan-sitemap {
    padding: 2rem 0;
}
.plan-sitemap-title {
    font-family: \'Poppins\', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--blue);
    margin-bottom: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--nav-border);
}
.plan-cols {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3rem 4rem;
    margin-top: 2.5rem;
}
.plan-col-group {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.plan-col-group + .plan-col-group {
    margin-top: 2rem;
}
.plan-cat-title {
    font-family: \'Poppins\', sans-serif;
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 12px;
    letter-spacing: 0;
}
.plan-cat-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.plan-cat-links li a {
    font-size: 14px;
    color: var(--blue-mid);
    text-decoration: none;
    line-height: 1.4;
    transition: color .15s;
}
.plan-cat-links li a:hover {
    color: var(--accent);
    text-decoration: underline;
}
/* Arborescence fichiers */
.arbo-wrap {
    background: var(--bg-card);
    border: 1px solid var(--nav-border);
    border-radius: var(--radius);
    padding: 22px 28px;
    font-family: \'Courier New\', monospace;
    font-size: 13.5px;
    color: var(--text);
    line-height: 2;
    margin-top: 8px;
    overflow-x: auto;
}
.arbo-root  { color:var(--blue); font-weight:800; font-size:14px; }
.arbo-file  { color:var(--accent); }
.arbo-dir   { color:var(--blue-mid); font-weight:700; }
.arbo-dim   { color:var(--text-muted); font-style:italic; }
/* Tableau infos */
.info-table { width:100%; border-collapse:collapse; font-size:13.5px; background:var(--bg-card); border:1px solid var(--nav-border); border-radius:var(--radius); overflow:hidden; margin-top:8px; }
.info-table td { padding:11px 16px; border-bottom:1px solid var(--nav-border); }
.info-table tr:last-child td { border-bottom:none; }
.info-table td:first-child { font-family:\'Poppins\',sans-serif; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); width:170px; background:var(--bg-card2); }

@media(max-width:768px) {
    .plan-cols { grid-template-columns:1fr; gap:2rem; }
}
';
require_once 'include/header.inc.php';
?>

<main id="main-content">
    <h1 class="page-title">Plan du site</h1>

   <div class="plan-sitemap">
    <h2 class="plan-sitemap-title">Plan du site i'm Station</h2>

    <div class="plan-cols">

        <!-- Colonne 1 : Navigation & Pages secondaires -->
        <div class="plan-col-group">
            <p class="plan-cat-title">Navigation principale</p>
            <ul class="plan-cat-links">
                <li><a href="bienvenue.php?style=<?= urlencode($styleActif) ?>">À propos</a></li>
                <li><a href="index.php?style=<?= urlencode($styleActif) ?>">Accueil — Carte interactive</a></li>
                <li><a href="stats.php?style=<?= urlencode($styleActif) ?>">Statistiques</a></li>
            </ul>

            <p class="plan-cat-title" style="margin-top: 2rem;">Pages secondaires</p>
            <ul class="plan-cat-links">
                <li><a href="tech.php?style=<?= urlencode($styleActif) ?>">Page technique</a></li>
                <li><a href="sources.php?style=<?= urlencode($styleActif) ?>">Sources et API</a></li>
                <li><a href="plan.php?style=<?= urlencode($styleActif) ?>">Plan du site</a></li>
            </ul>
        </div>

        <!-- Colonne 2 : Recherche & Démo -->
        <div class="plan-col-group">
            <p class="plan-cat-title">Recherche de carburants</p>
            <ul class="plan-cat-links">
                <li><a href="index.php?style=<?= urlencode($styleActif) ?>#choix-dept" aria-label="Accueil — Sélectionner une région sur la carte">Sélectionner une région sur la carte</a></li>
                <li><a href="index.php?style=<?= urlencode($styleActif) ?>&region=" aria-label="Accueil — Choisir un département après avoir sélectionné une région">Choisir un département</a></li>
                <li><a href="index.php?style=<?= urlencode($styleActif) ?>&dept=" aria-label="Accueil — Sélectionner une ville dans votre département">Sélectionner une ville</a></li>
                <li><a href="resultats.php?style=<?= urlencode($styleActif) ?>">Consulter les prix des stations</a></li>
            </ul>

            <p class="plan-cat-title" style="margin-top: 2rem;">Démonstrations techniques</p>
            <ul class="plan-cat-links">
                <li><a href="tech.php?style=<?= urlencode($styleActif) ?>#ghibli" aria-label="Page technique — API Ghibli film aléatoire">API Ghibli — film aléatoire (JSON)</a></li>
                <li><a href="tech.php?style=<?= urlencode($styleActif) ?>#geo" aria-label="Page technique — Géolocalisation par adresse IP">Géolocalisation par adresse IP (JSON)</a></li>
            </ul>
        </div>

        <!-- Colonne 3 : Perso & Stats -->
        <div class="plan-col-group">
            <p class="plan-cat-title">Personnalisation</p>
            <ul class="plan-cat-links">
                <li><a href="index.php?style=standard">Mode jour</a></li>
                <li><a href="index.php?style=alternatif">Mode nuit</a></li>
            </ul>

            <p class="plan-cat-title" style="margin-top: 2rem;">Statistiques</p>
            <ul class="plan-cat-links">
                <li><a href="stats.php?style=<?= urlencode($styleActif) ?>#prix-moyens" aria-label="Statistiques — Prix moyens nationaux">Prix moyens nationaux</a></li>
                <li><a href="stats.php?style=<?= urlencode($styleActif) ?>#histogramme" aria-label="Statistiques — Histogramme des villes consultées">Histogramme des villes consultées</a></li>
                <li><a href="stats.php?style=<?= urlencode($styleActif) ?>#historique" aria-label="Statistiques — Historique des consultations">Historique des consultations</a></li>
                <li><a href="stats.php?style=<?= urlencode($styleActif) ?>#recentes" aria-label="Statistiques — Vos recherches récentes">Vos recherches récentes</a></li>
            </ul>
        </div>

    </div>
</div>
</main>

<?php require_once 'include/footer.inc.php'; ?>
