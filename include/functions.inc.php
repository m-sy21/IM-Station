<?php
/**
 * @file functions.inc.php
 * @brief Fonctions PHP partagées par toutes les pages du site
 *
 * Ce fichier centralise toutes les fonctions réutilisables :
 * gestion du cookie de style, géolocalisation IP, accès à l'API carburants,
 * lecture du CSV des communes, historique des consultations.
 *
 * Inclus en début de chaque page via : require_once 'include/functions.inc.php';
 *
 * @author Ines LAIFAOUI
 * @author Meriem SIMYA
 * @version 1.0
 * @date 2026
 * @copyright L2 Informatique — CY Cergy Paris Université
 */

// ══════════════════════════════════════════════════════════════════════════════
// COOKIE STYLE (mode jour / mode nuit)
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Gère le cookie de style graphique (mode jour/nuit)
 *
 * Lit le paramètre GET "style", l'écrit dans un cookie si valide,
 * ou relit le cookie existant. Supprime le cookie si sa valeur est invalide.
 *
 * @return string "standard" (mode jour) ou "alternatif" (mode nuit)
 */
function gerer_cookie_style(): string {
    $valides = ["standard", "alternatif"];
    $style   = "standard";
    if (isset($_GET["style"]) && in_array($_GET["style"], $valides, true)) {
        $style = $_GET["style"];
        setcookie("style", $style, [
            "expires"  => time() + 30 * 24 * 3600,
            "path"     => "/", //pour ne pas interférer avec les autres sites du serveur partagé.
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    } elseif (isset($_COOKIE["style"]) && in_array($_COOKIE["style"], $valides, true)) {
        $style = $_COOKIE["style"];
    } elseif (isset($_COOKIE["style"])) {
        // Valeur invalide → supprimer le cookie
        setcookie("style", "", ["expires" => time() - 3600, "path" => "/"]);
    }
    // Incrémenter le compteur de visites
    $visites = (int)($_COOKIE['visites'] ?? 0) + 1;
    setcookie('visites', (string)$visites, [
        'expires'  => time() + 365 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    return $style;
}
/**
 * @brief Calcule les variables de style à partir du style actif
 *
 * Retourne un tableau avec toutes les variables nécessaires à l'affichage
 * du header (CSS, images de switch, textes alternatifs).
 *
 * @param  string $styleActif "standard" ou "alternatif"
 * @return array  Tableau associatif [css, imgSwitch, altSwitch, styleOppose]
 */
function get_vars_style(string $styleActif): array {
    $styleOppose = ($styleActif === "alternatif") ? "standard" : "alternatif";
    return [
        "styleOppose" => $styleOppose,
        "css"         => ($styleActif === "alternatif") ? "style_nuit.css" : "style.css",
        "imgSwitch"   => ($styleActif === "alternatif") ? "images/soleil.jpg" : "images/lune.jpg",
        "altSwitch"   => ($styleActif === "alternatif") ? "Mode jour" : "Mode nuit",
    ];
}

// ══════════════════════════════════════════════════════════════════════════════
// HISTORIQUE PERSONNEL (cookie côté client)
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Lit l'historique des villes consultées depuis le cookie
 *
 * @return array Liste des villes récemment consultées (max 5)
 */
function get_historique(): array {
    if (!isset($_COOKIE["historique"])) return [];
    $data = json_decode($_COOKIE["historique"], true);
    return is_array($data) ? $data : [];
}

/**
 * @brief Met à jour le cookie d'historique des villes consultées
 *
 * Ajoute la ville en tête de liste, supprime les doublons,
 * limite à 5 entrées et écrit le cookie JSON.
 *
 * @param  string $ville Nom de la ville à ajouter à l'historique
 * @return void
 */
function maj_cookie_historique(string $ville): void {
    $hist = get_historique();
    $hist = array_values(array_filter($hist, fn($v) => strtolower($v) !== strtolower($ville)));
    array_unshift($hist, $ville);
    $hist = array_slice($hist, 0, 5);
    setcookie("historique", json_encode($hist), [
        "expires"  => time() + 30 * 24 * 3600,
        "path"     => "/",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

// ══════════════════════════════════════════════════════════════════════════════
// STOCKAGE CSV CÔTÉ SERVEUR (consultations horodatées)
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Enregistre une consultation dans consultations.csv
 *
 * Format CSV horodaté : "horodatage","ville","ip_hash"
 * Crée le fichier avec en-tête si inexistant.
 * Utilise LOCK_EX pour gérer les accès concurrents.
 *
 * @param  string $ville Nom de la ville consultée
 * @return void
 */
function enregistrer_consultation_csv(string $ville): void {
    $csvPath = __DIR__ . "/../consultations.csv";
    if (!file_exists($csvPath)) {
        file_put_contents($csvPath, "\"horodatage\",\"ville\",\"ip_hash\"\n", LOCK_EX);
    }
    $ip = !empty($_SERVER["HTTP_X_FORWARDED_FOR"])
        ? trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0])
        : ($_SERVER["REMOTE_ADDR"] ?? "");
    $ipHash = substr(md5($ip . "sel_imstation"), 0, 8);
    $ligne  = '"' . date("Y-m-d H:i:s") . '","' . addslashes($ville) . '","' . $ipHash . '"' . "\n";
    $f = fopen($csvPath, "a");
    if ($f !== false) {
        flock($f, LOCK_EX);
        fwrite($f, $ligne);
        flock($f, LOCK_UN);
        fclose($f);
    }
}

/**
 * @brief Lit le fichier consultations.csv et agrège les statistiques globales
 *
 * Retourne le nombre total de consultations, les villes triées par popularité
 * et les 10 dernières consultations.
 *
 * @return array ['total' => int, 'par_ville' => array, 'recentes' => array]
 */
function lire_csv_consultations(): array {
    $csvPath = __DIR__ . "/../consultations.csv";
    $result  = ['total' => 0, 'par_ville' => [], 'recentes' => []];
    if (!file_exists($csvPath)) return $result;

    $f = fopen($csvPath, "r");
    if ($f === false) return $result;

    fgetcsv($f, 0, ",", "\"", "\\"); // ignorer l'en-tête
    $lignes = [];
    while (($row = fgetcsv($f, 0, ",", "\"", "\\")) !== false) {
        if (count($row) < 2) continue;
        $horodatage = trim($row[0]);
        $ville      = trim($row[1]);
        if ($ville === "") continue;
        $result['total']++;
        $result['par_ville'][$ville] = ($result['par_ville'][$ville] ?? 0) + 1;
        $lignes[] = [$horodatage, $ville];
    }
    fclose($f);

    arsort($result['par_ville']);
    $result['recentes'] = array_reverse(array_slice($lignes, -10));
    return $result;
}

// ══════════════════════════════════════════════════════════════════════════════
// REQUÊTES HTTP / API
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Effectue une requête HTTP GET et retourne le JSON décodé
 *
 * @param  string $url     URL à interroger
 * @param  int    $timeout Délai maximum en secondes (défaut : 8)
 * @return array|null Tableau PHP décodé ou null en cas d'erreur réseau/JSON
 */
function fetch_json(string $url, int $timeout = 8): ?array {
    $ctx = stream_context_create(["http" => [
        "timeout" => $timeout,
        "header"  => "User-Agent: Mozilla/5.0 (compatible; ImStation/1.0)\r\n"
    ]]);
    $r = @file_get_contents($url, false, $ctx);
    return ($r !== false) ? json_decode($r, true) : null;
}

/**
 * @brief Interroge l'API carburants avec pagination automatique
 *
 * Récupère toutes les stations correspondant à la clause WHERE,
 * en gérant la pagination (100 résultats par page, max 500).
 *
 * @param  string $where Clause WHERE encodée pour l'URL de l'API
 * @return array|null    Liste de stations ou null en cas d'erreur réseau
 */
function appeler_api_carburants(string $where): ?array {
    $base   = "https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets"
            . "/prix-des-carburants-en-france-flux-instantane-v2/records";
    $limit  = 100;
    $offset = 0;
    $tout   = [];

    do {
        $url  = "{$base}?where={$where}&limit={$limit}&offset={$offset}&timezone=Europe%2FParis";
        $data = fetch_json($url);
        if ($data === null) return null;
        $batch   = $data["results"] ?? [];
        $tout    = array_merge($tout, $batch);
        $total   = (int)($data["total_count"] ?? count($batch));
        $offset += $limit;
    } while (count($batch) === $limit && $offset < $total && $offset < 500);

    return $tout;
}

// ══════════════════════════════════════════════════════════════════════════════
// GÉOLOCALISATION IP
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Récupère la géolocalisation approximative depuis l'adresse IP du visiteur
 *
 * Utilise l'API ipinfo.io (format JSON).
 * Remplace les IP locales (127.0.0.1, ::1) par une IP de test.
 *
 * @return array Tableau associatif [city, region, country, postal, loc] ou tableau vide
 */

/*Dans get_geolocalisation() dans functions.inc.php, au lieu de fetch_json() qui utilise json_decode()
en interne, on aurait appelé directement l'API ip2location.io au format XML. file_get_contents()
reste identique — seul le décodage change :
// JSON (actuel)
$geo = fetch_json("https://ipinfo.io/{$ip}/geo");
$ville = $geo["city"]; // tableau
// XML (alternative)
$rep = file_get_contents("https://api.ip2location.io/?ip={$ip}&format;=xml");
$xml = simplexml_load_string($rep);
$ville = (string)$xml->city_name; // objet avec fleche
*/
function get_geolocalisation(): array {
    $ip = !empty($_SERVER["HTTP_X_FORWARDED_FOR"])
        ? trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0])
        : ($_SERVER["REMOTE_ADDR"] ?? "");
    if ($ip === "127.0.0.1" || $ip === "::1") $ip = "193.54.115.192";

    // Tentative 1 : ipinfo.io
    $geo = fetch_json("https://ipinfo.io/{$ip}/geo", 5);
    if (is_array($geo) && !empty($geo["city"])) return $geo;

    // Tentative 2 : ip-api.com (fallback)
    $geo2 = fetch_json("http://ip-api.com/json/{$ip}?fields=city,regionName,country,zip,lat,lon&lang=fr", 5);
    if (is_array($geo2) && !empty($geo2["city"])) {
        return [
            "city"    => $geo2["city"]       ?? "",
            "region"  => $geo2["regionName"] ?? "",
            "country" => $geo2["country"]    ?? "",
            "postal"  => $geo2["zip"]        ?? "",
            "loc"     => ($geo2["lat"] ?? "") . "," . ($geo2["lon"] ?? ""),
            "ip"      => $ip
        ];
    }

    return [];
}

// ══════════════════════════════════════════════════════════════════════════════
// CSV COMMUNES / CODES POSTAUX
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Supprime les accents d'une chaîne (pour les requêtes API)
 *
 * L'API carburants ne gère pas toujours les caractères accentués.
 *
 * @param  string $s Chaîne avec accents
 * @return string    Chaîne sans accents
 */
function sans_accents(string $s): string {
    $map = [
        'à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','À'=>'A','Â'=>'A','Ä'=>'A','É'=>'E','È'=>'E','Ê'=>'E',
        'Ë'=>'E','Î'=>'I','Ï'=>'I','Ô'=>'O','Ö'=>'O','Ù'=>'U','Û'=>'U',
        'Ü'=>'U','Ç'=>'C'
    ];
    return strtr($s, $map);
}

/**
 * @brief Normalise le nom d'une commune (regroupe les arrondissements)
 *
 * Transforme "PARIS 01".."PARIS 20" en "PARIS",
 * "LYON 01".."LYON 09" en "LYON", "MARSEILLE 01".."MARSEILLE 16" en "MARSEILLE".
 *
 * @param  string $nom Nom brut issu du CSV
 * @return string      Nom normalisé
 */
function normaliser_nom_ville(string $nom): string {
    foreach (["PARIS", "LYON", "MARSEILLE"] as $v) {
        if (preg_match('/^' . preg_quote($v, '/') . '\s+\d+$/i', $nom)) return $v;
    }
    return $nom;
}

/**
 * @brief Retourne tous les codes postaux d'une commune (par nom normalisé)
 *
 * Lit le fichier clean_postcodes.csv et retourne les codes postaux
 * associés au nom fourni. Gère la fusion des arrondissements.
 * La recherche par CP dans l'API est plus robuste que par nom de ville.
 *
 * @param  string $nomVille Nom normalisé de la commune (ex: "BORDEAUX", "PARIS")
 * @return string[]         Liste des codes postaux (ex: ["33000","33100",...])
 */
function get_codes_postaux(string $nomVille): array {
    $csvPath   = __DIR__ . "/../clean_postcodes.csv";
    if (!file_exists($csvPath)) return [];
    $nomUpper  = strtoupper(trim($nomVille));
    $estArrond = in_array($nomUpper, ["PARIS", "LYON", "MARSEILLE"]);
    $cps       = [];
    $f = fopen($csvPath, "r");
    if ($f === false) return [];
    fgetcsv($f, 0, ",", "\"", "\\"); // ignorer l'en-tête
    while (($row = fgetcsv($f, 0, ",", "\"", "\\")) !== false) {
        if (count($row) < 3) continue;
        $nomCsv = strtoupper(trim($row[1], '"'));
        $cp     = trim($row[2], '"');
        if ($estArrond) {
            if ($nomCsv === $nomUpper ||
                preg_match('/^' . preg_quote($nomUpper, '/') . '\s+\d+$/', $nomCsv)) {
                $cps[] = $cp;
            }
        } elseif ($nomCsv === $nomUpper) {
            $cps[] = $cp;
        }
    }
    fclose($f);
    return array_unique($cps);
}

/**
 * @brief Charge la liste des villes d'un département depuis le CSV
 *
 * @param  string $deptCode Code du département (ex: "75", "2A")
 * @return array             Tableau [nomAffiche => nomAffiche] trié alphabétiquement
 */
function get_villes_dept(string $deptCode): array {
    $csvPath = __DIR__ . "/../clean_postcodes.csv";
    if (!file_exists($csvPath)) return [];
    $villes  = [];
    $f = fopen($csvPath, "r");
    fgetcsv($f, 0, ",", "\"", "\\");
    while (($row = fgetcsv($f, 0, ",", "\"", "\\")) !== false) {
        if (count($row) < 3) continue;
        $insee   = trim($row[0], '"');
        $nomCsv  = trim($row[1], '"');
        $prefix2 = substr($insee, 0, 2);
        $dept    = in_array($prefix2, ["2A","2B"]) ? $prefix2 : $prefix2;
        if ($dept !== $deptCode) continue;
        $nom = normaliser_nom_ville($nomCsv);
        if (!isset($villes[$nom])) $villes[$nom] = $nom;
    }
    fclose($f);
    ksort($villes);
    return $villes;
}

// ══════════════════════════════════════════════════════════════════════════════
// FORMATAGE PRIX CARBURANTS
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Formate un prix carburant en euros/litre pour l'affichage HTML
 *
 * @param  mixed $prix Valeur numérique du prix ou null/0
 * @return string      HTML formaté (span.prix-val) ou "—" si absent
 */
function fmt_prix($prix): string {
    if ($prix === null || $prix === "" || (float)$prix <= 0)
        return "<span class='prix-nd'>—</span>";
    return "<span class='prix-val'>" . number_format((float)$prix, 3, ",", " ") . " €</span>";
}

/**
 * @brief Retourne la valeur float d'un prix pour les attributs data-*
 *
 * @param  mixed $prix Valeur numérique ou null
 * @return float        0.0 si absent, valeur float sinon
 */
function val_prix($prix): float {
    return ($prix === null || (float)$prix <= 0) ? 0.0 : (float)$prix;
}

// ══════════════════════════════════════════════════════════════════════════════
// DONNÉES RÉGIONS / DÉPARTEMENTS
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Retourne le tableau complet des régions et leurs départements
 *
 * @return array<string, array<string, string>> [nomRégion => [codeDept => nomDept]]
 */
function get_regions(): array {
    return [
        "Île-de-France"              => ["75"=>"Paris","77"=>"Seine-et-Marne","78"=>"Yvelines","91"=>"Essonne","92"=>"Hauts-de-Seine","93"=>"Seine-Saint-Denis","94"=>"Val-de-Marne","95"=>"Val-d'Oise"],
        "Auvergne-Rhône-Alpes"       => ["01"=>"Ain","03"=>"Allier","07"=>"Ardèche","15"=>"Cantal","26"=>"Drôme","38"=>"Isère","42"=>"Loire","43"=>"Haute-Loire","63"=>"Puy-de-Dôme","69"=>"Rhône","73"=>"Savoie","74"=>"Haute-Savoie"],
        "Hauts-de-France"            => ["02"=>"Aisne","59"=>"Nord","60"=>"Oise","62"=>"Pas-de-Calais","80"=>"Somme"],
        "Nouvelle-Aquitaine"         => ["16"=>"Charente","17"=>"Charente-Maritime","19"=>"Corrèze","23"=>"Creuse","24"=>"Dordogne","33"=>"Gironde","40"=>"Landes","47"=>"Lot-et-Garonne","64"=>"Pyrénées-Atlantiques","79"=>"Deux-Sèvres","86"=>"Vienne","87"=>"Haute-Vienne"],
        "Occitanie"                  => ["09"=>"Ariège","11"=>"Aude","12"=>"Aveyron","30"=>"Gard","31"=>"Haute-Garonne","32"=>"Gers","34"=>"Hérault","46"=>"Lot","48"=>"Lozère","65"=>"Hautes-Pyrénées","66"=>"Pyrénées-Orientales","81"=>"Tarn","82"=>"Tarn-et-Garonne"],
        "Grand Est"                  => ["08"=>"Ardennes","10"=>"Aube","51"=>"Marne","52"=>"Haute-Marne","54"=>"Meurthe-et-Moselle","55"=>"Meuse","57"=>"Moselle","67"=>"Bas-Rhin","68"=>"Haut-Rhin","88"=>"Vosges"],
        "Normandie"                  => ["14"=>"Calvados","27"=>"Eure","50"=>"Manche","61"=>"Orne","76"=>"Seine-Maritime"],
        "Pays de la Loire"           => ["44"=>"Loire-Atlantique","49"=>"Maine-et-Loire","53"=>"Mayenne","72"=>"Sarthe","85"=>"Vendée"],
        "Bretagne"                   => ["22"=>"Côtes-d'Armor","29"=>"Finistère","35"=>"Ille-et-Vilaine","56"=>"Morbihan"],
        "Bourgogne-Franche-Comté"    => ["21"=>"Côte-d'Or","25"=>"Doubs","39"=>"Jura","58"=>"Nièvre","70"=>"Haute-Saône","71"=>"Saône-et-Loire","89"=>"Yonne","90"=>"Territoire de Belfort"],
        "Centre-Val de Loire"        => ["18"=>"Cher","28"=>"Eure-et-Loir","36"=>"Indre","37"=>"Indre-et-Loire","41"=>"Loir-et-Cher","45"=>"Loiret"],
        "Provence-Alpes-Côte d'Azur" => ["04"=>"Alpes-de-Haute-Provence","05"=>"Hautes-Alpes","06"=>"Alpes-Maritimes","13"=>"Bouches-du-Rhône","83"=>"Var","84"=>"Vaucluse"],
        "Corse"                      => ["2A"=>"Corse-du-Sud","2B"=>"Haute-Corse"],
    ];
}

// ══════════════════════════════════════════════════════════════════════════════
// SERVICES DES STATIONS
// ══════════════════════════════════════════════════════════════════════════════

/**
 * @brief Retourne la liste des services disponibles dans une station
 *
 * Décode le champ "services_service" de l'API (chaîne JSON ou tableau)
 * et retourne un tableau de noms de services.
 *
 * @param  mixed $raw Valeur brute du champ services_service (string JSON ou array ou null)
 * @return string[]   Liste des services (ex: ["Automate CB 24/24", "Lavage automatique"])
 */
function get_services_station($raw): array {
    if ($raw === null || $raw === '' || $raw === 'null') return [];
    if (is_array($raw)) return array_values(array_filter($raw));
    if (is_string($raw)) {
        // Peut être un JSON array ou une chaîne simple
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) return array_values(array_filter($decoded));
        // Parfois séparé par des virgules
        return array_filter(array_map('trim', explode(',', $raw)));
    }
    return [];
}

/**
 * @brief Retourne l'icône SVG inline associée à un service de station
 *
 * @param  string $service Nom du service (ex: "Automate CB 24/24")
 * @return string          Balise SVG ou chaîne vide si service inconnu
 */
function icone_service(string $service): string {
    $map = [
        'Automate CB 24/24'          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
        'Lavage automatique'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        'Lavage manuel'              => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        'Gonflage pneus'             => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>',
        'Vente de fioul domestique'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><polyline points="9 11 12 14 22 4"/></svg>',
        'Boutique alimentaire'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'Boutique non alimentaire'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>',
        'Restauration sur place'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
        'Vente de pétrole lampant'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
        'DAB'                        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
        'Wifi'                       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
        'Toilettes publiques'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    ];
    return $map[$service] ?? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
}
