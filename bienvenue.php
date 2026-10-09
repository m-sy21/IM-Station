<?php
/**
 * @file bienvenue.php
 * @brief Page À propos — présentation du projet i'm Station et des auteures
 *
 * Présente le projet i'm Station, ses objectifs, sa charte graphique,
 * et les membres de l'équipe (SIMYA Meriem et LAIFAOUI Ines).
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

$pageTitle = 'À propos';
$pageDesc  = 'Présentation du site i\'m Station et de ses auteures.';
$pageActif = 'bienvenue.php';
$pageStyle = "\n /* Page bienvenue plein écran */\n body { overflow-x: hidden; }\n\n .bienvenue-hero {\n min-height: calc(100vh - 171px);\n display: flex;\n flex-direction: column;\n align-items: center;\n justify-content: center;\n text-align: center;\n padding: 3rem 2rem;\n background: var(--bg);\n position: relative;\n overflow: hidden;\n }\n\n /* Cercles décoratifs en arrière-plan */\n .bienvenue-hero::before {\n content: '';\n position: absolute;\n width: 600px; height: 600px;\n border-radius: 50%;\n background: radial-gradient(circle, rgba(224,80,146,0.07) 0%, transparent 70%);\n top: -100px; right: -100px;\n pointer-events: none;\n }\n .bienvenue-hero::after {\n content: '';\n position: absolute;\n width: 400px; height: 400px;\n border-radius: 50%;\n background: radial-gradient(circle, rgba(26,42,94,0.06) 0%, transparent 70%);\n bottom: -80px; left: -80px;\n pointer-events: none;\n }\n\n .bienvenue-titre {\n font-family: 'Playfair Display', serif;\n font-size: clamp(2.2rem, 5vw, 3.5rem);\n font-weight: 800;\n color: var(--text);\n line-height: 1.15;\n margin-bottom: 0.5rem;\n }\n .bienvenue-titre span { color: var(--accent); }\n\n .bienvenue-sous {\n font-family: 'Poppins', sans-serif;\n font-size: clamp(1rem, 2.5vw, 1.3rem);\n color: var(--text-muted);\n font-weight: 600;\n margin-bottom: 0.5rem;\n }\n\n .bienvenue-desc {\n font-size: 15px;\n color: var(--text-muted);\n max-width: 560px;\n margin: 0 auto 2.5rem;\n line-height: 1.7;\n }\n\n /* Stats rapides */\n .bienvenue-stats {\n display: flex;\n gap: 2rem;\n justify-content: center;\n flex-wrap: wrap;\n margin-bottom: 3rem;\n }\n .stat-item {\n display: flex;\n flex-direction: column;\n align-items: center;\n }\n .stat-nb {\n font-family: 'Playfair Display', serif;\n font-size: 2rem;\n font-weight: 800;\n color: var(--accent);\n line-height: 1;\n }\n .stat-label {\n font-family: 'Poppins', sans-serif;\n font-size: 11px;\n font-weight: 600;\n color: var(--text-muted);\n text-transform: uppercase;\n letter-spacing: 0.1em;\n margin-top: 3px;\n }\n\n /* CTA */\n .bienvenue-cta {\n display: flex;\n gap: 14px;\n flex-wrap: wrap;\n justify-content: center;\n margin-bottom: 3rem;\n }\n .cta-btn-primary {\n background: var(--accent); color: #fff;\n font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 15px;\n padding: 14px 36px; border-radius: 40px;\n text-decoration: none;\n box-shadow: 0 6px 24px rgba(224,80,146,0.35);\n transition: background .2s, transform .15s;\n }\n .cta-btn-primary:hover { background: var(--accent-dark); transform: translateY(-2px); }\n .cta-btn-secondary {\n background: var(--bg-card); color: var(--blue);\n font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 15px;\n padding: 14px 36px; border-radius: 40px;\n text-decoration: none; border: 2px solid var(--nav-border);\n transition: border-color .2s, transform .15s;\n }\n .cta-btn-secondary:hover { border-color: var(--accent); transform: translateY(-2px); }\n\n /* Carburants */\n .carburants-badges {\n display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;\n }\n .carb-badge {\n background: var(--bg-card); border: 1px solid var(--nav-border);\n border-radius: 20px; padding: 5px 16px;\n font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 700;\n color: var(--blue);\n }\n\n /* Fonctionnalités */\n .features {\n display: grid;\n grid-template-columns: repeat(4, 1fr);\n gap: 16px;\n width: 100%;\n max-width: 960px;\n padding: 2rem 1.5rem;\n margin: 0 auto;\n }\n .feature-card {\n background: var(--bg-card);\n border: 1px solid var(--nav-border);\n border-radius: 12px;\n padding: 22px 20px;\n text-align: center;\n box-shadow: 0 2px 12px rgba(26,42,94,0.07);\n }\n .feature-icon {\n width: 44px; height: 44px;\n background: var(--accent-light); border-radius: 50%;\n display: flex; align-items: center; justify-content: center;\n margin: 0 auto 12px;\n font-size: 20px;\n }\n .feature-titre {\n font-family: 'Poppins', sans-serif;\n font-weight: 700; font-size: 14px;\n color: var(--blue); margin-bottom: 6px;\n }\n .feature-desc { font-size: 13px; color: var(--text-muted); line-height: 1.5; }\n \n /* Carte présentation auteurs */\n .auteurs-card {\n  background: var(--bg-card);\n  border: 2px solid var(--accent);\n  border-radius: 14px;\n  padding: 22px 28px;\n  max-width: 620px;\n  margin: 0 auto 2rem;\n  text-align: left;\n  box-shadow: 0 4px 20px rgba(0,0,0,0.2);\n }\n .auteurs-intro {\n  font-family: 'Poppins', sans-serif;\n  font-size: 15px;\n  font-weight: 600;\n  color: var(--blue);\n  margin-bottom: 10px;\n  line-height: 1.6;\n }\n .auteurs-intro strong { color: var(--accent); }\n .auteurs-projet {\n  font-size: 14px;\n  color: var(--text-muted);\n  line-height: 1.7;\n  margin: 0;\n }\n";

require_once 'include/header.inc.php';
?>

<main id="main-content">
 <div class="bienvenue-hero">

 <h1 class="bienvenue-titre">
 Bienvenue sur i'm Station :<br><span>Votre allié contre la vie chère</span>
 </h1>
 <p class="bienvenue-sous">Données officielles en temps réel — France métropolitaine</p>

 <!-- Présentation des auteures -->
 <div class="auteurs-card">
  <p class="auteurs-intro">
   Nous sommes <strong>Ines LAIFAOUI</strong> et <strong>Meriem SIMYA</strong>,
   deux étudiantes de 20 ans en deuxième année de Licence Informatique
   à <strong>CY Cergy Paris Université</strong>.
  </p>
  <p class="auteurs-projet">
   Dans le cadre de notre cursus, nous avons développé ce projet web :
   une plateforme de consultation des prix des carburants en France métropolitaine.
   L'objectif est de permettre aux utilisateurs de trouver rapidement
   la station-service la moins chère près de chez eux, avec une interface
   fluide et moderne incluant un mode nuit et une interface responsive.
  </p>
 </div>

 <p class="bienvenue-desc">
  i'm Station vous permet de localiser rapidement les stations-service
  de votre ville et de comparer les prix de tous les carburants disponibles.
 </p>

 <!-- Stats -->
 <div class="bienvenue-stats">
 <div class="stat-item">
 <span class="stat-nb">35 000+</span>
 <span class="stat-label">communes</span>
 </div>
 <div class="stat-item">
 <span class="stat-nb">13</span>
 <span class="stat-label">régions</span>
 </div>
 <div class="stat-item">
 <span class="stat-nb">5</span>
 <span class="stat-label">carburants</span>
 </div>
 <div class="stat-item">
 <span class="stat-nb">Temps réel</span>
 <span class="stat-label">mise à jour</span>
 </div>
 </div>

 <!-- Boutons CTA -->
 <div class="bienvenue-cta">
 <a href="index.php?style=<?= urlencode($styleActif) ?>" class="cta-btn-primary">
 Rechercher une station
 </a>
 <a href="stats.php?style=<?= urlencode($styleActif) ?>" class="cta-btn-secondary">
 Voir les statistiques
 </a>
 </div>

 <!-- Badges carburants -->
 <div class="carburants-badges">
 <span class="carb-badge">SP95</span>
 <span class="carb-badge">SP98</span>
 <span class="carb-badge">Gazole</span>
 <span class="carb-badge">E10</span>
 <span class="carb-badge">GPLc</span>
 </div>
 </div>

 <!-- Fonctionnalités -->
 <div class="features">
 <div class="feature-card">
 <div class="feature-icon">
 <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
 </div>
 <p class="feature-titre">Recherche géolocalisée</p>
 <p class="feature-desc">Trouvez les stations près de votre position actuelle grâce à la géolocalisation IP.</p>
 </div>
 <div class="feature-card">
 <div class="feature-icon">
 <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
 </div>
 <p class="feature-titre">Carte interactive</p>
 <p class="feature-desc">Sélectionnez votre région sur la carte de France puis affinez par département et ville.</p>
 </div>
 <div class="feature-card">
 <div class="feature-icon">
 <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
 </div>
 <p class="feature-titre">Prix en temps réel</p>
 <p class="feature-desc">Données officielles du gouvernement français mises à jour en continu.</p>
 </div>
 <div class="feature-card">
 <div class="feature-icon">
 <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
 </div>
 <p class="feature-titre">Historique personnel</p>
 <p class="feature-desc">Vos dernières recherches sont mémorisées pour un accès rapide.</p>
 </div>
 </div>
</main>

<?php require_once 'include/footer.inc.php'; ?>
