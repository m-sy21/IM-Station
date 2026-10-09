# i'm Station — Prix des carburants en France

## Auteurs
- **Ines LAIFAOUI** — L2 Informatique, CY Cergy Paris Université
- **Meriem SIMYA** — L2 Informatique, CY Cergy Paris Université

## Description
Site web de consultation des prix des carburants en France métropolitaine.
Permet de sélectionner une région, un département et une ville pour afficher
les stations-service et leurs prix en temps réel.

## URL du site
- https://laifaoui.alwaysdata.net/
- https://simya.alwaysdata.net/

## Technologies
- HTML5 / CSS3 / PHP 8
- API REST JSON : data.economie.gouv.fr, ipinfo.io, ghibliapi.vercel.app
- Données CSV locales (codes postaux, communes)

## Pages
| Fichier         | Description                              |
|-----------------|------------------------------------------|
| bienvenue.php   | Page de présentation                     |
| index.php       | Carte interactive + sélection ville      |
| resultats.php   | Prix des carburants par ville            |
| stats.php       | Statistiques d'utilisation               |
| tech.php        | Page technique (API Ghibli + géoloc IP)  |
| plan.php        | Plan du site                             |

## Documentation
La documentation du code est disponible dans `doc/index.html`
(générée avec Doxygen depuis les commentaires PHPDoc).

## Structure des données
- `clean_postcodes.csv` : base codes postaux / communes INSEE
- `consultations.csv` : horodatage des consultations (créé automatiquement)
