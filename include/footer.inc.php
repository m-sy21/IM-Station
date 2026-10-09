<?php
/**
 * @file footer.inc.php
 * @brief Pied de page HTML commun — génère le footer, bouton top et ferme body/html
 *
 * Fichier inclus en fin de chaque page via require_once.
 * Attend la variable $styleActif définie par la page appelante.
 * Affiche les liens du footer (plan du site, page technique, sources, à propos),
 * le navigateur du visiteur et le compteur de visites (cookie).
 *
 * @author Ines LAIFAOUI
 * @author Meriem SIMYA
 * @version 1.0
 * @date 2026
 * @copyright L2 Informatique — CY Cergy Paris Université
 */

//Détection du navigateur (côté serveur via User-Agent) 
$ua          = $_SERVER['HTTP_USER_AGENT'] ?? '';
$navigateur  = 'Autre';
if (str_contains($ua, 'Edg'))     $navigateur = 'Microsoft Edge';
elseif (str_contains($ua, 'OPR')) $navigateur = 'Opera';
elseif (str_contains($ua, 'Chrome')) $navigateur = 'Google Chrome';
elseif (str_contains($ua, 'Firefox')) $navigateur = 'Mozilla Firefox';
elseif (str_contains($ua, 'Safari'))  $navigateur = 'Safari';

//Lecture du compteur de visites 
$visites = (int)($_COOKIE['visites'] ?? 1);
?>

<footer>
    <span>&copy; 2026 &mdash; Ines LAIFAOUI &amp; Meriem SIMYA &mdash; L2 Informatique CY Cergy Paris Université</span>
    <span>
        <a href="plan.php?style=<?= urlencode($styleActif) ?>">Plan du site</a>
        &nbsp;|&nbsp;
        <a href="tech.php?style=<?= urlencode($styleActif) ?>">Page technique</a>
        &nbsp;|&nbsp;
        <a href="sources.php?style=<?= urlencode($styleActif) ?>">Sources</a>
    </span>
    <span class="footer-info">
        Votre navigateur&nbsp;: <?= htmlspecialchars($navigateur, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
        &nbsp;&mdash;&nbsp;
        Visites sur le site&nbsp;: <?= $visites ?>
    </span>
</footer>

<a href="#top" class="back-to-top" aria-label="Retour en haut de page">&#8593;</a>

</body>
</html>
