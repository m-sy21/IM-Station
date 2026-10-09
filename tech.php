<?php
/**
 * @file tech.php
 * @brief Page technique — démonstration des API JSON (Ghibli) et XML (ip2location)
 *
 * Cette page illustre la consommation d'APIs externes en PHP :
 * - API Studio Ghibli (JSON) : affichage d'un film aléatoire avec images,
 *   titre japonais (lang="ja"), année de sortie et description.
 * - API ipinfo.io (JSON) : géolocalisation approximative du visiteur par IP.
 * - API ip2location.io (XML) : géolocalisation via SimpleXML (format XML).
 * Un rafraîchissement de la page provoque l'affichage d'un nouveau film.
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

// API Ghibli : film aléatoire
$films = fetch_json(API_GHIBLI, 6);
$film  = ($films && count($films) > 0) ? $films[array_rand($films)] : null;

// Géolocalisation IP
$geo = get_geolocalisation();

$pageTitle = 'Page technique';
$pageDesc  = 'Démonstration des API JSON : Ghibli et géolocalisation IP.';
$pageActif = 'tech.php';
$pageStyle = "\n /* ── Section Ghibli ── */\n .ghibli-card {\n background: var(--bg-card);\n border: 2px solid var(--accent);\n border-radius: var(--radius);\n overflow: hidden;\n box-shadow: 0 4px 24px rgba(224,80,146,0.10);\n }\n\n .ghibli-meta {\n padding: 18px 22px 14px;\n }\n .ghibli-titre-jp {\n font-family: 'Playfair Display', serif;\n font-size: 1.15rem;\n color: var(--accent);\n margin-bottom: 4px;\n }\n .ghibli-titre-en {\n font-family: 'Poppins', sans-serif;\n font-weight: 700; font-size: 1.3rem;\n color: var(--blue); margin-bottom: 10px;\n }\n .ghibli-tags {\n display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;\n }\n .ghibli-tag {\n background: var(--bg-card2); border: 1px solid var(--nav-border);\n border-radius: 20px; padding: 3px 12px;\n font-size: 12px; font-weight: 600; color: var(--text-muted);\n }\n .ghibli-tag.annee {\n background: var(--accent-light); color: var(--accent-dark);\n border-color: var(--accent-light);\n }\n .ghibli-desc {\n font-size: 13.5px; color: var(--text-muted);\n line-height: 1.65; margin: 0;\n }\n\n /* ── Images Ghibli : remplissent tout le cadre sans bandes ── */\n .ghibli-figure {\n margin: 0;\n border-top: 1px solid var(--nav-border);\n overflow: hidden;\n }\n\n .ghibli-figure img {\n width: 100%;\n height: 420px;\n object-fit: cover;\n object-position: center center;\n display: block;\n }\n\n .ghibli-figure figcaption {\n text-align: center;\n font-size: 12px; font-style: italic;\n color: var(--text-muted);\n padding: 8px 16px;\n background: var(--bg-card2);\n border-top: 1px solid var(--nav-border);\n }\n\n .card-refresh {\n padding: 9px 22px;\n font-size: 12px; font-style: italic;\n color: var(--text-muted);\n background: var(--bg-card2);\n border-top: 1px solid var(--nav-border);\n }\n\n /* ── Section Géo ── */\n .geo-card {\n background: var(--bg-card);\n border: 1px solid var(--nav-border);\n border-radius: var(--radius);\n overflow: hidden;\n }\n .geo-card-header {\n padding: 16px 22px;\n border-bottom: 1px solid var(--nav-border);\n }\n .geo-card-header p { font-size: 13.5px; color: var(--text-muted); margin: 0; }\n .geo-result-label {\n font-family: 'Poppins', sans-serif;\n font-weight: 700; font-size: 13px;\n color: var(--accent); margin-bottom: 8px;\n }\n .geo-body {\n padding: 16px 22px;\n }\n .geo-list {\n list-style: disc;\n padding-left: 20px;\n font-size: 14px;\n line-height: 2;\n color: var(--text);\n }\n .geo-list li strong { color: var(--text-muted); }\n .geo-ip-block {\n background: var(--bg-card2);\n border: 1px solid var(--nav-border);\n border-radius: 8px;\n padding: 10px 16px;\n margin-bottom: 14px;\n font-family: 'Courier New', monospace;\n font-size: 13px;\n color: var(--accent);\n word-break: break-all;\n }\n .geo-ip-label {\n font-family: 'Nunito', sans-serif;\n font-size: 11px; text-transform: uppercase;\n letter-spacing: .08em; color: var(--text-muted);\n font-weight: 700; margin-bottom: 4px;\n }\n\n .api-source {\n font-size: 12px; color: var(--text-muted);\n margin-top: 7px;\n }\n .api-source a { color: var(--blue-mid); text-decoration: none; font-weight: 600; }\n .api-source a:hover { text-decoration: underline; }\n .erreur-api {\n text-align: center; padding: 28px;\n color: var(--text-muted); font-style: italic;\n background: var(--bg-card); border: 1px solid var(--nav-border);\n border-radius: var(--radius);\n }\n ";

require_once 'include/header.inc.php';
?>

<main id="main-content">
 <h1 class="page-title">Page Technique</h1>

 <!-- ══ FILM GHIBLI ══ -->
 <section>
 <h2>Film Ghibli aléatoire</h2>

 <?php if ($film): ?>
 <div class="ghibli-card">
 <!-- Infos texte -->
 <div class="ghibli-meta">
 <div lang="ja" class="ghibli-titre-jp">
 <?= htmlspecialchars($film["original_title"] ?? "—") ?>
 </div>
 <div class="ghibli-titre-en">
 <?= htmlspecialchars($film["title"] ?? "—") ?>
 </div>
 <div class="ghibli-tags">
 <span class="ghibli-tag annee">
 Année de sortie : <?= htmlspecialchars($film["release_date"] ?? "—") ?>
 </span>
 <span class="ghibli-tag"><?= htmlspecialchars($film["running_time"] ?? "—") ?> min</span>
 <span class="ghibli-tag">Réal. <?= htmlspecialchars($film["director"] ?? "—") ?></span>
 </div>
 <p class="ghibli-desc">
 <strong>Description :</strong> <?= htmlspecialchars($film["description"] ?? "") ?>
 </p>
 </div>

 <!-- Affiche -->
 <?php if (!empty($film["image"])): ?>
 <figure class="ghibli-figure">
 <img src="<?= htmlspecialchars($film['image']) ?>" alt="">
 <figcaption>Affiche — <?= htmlspecialchars($film["title"] ?? "") ?></figcaption>
 </figure>
 <?php endif; ?>

 <!-- Bannière -->
 <?php if (!empty($film["movie_banner"])): ?>
 <figure class="ghibli-figure">
 <img src="<?= htmlspecialchars($film['movie_banner']) ?>" alt="">
 <figcaption>Bannière — <?= htmlspecialchars($film["title"] ?? "") ?></figcaption>
 </figure>
 <?php endif; ?>

 <div class="card-refresh">↻ Rafraîchissez la page pour afficher un autre film aléatoirement</div>
 </div>
 <?php else: ?>
 <p class="erreur-api">️ Impossible de contacter l'API Ghibli.</p>
 <?php endif; ?>

 <p class="api-source">
 Source : <a href="https://ghibliapi.vercel.app/films" target="_blank" rel="noopener">ghibliapi.vercel.app/films</a> — format JSON
 </p>
 </section>

 <!-- ══ GÉOLOCALISATION ══ -->
 <section>
 <h2>Localisation par IP</h2>

 <?php if ($geo): ?>
 <div class="geo-card">
 <div class="geo-card-header">
 <p class="geo-result-label">Résultat</p>
 <div class="geo-ip-label">Adresse IP détectée</div>
 <div class="geo-ip-block"><?= htmlspecialchars($geo["ip"] ?? $ip) ?></div>
 <ul class="geo-list">
 <li><strong>Ville :</strong> <?= htmlspecialchars($geo["city"] ?? "—") ?></li>
 <li><strong>Région :</strong> <?= htmlspecialchars($geo["region"] ?? "—") ?></li>
 <li><strong>Pays :</strong> <?= htmlspecialchars($geo["country"] ?? "—") ?></li>
 <?php if (!empty($geo["timezone"])): ?>
 <li><strong>Fuseau horaire :</strong> <?= htmlspecialchars($geo["timezone"]) ?></li>
 <?php endif; ?>
 <?php if (!empty($geo["loc"])): ?>
 <li><strong>Coordonnées GPS :</strong> <?= htmlspecialchars($geo["loc"]) ?></li>
 <?php endif; ?>
 </ul>
 </div>
 </div>
 <?php else: ?>
 <p class="erreur-api">️ Impossible de contacter l'API de géolocalisation.</p>
 <?php endif; ?>

 <p class="api-source">
 Source : <a href="https://ipinfo.io" target="_blank" rel="noopener">ipinfo.io</a> — format JSON
 </p>
 </section>
</main>

<?php require_once 'include/footer.inc.php'; ?>

