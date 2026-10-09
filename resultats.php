<?php
/**
 * @file resultats.php
 * @brief Affichage des stations-service et prix des carburants par ville
 *
 * Reçoit le nom d'une ville via GET, interroge l'API data.economie.gouv.fr
 * (format JSON) avec les codes postaux correspondants, et affiche le tableau
 * des stations avec leurs prix par carburant (SP95, SP98, Gazole, E10, GPLc)
 * ainsi que les services disponibles (Automate CB, Lavage, Wifi, etc.).
 * Enregistre chaque consultation dans consultations.csv (horodaté, côté serveur).
 * Met également à jour le cookie d'historique personnel (côté client).
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

$villeParam  = trim($_GET['ville'] ?? '');
$stations    = [];
$erreurApi   = false;
$cpsUtilises = [];

if ($villeParam !== '') {
    maj_cookie_historique($villeParam);
    enregistrer_consultation_csv($villeParam);

    $cpsUtilises = get_codes_postaux($villeParam);

    if (!empty($cpsUtilises)) {
        $cpsList = implode('","', $cpsUtilises);
        $where   = urlencode("cp in (\"$cpsList\")");
        $result  = appeler_api_carburants($where);
        if ($result === null) $erreurApi = true;
        else $stations = $result;
    }

    if (!$erreurApi && empty($stations)) {
        $nomApi = strtoupper(sans_accents($villeParam));
        $r1 = appeler_api_carburants(urlencode("ville=\"$nomApi\""));
        if ($r1 === null) $erreurApi = true;
        elseif (!empty($r1)) $stations = $r1;
        else {
            $r2 = appeler_api_carburants(urlencode("ville like \"%$nomApi%\""));
            if ($r2 !== null) $stations = $r2;
            else $erreurApi = true;
        }
    }
}

$pageTitle = h($villeParam ?: 'Résultats');
$pageDesc  = 'Stations-service et prix des carburants à ' . ($villeParam ?: 'votre ville');
$pageActif = 'resultats.php';
$pageStyle = '
.retour-lien { display:inline-flex;align-items:center;gap:6px;color:var(--blue-mid);font-weight:700;font-size:13.5px;text-decoration:none;margin-bottom:1.2rem; }
.resultats-info { font-size:13.5px;color:var(--text-muted);margin-bottom:10px; }
.resultats-info strong { color:var(--blue); }
.cp-badge { display:inline-block;font-size:11.5px;color:var(--text-muted);background:var(--bg-card2);border:1px solid var(--nav-border);border-radius:6px;padding:5px 12px;margin-bottom:14px; }
.cp-badge code { font-family:\'Courier New\',monospace;color:var(--blue-mid); }
.filtres { background:var(--bg-card);border:1px solid var(--nav-border);border-radius:var(--radius);padding:14px 18px;margin-bottom:1.2rem; }
.filtres-titre { font-family:\'Poppins\',sans-serif;font-weight:700;font-size:12px;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;display:block; }
.filtres-row { display:flex;flex-wrap:wrap;align-items:center;gap:14px; }
.filtres-row label { display:flex;align-items:center;gap:5px;font-size:13px;font-weight:600;cursor:pointer;color:var(--text); }
.filtres-row input[type="checkbox"] { accent-color:var(--accent);width:15px;height:15px; }
.filtres-row select { font-size:13px;padding:6px 10px;border:1px solid var(--nav-border);border-radius:6px;background:var(--bg-card2);color:var(--text);cursor:pointer; }
.table-wrap { overflow-x:auto;border:1px solid var(--nav-border);border-radius:var(--radius);background:var(--bg-card); }
table { width:100%;border-collapse:collapse;font-size:13.5px; }
thead { background:var(--blue-light); }
thead th { font-family:\'Poppins\',sans-serif;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:var(--blue);padding:11px 14px;text-align:left;border-bottom:2px solid var(--nav-border);cursor:pointer;user-select:none;white-space:nowrap; }
thead th:hover { background:var(--blue-light); }
thead th.sort-asc::after { content:" ▲";color:var(--accent);font-size:10px; }
thead th.sort-desc::after { content:" ▼";color:var(--accent);font-size:10px; }
tbody tr { border-bottom:1px solid var(--nav-border);transition:background .15s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--bg-card2); }
tbody td { padding:10px 14px;vertical-align:middle; }
.td-adresse { color:var(--text-muted);font-size:12.5px; }
.td-cp { font-size:12px;color:var(--text-muted);font-family:\'Courier New\',monospace; }
.prix-val { font-weight:700;color:var(--text); }
.prix-nd { color:var(--text-muted); }
.prix-bas { color:var(--prix-bas-color,#16a34a)!important;font-weight:800; }
.vide { text-align:center;padding:40px;color:var(--text-muted);font-style:italic; }
.erreur-api { background:var(--accent-light);border-left:4px solid var(--accent);border-radius:8px;padding:14px 18px;color:var(--accent-dark);font-size:14px; }
.btn-retour { display:inline-block;margin-top:1.4rem;background:var(--bg-card2);border:1px solid var(--nav-border);color:var(--blue);font-family:\'Poppins\',sans-serif;font-weight:700;font-size:13.5px;padding:9px 22px;border-radius:30px;text-decoration:none;transition:background .2s; }
.btn-retour:hover { background:var(--blue-light); }

.services-tags { display:flex;flex-wrap:wrap;gap:5px;margin-top:4px; }
.service-tag { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:var(--blue);background:var(--blue-light);border:1px solid var(--nav-border);border-radius:20px;padding:3px 9px;white-space:nowrap; }
.services-vide { font-size:11px;color:var(--text-muted);font-style:italic; }
.td-services { vertical-align:top; }
/* Colonne services masquée par défaut */
.col-services { display:none; }
/* Tableau pleine largeur sans scroll horizontal forcé */
.table-wrap { overflow-x:auto;border:1px solid var(--nav-border);border-radius:var(--radius);background:var(--bg-card); }
table { width:100%;border-collapse:collapse;font-size:13.5px; }
/* Séparateur visuel entre filtres carburants et filtre services */
.filtres-sep { width:1px;height:28px;background:var(--nav-border);margin:0 6px; }
';

require_once 'include/header.inc.php';
?>

<main id="main-content">
    <h1 class="page-title">Stations — <?= h($villeParam) ?></h1>

    <?php if ($erreurApi): ?>
        <div class="erreur-api">
            Impossible de contacter l'API des prix. Réessayez dans quelques instants.<br>
            <small>Source : data.economie.gouv.fr — flux instantané v2</small>
        </div>
    <?php elseif (empty($stations)): ?>
        <p class="vide">
            Aucune station trouvée pour « <?= h($villeParam) ?> ».<br>
            <?php if (!empty($cpsUtilises)): ?>
            <small>Codes postaux recherchés : <?= h(implode(', ', $cpsUtilises)) ?></small>
            <?php endif; ?>
        </p>
    <?php else: ?>
        <p class="resultats-info">
            <strong><?= count($stations) ?></strong> station(s) trouvée(s) pour
            <strong><?= h($villeParam) ?></strong>
        </p>
        <?php if (!empty($cpsUtilises)): ?>
        <div class="cp-badge">Codes postaux : <code><?= h(implode(', ', $cpsUtilises)) ?></code></div>
        <?php endif; ?>

        <div class="filtres">
            <span class="filtres-titre">Filtres carburants</span>
            <div class="filtres-row">
                <label><input type="checkbox" class="filtre-cb" data-col="col-sp95"   checked> SP95</label>
                <label><input type="checkbox" class="filtre-cb" data-col="col-sp98"   checked> SP98</label>
                <label><input type="checkbox" class="filtre-cb" data-col="col-gazole" checked> Gazole</label>
                <label><input type="checkbox" class="filtre-cb" data-col="col-e10"    checked> E10</label>
                <label><input type="checkbox" class="filtre-cb" data-col="col-gplc"   checked> GPL</label>
                <span class="filtres-sep" aria-hidden="true"></span>
                <label><input type="checkbox" id="cb-services"> Afficher les services</label>
            </div>
            <div class="filtres-row" style="margin-top:10px;">
                <label for="tri-prix" style="font-size:13px;font-weight:600;">Trier par prix :</label>
                <select id="tri-prix" aria-label="Trier les stations par prix">
                    <option value="">-- Aucun tri --</option>
                    <option value="asc">Croissant</option>
                    <option value="desc">Décroissant</option>
                </select>
                <button type="button" id="btn-appliquer" aria-label="Appliquer le tri par prix" class="btn-appliquer">Appliquer</button>
            </div>
        </div>

        <div class="table-wrap">
            <table id="tab">
                <thead>
                    <tr>
                        <th data-sort="nom">Station</th>
                        <th data-sort="adresse">Adresse</th>
                        <th data-sort="cp">CP</th>
                        <th data-sort="ville_api">Ville</th>
                        <th class="col-sp95"   data-sort="sp95">SP95</th>
                        <th class="col-sp98"   data-sort="sp98">SP98</th>
                        <th class="col-gazole" data-sort="gazole">Gazole</th>
                        <th class="col-e10"    data-sort="e10">E10</th>
                        <th class="col-gplc"   data-sort="gplc">GPL</th>
                        <th class="col-services">Services</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($stations as $s): ?>
                    <tr data-nom="<?= h($s['nom'] ?? '') ?>"
                        data-adresse="<?= h($s['adresse'] ?? '') ?>"
                        data-cp="<?= h($s['cp'] ?? '') ?>"
                        data-ville_api="<?= h($s['ville'] ?? '') ?>"
                        data-sp95="<?= val_prix($s['sp95_prix'] ?? null) ?>"
                        data-sp98="<?= val_prix($s['sp98_prix'] ?? null) ?>"
                        data-gazole="<?= val_prix($s['gazole_prix'] ?? null) ?>"
                        data-e10="<?= val_prix($s['e10_prix'] ?? null) ?>"
                        data-gplc="<?= val_prix($s['gplc_prix'] ?? null) ?>"
                        data-services="<?= h(implode(',', get_services_station($s['services_service'] ?? null))) ?>">
                        <td><?= h($s['nom'] ?? $s['id'] ?? 'N/A') ?></td>
                        <td class="td-adresse"><?= h($s['adresse'] ?? '—') ?></td>
                        <td class="td-cp"><?= h($s['cp'] ?? '—') ?></td>
                        <td class="td-adresse"><?= h($s['ville'] ?? '—') ?></td>
                        <td class="col-sp95"><?= fmt_prix($s['sp95_prix'] ?? null) ?></td>
                        <td class="col-sp98"><?= fmt_prix($s['sp98_prix'] ?? null) ?></td>
                        <td class="col-gazole"><?= fmt_prix($s['gazole_prix'] ?? null) ?></td>
                        <td class="col-e10"><?= fmt_prix($s['e10_prix'] ?? null) ?></td>
                        <td class="col-gplc"><?= fmt_prix($s['gplc_prix'] ?? null) ?></td>
                        <td class="td-services col-services">
                            <?php
                            $svcs = get_services_station($s['services_service'] ?? null);
                            if (!empty($svcs)):
                            ?>
                            <ul class="services-tags" aria-label="Services disponibles">
                                <?php foreach ($svcs as $svc): ?>
                                <li class="service-tag"><?= h($svc) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <span class="services-vide">Non renseignés</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
   <a href="index.php?style=<?= urlencode($styleActif) ?>" class="btn-retour" aria-label="Retourner à la carte (bas de page)">← Retour à la carte</a>
</main>


<script>
(function() {
    document.querySelectorAll('.filtre-cb').forEach(function(cb) {
        cb.addEventListener('change', function() {
            document.querySelectorAll('.' + this.dataset.col).forEach(function(el) {
                el.style.display = cb.checked ? '' : 'none';
            });
        });
    });
    /* Case à cocher Services : affiche/masque la colonne entière */
    var cbServices = document.getElementById('cb-services');
    if (cbServices) {
        cbServices.addEventListener('change', function() {
            var show = this.checked;
            document.querySelectorAll('.col-services').forEach(function(el) {
                /* 'table-cell' pour th/td, sinon le CSS display:none prend priorité */
                el.style.display = show ? 'table-cell' : 'none';
            });
        });
    }
    var sortCol = null, sortDir = 1;
    document.querySelectorAll('#tab thead th[data-sort]').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = this.dataset.sort;
            if (sortCol === col) { sortDir *= -1; }
            else { document.querySelectorAll('#tab thead th').forEach(function(h) { h.classList.remove('sort-asc','sort-desc'); }); sortCol = col; sortDir = 1; }
            this.classList.toggle('sort-asc', sortDir === 1);
            this.classList.toggle('sort-desc', sortDir === -1);
            var tbody = document.querySelector('#tab tbody');
            var rows  = Array.from(tbody.querySelectorAll('tr'));
            var numCols = ['sp95','sp98','gazole','e10','gplc'];
            rows.sort(function(a, b) {
                var va = a.dataset[col] || '', vb = b.dataset[col] || '';
                if (numCols.includes(col)) {
                    var na = parseFloat(va)||0, nb = parseFloat(vb)||0;
                    if (na===0) return 1; if (nb===0) return -1;
                    return (na-nb)*sortDir;
                }
                return va.localeCompare(vb,'fr')*sortDir;
            });
            rows.forEach(function(r) { tbody.appendChild(r); });
            document.querySelectorAll('#tab .prix-bas').forEach(function(el) { el.classList.remove('prix-bas'); });
            if (numCols.includes(sortCol) && sortDir===1) {
                var first = tbody.querySelector('tr');
                if (first) { var cell = first.querySelector('.col-'+sortCol+' .prix-val'); if (cell) cell.classList.add('prix-bas'); }
            }
        });
    });
})();

document.getElementById('btn-appliquer')?.addEventListener('click', function() {
    var ordre = document.getElementById('tri-prix').value;
    if (!ordre) return;
    var col = 'gazole';
    var cols = ['sp95','sp98','gazole','e10','gplc'];
    document.querySelectorAll('.filtre-cb').forEach(function(cb) {
        if (cb.checked) {
            cols.forEach(function(c) {
                if (cb.dataset.col === 'col-' + c) col = c;
            });
        }
    });
    var tbody = document.querySelector('#tab tbody');
    var rows  = Array.from(tbody.querySelectorAll('tr'));
    rows.sort(function(a, b) {
        var va = parseFloat(a.dataset[col]) || 0;
        var vb = parseFloat(b.dataset[col]) || 0;
        if (va === 0) return 1;
        if (vb === 0) return -1;
        return ordre === 'asc' ? va - vb : vb - va;
    });
    rows.forEach(function(r) { tbody.appendChild(r); });
    document.querySelectorAll('#tab .prix-bas').forEach(function(el) { el.classList.remove('prix-bas'); });
    if (ordre === 'asc') {
        var first = tbody.querySelector('tr');
        if (first) {
            var cell = first.querySelector('.col-' + col + ' .prix-val');
            if (cell) cell.classList.add('prix-bas');
        }
    }
});
</script>
<?php require_once 'include/footer.inc.php'; ?>
