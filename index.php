<?php
/**
 * @file index.php
 * @brief Page d'accueil — Carte SVG interactive des régions de France
 *
 * Cette page permet à l'utilisateur de sélectionner sa région sur une carte SVG
 * cliquable, puis son département dans un menu déroulant, et enfin sa ville
 * pour être redirigé vers la page des résultats (prix des carburants).
 * Elle affiche également une suggestion géolocalisée et l'historique des villes
 * récemment consultées (cookie).
 *
 * @author Ines LAIFAOUI
 * @author Meriem SIMYA
 * @version 1.0
 * @date 2026
 * @copyright L2 Informatique — CY Cergy Paris Université
 */
require_once 'include/util.inc.php';
require_once 'include/functions.inc.php';

// ── Style et variables communes ───────────────────────────────────────────────
$styleActif  = gerer_cookie_style();
$vars        = get_vars_style($styleActif);
$styleOppose = $vars['styleOppose'];
$css         = $vars['css'];
$imgSwitch   = $vars['imgSwitch'];
$altSwitch   = $vars['altSwitch'];

// ── Historique et géolocalisation ─────────────────────────────────────────────
$historique  = get_historique();
$geo         = get_geolocalisation();
$villeGeo    = $geo['city']    ?? null;
$regionGeo   = $geo['region']  ?? null;
$paysGeo     = $geo['country'] ?? null;
$cpGeo       = $geo['postal']  ?? null;

// ── Paramètres GET ────────────────────────────────────────────────────────────
$deptSelectionne = $_GET['dept']   ?? '';
$regionGet       = $_GET['region'] ?? '';

// ── Régions et départements ───────────────────────────────────────────────────
$regions = get_regions();

$regionDuDept    = '';
$deptsDuDept     = [];
foreach ($regions as $nomRegion => $depts) {
    if (array_key_exists($deptSelectionne, $depts)) {
        $regionDuDept = $nomRegion;
        $deptsDuDept  = $depts;
        break;
    }
}
$regionEffective  = ($regionGet !== '') ? $regionGet : $regionDuDept;
$deptsDisponibles = $regions[$regionEffective] ?? [];

// ── Villes du département ─────────────────────────────────────────────────────
$villes = ($deptSelectionne !== '') ? get_villes_dept($deptSelectionne) : [];

// ── Variables pour le header ──────────────────────────────────────────────────
$pageTitle  = 'Accueil';
$pageDesc   = 'Trouvez le carburant le moins cher près de chez vous en France.';
$pageActif  = 'index.php';
$pageStyle  = '
.hero { text-align: center; padding: 30px 20px 20px; }
.hero-titre { font-family: \'Playfair Display\', serif; font-size: clamp(1.7rem, 4vw, 2.4rem); font-weight: 800; color: var(--blue); line-height: 1.15; margin-bottom: 8px; }
.hero-titre span { color: var(--accent); }
.hero-sous { font-size: 14px; color: var(--text-muted); }
.geo-suggestion { background: var(--bg-card); border: 1px solid var(--nav-border); border-left: 4px solid var(--accent); border-radius: var(--radius); padding: 12px 18px; margin-bottom: 1.4rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.geo-suggestion p { margin: 0; font-size: 14px; }
.geo-suggestion strong { color: var(--blue); }
.geo-suggestion a { background: var(--accent); color: #fff; font-family: \'Poppins\', sans-serif; font-weight: 700; font-size: 13px; padding: 7px 18px; border-radius: 20px; text-decoration: none; transition: background .2s; }
.geo-suggestion a:hover { background: var(--accent-dark); }
.historique-wrap { text-align: center; margin-bottom: 1.2rem; font-size: 13px; color: var(--text-muted); }
.historique-wrap a { display: inline-block; background: var(--bg-card2); border: 1px solid var(--nav-border); border-radius: 20px; padding: 3px 12px; color: var(--blue-mid); text-decoration: none; font-weight: 600; margin: 3px; font-size: 12.5px; }
.carte-svg-wrap { display: block; width: 100%; border-radius: 10px; overflow: hidden; border: 1px solid var(--nav-border); }
.carte-svg-wrap svg { width: 100%; height: auto; display: block; }
.carte-svg-wrap svg .region-zone { fill: transparent; cursor: pointer; }
.carte-svg-wrap svg .region-zone.active { fill: transparent; }
.carte-legende { font-size: 11px; color: var(--text-muted); font-style: italic; margin-top: 8px; }
.selection-wrap { background: var(--bg-card); border: 1px solid var(--nav-border); border-radius: var(--radius); padding: 20px 22px; margin-top: 6px; }
.selection-wrap h3 { font-family: \'Poppins\', sans-serif; font-size: 1rem; font-weight: 700; color: var(--blue); margin-bottom: 6px; }
.selection-wrap p { font-size: 13px; color: var(--text-muted); margin-bottom: 14px; }
.selection-row { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
.selection-row label { font-family: \'Poppins\', sans-serif; font-size: 13px; font-weight: 600; color: var(--text); }
.selection-row select { font-family: \'Nunito\', sans-serif; font-size: 14px; padding: 9px 14px; border: 2px solid var(--nav-border); border-radius: 8px; background: var(--bg); color: var(--text); outline: none; cursor: pointer; transition: border-color .2s; min-width: 220px; }
.selection-row select:focus { border-color: var(--blue-mid); }
.selection-row .btn-voir { background: var(--accent); color: #fff; font-family: \'Poppins\', sans-serif; font-weight: 700; font-size: 13.5px; padding: 9px 24px; border: none; border-radius: 8px; cursor: pointer; transition: background .2s; }
.selection-row .btn-voir:hover { background: var(--accent-dark); }
';

require_once 'include/header.inc.php';
?>

<main id="main-content">
    <div class="hero">
        <h1 class="hero-titre">Trouvez le carburant<br><span>le moins cher</span> près de chez vous</h1>
        <p class="hero-sous">Données officielles mises à jour en temps réel — SP95, SP98, Gazole, E10, GPLc</p>
    </div>

    <?php if ($villeGeo): ?>
    <div class="geo-suggestion">
        <p>Vous semblez être à <strong><?= h($villeGeo) ?></strong>
        <?= $cpGeo ? ' (' . h($cpGeo) . ')' : '' ?>
        <?= $regionGeo ? ' — ' . h($regionGeo) : '' ?>
        <?= $paysGeo   ? ' — ' . h($paysGeo)   : '' ?></p>
        <a href="resultats.php?style=<?= urlencode($styleActif) ?>&ville=<?= urlencode(strtoupper($villeGeo)) ?>">
            Voir les stations près de moi
        </a>
    </div>
    <?php endif; ?>

    <?php if (!empty($historique)): ?>
    <div class="historique-wrap">
        Récemment consultées :
        <?php foreach ($historique as $v): ?>
        <a href="resultats.php?style=<?= urlencode($styleActif) ?>&ville=<?= urlencode($v) ?>"><?= h($v) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Carte SVG interactive des régions -->
    <section>
        <h2>Sélectionnez votre région</h2>
        <div class="carte-section-wrap">
            <p class="carte-instruction">
                Cliquez sur votre région pour afficher les départements.
            </p>
            <div class="carte-svg-wrap">
                <svg viewBox="0 0 105.16 100" xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Carte des régions de France"
                     class="svg-carte">
                    <image href="images/carte.png" x="0" y="0" width="105.16" height="100" preserveAspectRatio="none"/>
                    <polygon class="region-zone <?= $regionEffective === 'Nouvelle-Aquitaine' ? 'active' : '' ?>" data-region="Nouvelle-Aquitaine" points="5,42.7 9.9,39.2 33.7,39.7 43.7,41.2 43.7,54.6 42.7,61.5 33.7,62.5 19.3,62.5 4,56.1"><title>Nouvelle-Aquitaine</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Grand Est' ? 'active' : '' ?>" data-region="Grand Est" points="60,2 91,2 93,19 87,26 72,24 64,18 64,9"><title>Grand Est</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Occitanie' ? 'active' : '' ?>" data-region="Occitanie" points="29.8,50.6 44.6,48.6 67.5,47.6 70.4,65.0 66.0,73.4 50.6,75.4 32.7,75.4 19.3,71.4 17.4,64.5"><title>Occitanie</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Auvergne-Rhône-Alpes' ? 'active' : '' ?>" data-region="Auvergne-Rhône-Alpes" points="56,38 62,42 73,48 85,46 90,56 85,65 76,66 66,65 62,57 55,47"><title>Auvergne-Rhône-Alpes</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Bourgogne-Franche-Comté' ? 'active' : '' ?>" data-region="Bourgogne-Franche-Comté" points="60,22 73,24 87,26 91,38 85,47 72,48 61,42 59,32"><title>Bourgogne-Franche-Comté</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Pays de la Loire' ? 'active' : '' ?>" data-region="Pays de la Loire" points="10,21 36,20 43,25 44,36 34,41 18,40 10,33"><title>Pays de la Loire</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Centre-Val de Loire' ? 'active' : '' ?>" data-region="Centre-Val de Loire" points="36,20 49,17 60,27 61,34 56,42 44,42 36,35"><title>Centre-Val de Loire</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Normandie' ? 'active' : '' ?>" data-region="Normandie" points="10,8 43,8 44,11 49,17 36,20 23,21 10,15"><title>Normandie</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Bretagne' ? 'active' : '' ?>" data-region="Bretagne" points="1,15 18,13 22,20 18,28 8,30 1,27"><title>Bretagne</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === "Provence-Alpes-Côte d'Azur" ? 'active' : '' ?>" data-region="Provence-Alpes-Côte d'Azur" points="76,62 85,60 95,64 97,71 94,78 84,78 74,72"><title>Provence-Alpes-Côte d'Azur</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Hauts-de-France' ? 'active' : '' ?>" data-region="Hauts-de-France" points="43,2 60,2 64,9 65,17 58,19 49,17 44,11"><title>Hauts-de-France</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Corse' ? 'active' : '' ?>" data-region="Corse" points="89,82 94,80 98,83 99,89 97,96 93,97 90,94 88,88"><title>Corse</title></polygon>
                    <polygon class="region-zone <?= $regionEffective === 'Île-de-France' ? 'active' : '' ?>" data-region="Île-de-France" points="49,17 58,17 61,21 60,27 51,27 47,23"><title>Île-de-France</title></polygon>
                </svg>
            </div>
            <p class="carte-legende">Carte administrative des régions de France — Source : IGN</p>
        </div>
    </section>

    <!-- Sélection département → ville -->
    <section id="choix-dept">
        <h2>Région sélectionnée</h2>
        <div class="selection-wrap">
            <?php if ($regionEffective === '' && $deptSelectionne === ''): ?>
                <h3>Choisissez votre région</h3>
                <p>Cliquez sur une région sur la carte ci-dessus pour afficher les départements.</p>
            <?php elseif ($regionEffective !== '' && $deptSelectionne === ''): ?>
                <h3><?= h($regionEffective) ?></h3>
                <p>Choisissez votre département puis votre ville.</p>
                <div class="selection-row">
                    <label for="dept-sel">Département :</label>
                    <select id="dept-sel" data-region="<?= h($regionEffective) ?>" data-style="<?= h($styleActif) ?>">
                        <option value="">— Sélectionnez un département —</option>
                        <?php foreach ($deptsDisponibles as $num => $nomDept): ?>
                        <option value="<?= h($num) ?>"><?= h($num) ?> — <?= h($nomDept) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif ($deptSelectionne !== ''): ?>
                <h3><?= h($regionDuDept) ?></h3>
                <p>Choisissez votre ville :</p>
                <div class="selection-row">
                    <label for="dept-sel2">Département :</label>
                    <select id="dept-sel2" data-region="<?= h($regionDuDept) ?>" data-style="<?= h($styleActif) ?>">
                        <?php foreach ($deptsDuDept as $num => $nomDept): ?>
                        <option value="<?= h($num) ?>" <?= ($deptSelectionne === $num) ? 'selected' : '' ?>>
                            <?= h($num) ?> — <?= h($nomDept) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="ville-select">Ville :</label>
                    <select id="ville-select">
                        <option value="">— Sélectionnez une ville —</option>
                        <?php foreach ($villes as $nom => $val): ?>
                        <option value="<?= h($val) ?>"><?= h($nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-voir" id="btn-voir-stations">
                        Voir les stations
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
/* ── Carte SVG : clic région ── */
document.querySelectorAll('.region-zone').forEach(function(zone) {
    zone.addEventListener('click', function() {
        var region = this.dataset.region;
        var style  = <?= json_encode($styleActif) ?>;
        // On ajoute #choix-dept à la fin de l'URL pour rester sur le formulaire
        window.location.href = 'index.php?style=' + encodeURIComponent(style)
            + '&region=' + encodeURIComponent(region) 
            + '#choix-dept'; 
    });
});
/* Select département : navigation au 1er coup */
var _deptNav = false;
['dept-sel', 'dept-sel2'].forEach(function(id) {
    var sel = document.getElementById(id);
    if (!sel) return;
    /* ── Select département ── */
sel.addEventListener('change', function() {
    if (_deptNav) return;
    var deptVal = this.value;
    if (!deptVal) return;
    _deptNav = true;
    var region = this.dataset.region;
    var style  = this.dataset.style;
    
    sessionStorage.setItem('deptChoisi', deptVal);
    
    // On ajoute aussi l'ancre ici
    window.location.href = 'index.php?style=' + encodeURIComponent(style)
        + '&region=' + encodeURIComponent(region)
        + '&dept='   + encodeURIComponent(deptVal)
        + '#choix-dept'; 
});
});

/* Bouton Voir les stations */
var btnVoir = document.getElementById('btn-voir-stations');
if (btnVoir) {
    btnVoir.addEventListener('click', function() {
        var sel = document.getElementById('ville-select');
        if (!sel || !sel.value) { alert('Veuillez sélectionner une ville.'); return; }
        window.location.href = 'resultats.php?style='
            + encodeURIComponent(<?= json_encode($styleActif) ?>)
            + '&ville=' + encodeURIComponent(sel.value);
    });
}

/* Restaurer le bon département + scroll après rechargement */
(function() {
    /* 1. Restaurer la valeur du select dept si stockée */
    var deptChoisi = sessionStorage.getItem('deptChoisi');
    if (deptChoisi) {
        sessionStorage.removeItem('deptChoisi');
        var s2 = document.getElementById('dept-sel2');
        if (s2) {
            for (var i = 0; i < s2.options.length; i++) {
                if (s2.options[i].value === deptChoisi) {
                    s2.selectedIndex = i;
                    break;
                }
            }
        }
    }

})();
</script>
<?php require_once 'include/footer.inc.php'; ?>
