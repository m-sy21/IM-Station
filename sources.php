<?php
/**
 * @file sources.php
 * @brief Sources et références — APIs, données et documentation utilisées
 *
 * Recense toutes les sources utilisées dans le projet :
 * APIs (carburants, géolocalisation JSON/XML, Ghibli),
 * données géographiques (CSV communes, carte IGN),
 * et références de la documentation PHP officielle.
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

$pageTitle = 'Sources';
$pageDesc  = 'Sources et API utilisées par le site i\'m Station.';
$pageActif = 'sources.php';
$pageStyle = "\n        /* ── Intro ── */\n        .sources-intro {\n            font-size: 15px;\n            color: var(--text-muted);\n            margin-bottom: 2rem;\n            max-width: 680px;\n        }\n\n        /* ── Sections ── */\n        .source-section {\n            margin-bottom: 2.4rem;\n        }\n        .source-section h2 {\n            font-family: 'Poppins', sans-serif;\n            font-size: 1rem;\n            font-weight: 700;\n            color: var(--blue);\n            margin-bottom: 0.5rem;\n            padding-bottom: 6px;\n            border-bottom: 2px solid var(--accent-light);\n            text-transform: uppercase;\n            letter-spacing: .05em;\n        }\n        .source-section p {\n            font-size: 13.5px;\n            color: var(--text-muted);\n            margin-bottom: 12px;\n            line-height: 1.65;\n        }\n\n        /* ── Tableau ── */\n        .table-sources {\n            width: 100%;\n            border-collapse: collapse;\n            font-size: 13.5px;\n            background: var(--bg-card);\n            border: 1px solid var(--nav-border);\n            border-radius: var(--radius);\n            overflow: hidden;\n        }\n        .table-sources thead {\n            background: var(--blue-light);\n        }\n        .table-sources thead th {\n            font-family: 'Poppins', sans-serif;\n            font-weight: 700;\n            font-size: 11.5px;\n            text-transform: uppercase;\n            letter-spacing: .07em;\n            color: var(--blue);\n            padding: 10px 16px;\n            text-align: left;\n            border-bottom: 2px solid var(--nav-border);\n        }\n        .table-sources tbody tr {\n            border-bottom: 1px solid var(--nav-border);\n            transition: background .15s;\n        }\n        .table-sources tbody tr:last-child {\n            border-bottom: none;\n        }\n        .table-sources tbody tr:hover {\n            background: var(--bg-card2);\n        }\n        .table-sources tbody td {\n            padding: 11px 16px;\n            color: var(--text);\n            vertical-align: middle;\n        }\n        .table-sources a {\n            color: var(--blue-mid);\n            font-weight: 700;\n            text-decoration: none;\n        }\n        .table-sources a:hover {\n            text-decoration: underline;\n        }\n        .badge-format {\n            display: inline-block;\n            background: var(--bg-card2);\n            border: 1px solid var(--nav-border);\n            border-radius: 4px;\n            padding: 2px 8px;\n            font-family: 'Courier New', monospace;\n            font-size: 11.5px;\n            color: var(--accent);\n            font-weight: 700;\n        }\n    ";

require_once 'include/header.inc.php';
?>

<main id="main-content">
    <h1 class="page-title">Sources &amp; Références</h1>
    <p class="sources-intro">
        Cette page recense l'ensemble des sources de données, APIs et références utilisées
        dans le cadre du projet <strong>i'm Station</strong> — L2 Informatique, UE Développement Web, CY Cergy Paris Université.
    </p>

    <!-- ── Données carburants ── -->
    <div class="source-section">
        <h2>Données carburants</h2>
        <p>
            Les prix des carburants proviennent de l'API officielle du gouvernement français,
            disponible en open data et mise à jour en temps réel.
        </p>
        <table class="table-sources">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Format</th>
                    <th>Utilisation dans le projet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="https://data.economie.gouv.fr/explore/dataset/prix-des-carburants-en-france-flux-instantane-v2/" target="_blank" rel="noopener">data.economie.gouv.fr</a></td>
                    <td><span class="badge-format">JSON</span></td>
                    <td>Prix des carburants en temps réel — pages Résultats et Statistiques</td>
                </tr>
                <tr>
                    <td><a href="https://www.prix-carburants.gouv.fr/rubrique/opendata/" target="_blank" rel="noopener">prix-carburants.gouv.fr</a></td>
                    <td><span class="badge-format">XML / CSV</span></td>
                    <td>Référence officielle open data des prix historiques</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ── Géolocalisation ── -->
    <div class="source-section">
        <h2>Géolocalisation par IP</h2>
        <p>
            La position géographique approximative du visiteur est détectée à partir de son adresse IP
            via une API externe, afin de suggérer les stations les plus proches.
        </p>
        <table class="table-sources">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Format</th>
                    <th>Utilisation dans le projet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="https://ipinfo.io" target="_blank" rel="noopener">ipinfo.io</a></td>
                    <td><span class="badge-format">JSON</span></td>
                    <td>Détection de la ville et de la région du visiteur — page d'accueil et page technique</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ── API Ghibli ── -->
    <div class="source-section">
        <h2>API Studio Ghibli</h2>
        <p>
            La page technique utilise l'API publique du Studio Ghibli pour illustrer
            la consommation d'une API REST externe au format JSON.
        </p>
        <table class="table-sources">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Format</th>
                    <th>Utilisation dans le projet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="https://ghibliapi.vercel.app/" target="_blank" rel="noopener">ghibliapi.vercel.app</a></td>
                    <td><span class="badge-format">JSON</span></td>
                    <td>Affichage d'un film aléatoire — page technique</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ── Données géographiques ── -->
    <div class="source-section">
        <h2>Données géographiques</h2>
        <p>
            Les données des communes, départements et régions de France proviennent de sources officielles.
            La carte des régions est issue de l'Institut Géographique National.
        </p>
        <table class="table-sources">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Format</th>
                    <th>Utilisation dans le projet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="https://www.data.gouv.fr" target="_blank" rel="noopener">data.gouv.fr</a></td>
                    <td><span class="badge-format">CSV</span></td>
                    <td>Liste des communes et codes postaux de France</td>
                </tr>
                <tr>
                    <td><a href="https://www.ign.fr" target="_blank" rel="noopener">IGN — Institut Géographique National</a></td>
                    <td><span class="badge-format">Image</span></td>
                    <td>Carte administrative des régions de France</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ── Références du cours ── -->
    <div class="source-section">
        <h2>Références du cours</h2>
        <p>
            Documentation PHP officielle utilisée pour la mise en œuvre des fonctionnalités du projet.
        </p>
        <table class="table-sources">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Sujet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="https://www.php.net/manual/fr/function.file-get-contents.php" target="_blank" rel="noopener">php.net — file_get_contents</a></td>
                    <td>Appel des APIs distantes côté serveur</td>
                </tr>
                <tr>
                    <td><a href="https://www.php.net/manual/fr/function.json-decode.php" target="_blank" rel="noopener">php.net — json_decode</a></td>
                    <td>Décodage des flux JSON retournés par les APIs</td>
                </tr>
                <tr>
                    <td><a href="https://www.php.net/manual/fr/function.setcookie.php" target="_blank" rel="noopener">php.net — setcookie</a></td>
                    <td>Gestion des cookies (style et historique)</td>
                </tr>
                <tr>
                    <td><a href="https://www.php.net/manual/fr/simplexml.examples-basic.php" target="_blank" rel="noopener">php.net — SimpleXML</a></td>
                    <td>Traitement des flux XML</td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'include/footer.inc.php'; ?>
