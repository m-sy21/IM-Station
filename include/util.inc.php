<?php
/**
 * @file util.inc.php
 * @brief Constantes de configuration et utilitaires globaux du site
 *
 * Centralise les constantes (URLs d'API, chemins de fichiers, limites)
 * et les fonctions utilitaires mineures utilisées dans tout le projet.
 *
 * Inclus via : require_once 'include/util.inc.php';
 *
 * @author Ines LAIFAOUI
 * @author Meriem SIMYA
 * @version 1.0
 * @date 2026
 * @copyright L2 Informatique — CY Cergy Paris Université
 */

// ── Chemins fichiers ──────────────────────────────────────────────────────────

/** @var string Chemin absolu vers le fichier CSV des communes */
define('CSV_COMMUNES',      __DIR__ . '/../clean_postcodes.csv');

/** @var string Chemin absolu vers le fichier CSV des consultations */
define('CSV_CONSULTATIONS', __DIR__ . '/../consultations.csv');

// ── URLs des API ──────────────────────────────────────────────────────────────

/** @var string URL de base de l'API carburants (data.economie.gouv.fr) */
define('API_CARBURANTS_BASE',
    'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets'
    . '/prix-des-carburants-en-france-flux-instantane-v2/records'
);

/** @var string URL de base de l'API Ghibli */
define('API_GHIBLI', 'https://ghibliapi.vercel.app/films');

/** @var string URL de base de l'API géolocalisation IP */
define('API_IPINFO', 'https://ipinfo.io/');

// ── Limites ───────────────────────────────────────────────────────────────────

/** @var int Nombre maximum de résultats par requête API carburants */
define('API_LIMIT',        100);

/** @var int Nombre maximum de stations récupérées au total */
define('API_MAX_RESULTS',  500);

/** @var int Nombre de villes dans l'historique personnel (cookie) */
define('HISTORIQUE_MAX',   5);

/** @var int Durée de validité des cookies en secondes (30 jours) */
define('COOKIE_DUREE',     30 * 24 * 3600);

// ── IP de test (remplace localhost en développement) ─────────────────────────

/** @var string IP de substitution pour les environnements localhost */
define('IP_TEST', '193.54.115.192');

// ══════════════════════════════════════════════════════════════════════════════
// FONCTIONS UTILITAIRES
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Récupère l'adresse IP réelle du visiteur
 *
 * Gère les proxies (X-Forwarded-For) et remplace les IP locales
 * par une IP de test (définie dans IP_TEST).
 *
 * @return string Adresse IP du visiteur
 */
function get_ip_visiteur(): string {
    $ip = !empty($_SERVER["HTTP_X_FORWARDED_FOR"])
        ? trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0])
        : ($_SERVER["REMOTE_ADDR"] ?? "");
    if ($ip === "127.0.0.1" || $ip === "::1") $ip = IP_TEST;
    return $ip;
}

/**
 * @brief Échappe une chaîne pour une utilisation sécurisée en HTML
 *
 * Alias court de htmlspecialchars() avec les options standards.
 *
 * @param  string $str Chaîne à échapper
 * @return string      Chaîne échappée
 */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * @brief Redirige vers une URL et arrête l'exécution
 *
 * @param  string $url URL de destination
 * @return never
 */
function redirect(string $url): never {
    header("Location: " . $url);
    exit;
}
