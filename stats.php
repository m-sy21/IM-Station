<?php
/**
 * @file stats.php
 * @brief Page de statistiques — histogramme Chart.js et prix moyens nationaux
 *
 * Affiche les statistiques d'utilisation du site :
 * - Histogramme Chart.js des villes les plus consultées (données CSV serveur)
 * - Prix moyens nationaux par carburant (API data.economie.gouv.fr, JSON)
 * - Historique personnel de l'utilisateur (cookie)
 * - Nombre total de consultations enregistrées
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

$statsCSV  = lire_csv_consultations();
$totalCSV  = $statsCSV['total'];
$parVille  = $statsCSV['par_ville'];
$recentes  = $statsCSV['recentes'];
$topVilles = array_slice($parVille, 0, 20, true);

// Données JSON pour Chart.js
$chartLabels = json_encode(array_keys($topVilles),   JSON_UNESCAPED_UNICODE);
$chartData   = json_encode(array_values($topVilles), JSON_UNESCAPED_UNICODE);

$historiqueCookie = get_historique();

// Prix moyens nationaux
$apiUrl  = API_CARBURANTS_BASE . "?limit=100&timezone=Europe%2FParis";
$apiData = fetch_json($apiUrl);
$moyennes = ["sp95"=>[],"sp98"=>[],"gazole"=>[],"e10"=>[],"gplc"=>[]];
if ($apiData && isset($apiData["results"])) {
    foreach ($apiData["results"] as $s) {
        foreach (["sp95","sp98","gazole","e10","gplc"] as $carb) {
            $k = $carb . "_prix";
            if (!empty($s[$k]) && (float)$s[$k] > 0) $moyennes[$carb][] = (float)$s[$k];
        }
    }
}
$carburantsInfo = [
    "SP95"   => !empty($moyennes["sp95"])   ? array_sum($moyennes["sp95"])   / count($moyennes["sp95"])   : null,
    "SP98"   => !empty($moyennes["sp98"])   ? array_sum($moyennes["sp98"])   / count($moyennes["sp98"])   : null,
    "Gazole" => !empty($moyennes["gazole"]) ? array_sum($moyennes["gazole"]) / count($moyennes["gazole"]) : null,
    "E10"    => !empty($moyennes["e10"])    ? array_sum($moyennes["e10"])    / count($moyennes["e10"])    : null,
    "GPLc"   => !empty($moyennes["gplc"])   ? array_sum($moyennes["gplc"])   / count($moyennes["gplc"])   : null,
];

$pageTitle = 'Statistiques';
$pageDesc  = "Statistiques d'utilisation du site i'm Station.";
$pageActif = 'stats.php';
$pageStyle = '
.prix-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-top:6px; }
.prix-card { background:var(--bg-card); border:1px solid var(--nav-border); border-radius:var(--radius); padding:18px 16px; text-align:center; }
.prix-carburant { font-family:\'Poppins\',sans-serif; font-weight:800; font-size:13px; color:var(--blue); margin-bottom:8px; }
.prix-val-big { font-family:\'Playfair Display\',serif; font-size:1.55rem; font-weight:700; color:var(--accent); line-height:1.2; }
.prix-unite { font-size:.85rem; color:var(--text-muted); }
.prix-nd { color:var(--text-muted); font-size:13px; font-style:italic; }
.prix-source { font-size:13px; color:var(--text-muted); margin-top:8px; font-style:italic; text-align:center; }
.stat-total { background:var(--bg-card); border:1px solid var(--nav-border); border-radius:var(--radius); padding:20px 22px; margin-top:6px; display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
.stat-total-nb { font-family:\'Playfair Display\',serif; font-size:2.8rem; font-weight:800; color:var(--accent); line-height:1; }
.stat-total-label { font-size:14px; color:var(--text-muted); }
.stat-total-label strong { color:var(--blue); }
.stat-source { font-size:13px; color:var(--text-muted); margin-top:4px; font-style:italic; }
.chart-canvas { max-height: 420px; }
.chart-container { background:var(--bg-card); border:1px solid var(--nav-border); border-radius:var(--radius); padding:24px 20px; margin-top:8px; }
.chart-title { font-family:\'Poppins\',sans-serif; font-size:13px; font-weight:700; color:var(--text-muted); text-align:center; margin-bottom:16px; }
.recentes-table { width:100%; border-collapse:collapse; font-size:13.5px; margin-top:8px; }
.recentes-table th { font-family:\'Poppins\',sans-serif; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:var(--blue); padding:10px 14px; text-align:left; background:var(--blue-light); border-bottom:2px solid var(--nav-border); }
.recentes-table td { padding:9px 14px; border-bottom:1px solid var(--nav-border); }
.recentes-table tr:last-child td { border-bottom:none; }
.recentes-table tr:hover td { background:var(--bg-card2); }
.recentes-table a { color:var(--blue); font-weight:600; text-decoration:none; }
.recentes-table a:hover { text-decoration:underline; }
.td-date { color:var(--text-muted); font-size:12px; font-family:\'Courier New\',monospace; }
.table-wrap { border:1px solid var(--nav-border); border-radius:var(--radius); overflow:hidden; background:var(--bg-card); }
.villes-list { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
.ville-tag { background:var(--bg-card); border:1px solid var(--nav-border); border-radius:20px; padding:5px 14px; font-size:13px; font-weight:600; color:var(--blue); text-decoration:none; transition:background .15s; }
.ville-tag:hover { background:var(--blue-light); }
.vide-stats { font-size:13.5px; color:var(--text-muted); font-style:italic; padding:14px 0; }
';
require_once 'include/header.inc.php';
?>

<main id="main-content">
    <h1 class="page-title">Statistiques</h1>

    <!-- Prix moyens nationaux -->
    <section>
        <h2>Prix moyens nationaux</h2>
        <p>Calculés en temps réel à partir des données officielles (flux instantané).</p>
        <div class="prix-grid">
            <?php foreach ($carburantsInfo as $nom => $moy): ?>
            <div class="prix-card">
                <p class="prix-carburant"><?= htmlspecialchars($nom) ?></p>
                <?php if ($moy !== null): ?>
                    <div class="prix-val-big">
                        <?= number_format($moy, 3, ",", " ") ?>
                        <span class="prix-unite">€/L</span>
                    </div>
                <?php else: ?>
                    <p class="prix-nd">Non disponible</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="prix-source">
            Source : <a href="https://data.economie.gouv.fr" class="lien-gouv" target="_blank" rel="noopener noreferrer">data.economie.gouv.fr</a>
            — échantillon de 100 stations (flux instantané)
        </p>
    </section>

    <!-- Compteur global -->
    <section>
        <h2>Consultations globales</h2>
        <div class="stat-total">
            <div class="stat-total-nb"><?= number_format($totalCSV) ?></div>
            <div>
                <p class="stat-total-label">consultation(s) enregistrée(s) par <strong>tous les visiteurs</strong></p>
                <p class="stat-source">Source : <code>consultations.csv</code> — horodaté, accès concurrent sécurisé</p>
            </div>
        </div>
    </section>

    <!-- Histogramme villes les plus consultées -->
    <section>
        <h2>Villes les plus consultées</h2>
        <p>Total des consultations : <strong><?= number_format($totalCSV) ?></strong></p>
        <?php if (empty($topVilles)): ?>
            <p class="vide-stats">Aucune donnée — effectuez des recherches pour alimenter les statistiques.</p>
        <?php else: ?>
        <div class="chart-container">
            <p class="chart-title">Nombre de consultations par ville (top <?= count($topVilles) ?>)</p>
            <canvas id="chartVilles"
                    aria-label="Histogramme des villes les plus consultées"
                    role="img"
                    class="chart-canvas">
            </canvas>
        </div>
        <?php endif; ?>
    </section>

    <!-- 10 dernières consultations -->
    <section>
        <h2>10 dernières consultations</h2>
        <?php if (empty($recentes)): ?>
            <p class="vide-stats">Aucune consultation encore enregistrée.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="recentes-table">
                <thead>
                    <tr>
                        <th scope="col">Date / Heure</th>
                        <th scope="col">Ville consultée</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentes as [$horodatage, $ville]): ?>
                    <tr>
                        <td class="td-date"><?= htmlspecialchars($horodatage) ?></td>
                        <td>
                            <a href="resultats.php?style=<?= urlencode($styleActif) ?>&ville=<?= urlencode($ville) ?>">
                                <?= htmlspecialchars($ville) ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>

    <!-- Historique perso -->
    <section>
        <h2>Vos recherches récentes</h2>
        <?php if (empty($historiqueCookie)): ?>
            <p class="vide-stats">
                Aucune ville consultée depuis ce navigateur —
                <a href="index.php?style=<?= urlencode($styleActif) ?>" class="lien-gouv">recherchez une ville</a>.
            </p>
        <?php else: ?>
            <div class="villes-list">
                <?php foreach ($historiqueCookie as $ville): ?>
                <a href="resultats.php?style=<?= urlencode($styleActif) ?>&ville=<?= urlencode($ville) ?>"
                   class="ville-tag"><?= htmlspecialchars($ville) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>



<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" 
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function() {
    var canvas = document.getElementById('chartVilles');
    if (!canvas) return;
    var isNuit = <?= json_encode($styleActif === 'alternatif') ?>;

    var labels = <?= $chartLabels ?>;
    var data   = <?= $chartData ?>;
    var maxVal = Math.max.apply(null, data);

    // Dégradé de couleurs : du rouge-rose foncé au rouge-rose clair selon la valeur
    var colors = data.map(function(v) {
        var ratio = v / maxVal;
        var r = Math.round(192 - ratio * 40);
        var g = Math.round(48  + ratio * 10);
        var b = Math.round(90  - ratio * 20);
        return 'rgba(' + r + ',' + g + ',' + b + ',0.82)';
    });
    var borders = data.map(function(v) {
        var ratio = v / maxVal;
        var r = Math.round(143 - ratio * 30);
        var g = Math.round(30  + ratio * 8);
        var b = Math.round(63  - ratio * 15);
        return 'rgb(' + r + ',' + g + ',' + b + ')';
    });

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nombre de consultations',
                data: data,
                backgroundColor: colors,
                borderColor: borders,
                borderWidth: 1.5,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { family: "'Poppins', sans-serif", size: 12 },
                        color: isNuit ? '#c0c8e0' : '#1a1f2e'
                    }
                },
                tooltip: {
                    backgroundColor: isNuit ? 'rgba(16,22,40,0.95)' : 'rgba(13,28,69,0.92)',
                    titleColor: '#eef0f8',
                    bodyColor: '#c0c8e0',
                    titleFont: { family: "'Poppins', sans-serif", size: 13, weight: 'bold' },
                    bodyFont:  { family: "'Poppins', sans-serif", size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            return '  ' + ctx.parsed.y + ' consultation' + (ctx.parsed.y > 1 ? 's' : '');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        color: isNuit ? '#c0c8e0' : '#374151',
                        font: { family: "'Poppins', sans-serif", size: 11 }
                    },
                    grid: { color: isNuit ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' },
                    border: { dash: [4, 4] }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 30,
                        color: isNuit ? '#c0c8e0' : '#374151',
                        font: { family: "'Poppins', sans-serif", size: 11 }
                    },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
<?php require_once 'include/footer.inc.php'; ?>