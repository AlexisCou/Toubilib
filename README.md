# Toubilib

Projet réalisé dans le cadre du cours de Développement Web Serveur avancé (BUT Informatique, S5 DWM, IUT Charlemagne Nancy). C'est une API de prise de rendez-vous médicaux, en PHP avec le framework Slim, construite en architecture hexagonale avec injection de dépendances via PHP-DI.

Ce README regroupe tout ce qui a été fait sur les quatre TD (1.1 à 1.4), à la place des petits fichiers séparés dans `docs/` que j'avais faits au début. L'idée ici c'est d'expliquer pourquoi le code est organisé comme ça, pas juste de lister ce qui existe.

## Sommaire

- [Lancer le projet](#lancer-le-projet)
- [TD1.1 : architecture hexagonale](#td11--architecture-hexagonale)
- [TD1.2 : injection de dépendances explicite](#td12--injection-de-dépendances-explicite)
- [TD1.3 : règles métier et persistance](#td13--règles-métier-et-persistance)
- [TD1.4 : API RESTful, HATEOAS, CORS](#td14--api-restful-hateoas-cors)
- [Tests](#tests)
- [Ce que je ferais différemment avec plus de temps](#ce-que-je-ferais-différemment-avec-plus-de-temps)

## Lancer le projet

```
docker-compose up -d
```

Ça lance trois conteneurs : `api.toubilib` (le serveur PHP, exposé sur le port 7080), `toubilib.db` (Postgres, port 5532) et `adminer` (interface web pour voir la base, port 8880).

Point important sur la base de données : le `docker-compose.yml` monte le dossier `sql/` à la racine du projet dans le conteneur Postgres, pas `app/sql/`. Au départ `sql/` était vide donc les tables n'étaient jamais créées au démarrage. J'ai copié `toubilib.schema.sql` et `toubilib.data.sql` de `app/sql/` vers `sql/` pour que ça fonctionne. Si jamais la base est vide après un `docker-compose down -v`, il faut réexécuter ces deux scripts à la main avec `psql` (Postgres ne les exécute pas tout seul après le premier démarrage, il faut le faire manuellement ici, pas comme avec l'image officielle qui lirait automatiquement un dossier `/docker-entrypoint-initdb.d`).

Pour lancer les tests :

```
docker-compose exec api.toubilib vendor/bin/pest
```

## TD1.1 : architecture hexagonale

Le but du TD1.1 c'était de découper le code en couches qui ne dépendent pas les unes des autres n'importe comment. L'idée de l'architecture hexagonale (ports et adaptateurs), c'est que le cœur du métier (le domaine) ne doit jamais dépendre de détails techniques comme Slim, PDO ou Postgres. C'est l'inverse qui doit être vrai : les détails techniques dépendent du domaine.

Concrètement, dans `app/src/` il y a :

- `domain/` : les entités métier pures (`RendezVous`, `Praticien`, `Patient`, `MotifVisite`, `StatutRendezVous`) et leurs exceptions (`CreneauIndisponibleException`, `HoraireNonDisponibleException`, etc). Aucune de ces classes ne connaît PDO, Slim ou même l'existence d'une base de données. Une entité comme `RendezVous` sait juste comment un rendez-vous se comporte (on peut l'annuler, le marquer honoré, vérifier s'il chevauche un autre créneau).
- `application/ports/` : les interfaces qui définissent les contrats. Il y a deux types de ports.
  - `ports/api/` : les ports d'entrée, c'est-à-dire ce que le monde extérieur peut demander à l'application (`ServiceRendezVousInterface`, `PraticienServiceInterface`). Ce sont les interfaces que les actions HTTP utilisent.
  - `ports/spi/` : les ports de sortie, c'est-à-dire ce que l'application a besoin du monde extérieur (`RendezVousRepositoryInterface`, `PraticienRepositoryInterface`, `PatientRepositoryInterface`). Ce sont les interfaces que la couche métier utilise pour aller chercher ou sauvegarder des données, sans savoir comment c'est fait réellement.
  - `application/usecases/` : l'implémentation concrète des ports d'entrée (`ServiceRendezVous`, `PraticienService`). C'est la couche qui orchestre : elle appelle le domaine, elle appelle les repositories via leurs interfaces.
- `adapters/` : les implémentations concrètes des ports, du côté technique.
  - `adapters/http/actions/` : les adaptateurs d'entrée, qui traduisent une requête HTTP Slim en appel à un service, puis traduisent la réponse du service en réponse HTTP JSON.
  - `adapters/persistence/` : les adaptateurs de sortie, qui implémentent les interfaces repository avec du vrai PDO et des vraies requêtes SQL.

Pourquoi c'est intéressant : si demain on change Slim pour un autre framework, ou Postgres pour autre chose, le domaine et les cas d'usage ne changent pas du tout. Et pour les tests, ça permet de remplacer un `RendezVousRepository` (qui a besoin d'une vraie base) par un `RendezVousRepositoryFake` (juste un tableau en mémoire) sans toucher au code métier, puisque le service ne connaît que l'interface, pas l'implémentation.

## TD1.2 : injection de dépendances explicite

Le conteneur est construit dans `app/config/bootstrap.php` avec PHP-DI, et surtout avec `useAutowiring(false)`. Ça veut dire qu'on désactive la résolution automatique des dépendances par réflexion : chaque classe doit dire explicitement de quoi elle a besoin, dans les fichiers `app/config/services.php` et `app/config/api.php`.

Exemple dans `api.php` :

```php
ConsulterRendezVousAction::class => \DI\create(ConsulterRendezVousAction::class)
    ->constructor(\DI\get(ServiceRendezVousInterface::class)),
```

Au début j'avais utilisé `\DI\autowire()` en pensant que ça suffisait puisque je le déclarais explicitement dans le fichier de config. Mais ça plantait quand même avec une erreur disant que l'autowiring est désactivé. En creusant, le problème c'est que `autowire()` passe toujours par le mécanisme de réflexion de PHP-DI en interne pour déterminer les paramètres du constructeur, même si on l'appelle à la main dans une définition explicite. Avec `useAutowiring(false)`, ce mécanisme est entièrement coupé, donc `autowire()` ne marche plus du tout, peu importe où on l'utilise. La solution c'est `\DI\create()->constructor(...)`, qui ne fait aucune réflexion : on donne la liste exacte des arguments du constructeur, dans l'ordre, avec `\DI\get()` pour chaque dépendance qu'on veut que le conteneur résolve.

Pourquoi désactiver l'autowiring alors que ça complique un peu l'écriture des fichiers de config ? C'est voulu dans le sujet du TD, pour nous forcer à être explicites sur toutes les dépendances d'une classe plutôt que de laisser PHP-DI deviner par réflexion. Ça rend aussi les erreurs de configuration plus claires (on voit directement dans le fichier `api.php` ou `services.php` ce qui est branché à quoi) et ça évite les surprises de performance liées à la réflexion à l'exécution.

Un autre détail qui m'a pris du temps : Slim est lancé via `DI\Bridge\Slim\Bridge::create($container)`, et pas juste `AppFactory::create()`. Ça fait que Slim lui-même utilise le conteneur pour résoudre ses propres classes internes, y compris les classes de rendu d'erreur (`PlainTextErrorRenderer`, `JsonErrorRenderer`, etc). Du coup il a fallu aussi les déclarer dans `services.php` avec `\DI\create()`, sinon le serveur plantait au moment de générer une page d'erreur, ce qui est un bug particulièrement pénible à déboguer puisqu'il n'apparaît que quand une erreur HTTP se produit.

Dernier piège de cette partie : le bridge `php-di/slim-bridge` n'injecte pas les paramètres de route comme Slim le fait nativement. En Slim classique, une action reçoit `(Request $request, Response $response, array $args)` et on va chercher l'id avec `$args['id']`. Avec le bridge PHP-DI, l'invoker regarde le nom de chaque paramètre de la méthode `__invoke` et injecte directement la valeur correspondante depuis la route. Donc il faut écrire `__invoke(Request $request, Response $response, string $id)` et utiliser directement `$id`, sinon on obtient une erreur du genre "aucune valeur donnée pour le paramètre $args".

## TD1.3 : règles métier et persistance

Cette partie, c'est surtout le contenu du domaine et des cas d'usage qui a été complété et testé.

Les règles métier principales portées par l'entité `RendezVous` :

- Un rendez-vous a un statut (`CREE`, `ANNULE`, `HONORE`, `IGNORE`) et une durée qui dépend du motif de visite (`MotifVisite`, chaque motif a sa propre durée en minutes).
- `annuler()` refuse d'annuler un rendez-vous déjà passé (`RendezVousDejaPasseException`) ou déjà annulé (`RendezVousDejaAnnuleException`).
- `chevauche()` vérifie si deux créneaux se recoupent dans le temps, utilisé pour détecter les conflits d'agenda.
- `validerEtCreer()` est la méthode qui fait tourner toutes les vérifications avant de créer un rendez-vous : est-ce que l'horaire est ouvrable pour ce praticien (pas le week-end, dans les heures d'ouverture), est-ce que le créneau n'est pas déjà pris par un autre rendez-vous de ce praticien. Si une de ces règles est violée, ça lève `HoraireNonDisponibleException` ou `CreneauIndisponibleException`.

Côté persistance, `RendezVousRepository`, `PraticienRepository` et `PatientRepository` utilisent PDO directement avec des requêtes SQL préparées (pas d'ORM). Chaque repository implémente l'interface SPI correspondante et sait reconstituer une entité domaine à partir d'une ligne de résultat SQL.

Pour les tests, les fakes dans `app/tests/pest/fakes/` (`RendezVousRepositoryFake`, `PraticienRepositoryFake`, `PatientRepositoryFake`) sont juste des tableaux PHP en mémoire qui implémentent les mêmes interfaces. Ça permet de tester les services (`ServiceRendezVous`, `PraticienService`) sans avoir besoin d'une vraie base Postgres qui tourne, et sans dépendre de données qui pourraient changer dans la base. C'est exactement l'intérêt d'avoir séparé les ports des adaptateurs au TD1.1 : le code de test ne change rien à `ServiceRendezVous`, il remplace juste l'implémentation injectée dans `services.php` au niveau des tests (via `Pest.php` ou directement dans chaque fichier de test).

## TD1.4 : API RESTful, HATEOAS, CORS

### Partie théorique : critique d'une API mal conçue

Le TD donnait une API v1 avec des routes comme `GET /list-commandes`, `POST /commandes/add`, `PUT /commande/set-paiement/{id}`. Le problème commun à toutes : il y a un verbe dans l'URL (`list`, `add`, `set-paiement`), alors qu'une URL REST ne doit désigner que des ressources. L'action est déjà portée par le verbe HTTP (`GET` pour lire, `POST` pour créer, `PUT`/`PATCH` pour modifier), donc répéter l'action dans l'URL est redondant et surtout ça casse le principe d'uniformité de REST. Il y avait aussi des soucis de codes de statut, par exemple retourner `404` pour une erreur de connexion à la base de données, alors que `404` veut dire précisément "la ressource n'existe pas" et pas "le serveur a un problème". Une erreur de ce type devrait être un `500`.

La bonne version regroupe les ressources autour des noms du domaine (`commandes`, `clients`, `items`, `paiement`), avec les sous-ressources en imbrication d'URL quand il y a une relation (`/commandes/{id}/items`, `/commandes/{id}/paiement`), et les bons codes de statut selon le cas (`201` à la création avec un header `Location`, `422` pour des données invalides, `409` pour un conflit comme une commande déjà payée).

### Application à Toubilib

Les routes RDV ont été renommées de `/rdv` vers `/rdvs` (au pluriel, pour être cohérent avec `/praticiens` qui était déjà pluriel), et `/rdv/{id}/annulation` est devenu `/rdvs/{id}/annuler`. Les routes actuelles :

| Méthode | Route | Rôle |
|---|---|---|
| POST | `/rdvs` | créer un rendez-vous |
| GET | `/rdvs/{id}` | consulter un rendez-vous |
| PATCH | `/rdvs/{id}/annuler` | annuler un rendez-vous |
| PATCH | `/rdvs/{id}/honorer` | marquer un rendez-vous honoré |
| PATCH | `/rdvs/{id}/ignorer` | marquer un rendez-vous ignoré |
| GET | `/praticiens` | lister les praticiens |
| GET | `/praticiens/{id}` | consulter un praticien |
| GET | `/praticiens/{id}/rdvs` | lister les créneaux occupés d'un praticien (tout statut sauf annulé) |
| GET | `/praticiens/{id}/agenda?du=...&au=...` | agenda complet d'un praticien sur une période (tous statuts) |

Les routes `annuler`, `honorer`, `ignorer` gardent un verbe dans l'URL, ce qui va a priori à l'encontre du principe vu plus haut. C'est volontaire : ce sont des transitions d'état qui ne se résument pas à "modifier un champ", parce que c'est le serveur qui décide si la transition est valide (on ne peut pas honorer un rendez-vous déjà annulé par exemple), pas le client qui envoie une nouvelle valeur de statut. C'est une exception assez classique en REST pour ce genre d'opération, et c'est aussi la convention utilisée dans l'exemple donné en annexe du sujet, donc je m'y suis tenu. J'ai choisi `PATCH` plutôt que `POST` parce qu'on modifie partiellement une ressource qui existe déjà (son statut), ce qui correspond au sens de `PATCH`, alors que `POST` sert normalement à créer quelque chose de nouveau.

Pour `honorer` et `ignorer`, il manquait les méthodes correspondantes côté domaine. Je les ai ajoutées sur `RendezVous` (`marquerHonore()`, `marquerIgnore()`), avec une garde : elles lèvent `RendezVousStatutInvalideException` si le rendez-vous n'est pas à l'état `CREE` (on ne peut pas honorer un rendez-vous déjà annulé ou déjà honoré). Ensuite ça remonte dans `ServiceRendezVousInterface`/`ServiceRendezVous` (méthodes `honorer()`/`ignorer()`) et dans deux nouvelles actions HTTP, `HonererRendezVousAction` et `IgnererRendezVousAction`, qui renvoient `200` en cas de succès, `404` si le rendez-vous n'existe pas, `409` si la transition est invalide (conflit avec l'état actuel de la ressource).

Pour `/praticiens/{id}/rdvs` et `/praticiens/{id}/agenda`, il a fallu ajouter deux méthodes sur `RendezVousRepositoryInterface` : `findCreneauxOccupesParPraticien()` (tous les rendez-vous d'un praticien sauf les annulés, pour savoir quels créneaux sont réellement pris) et `findParPraticienEtPeriode()` (tous les rendez-vous, tous statuts, sur une plage de dates, pour l'agenda complet). La différence entre les deux, c'est que l'agenda sert à visualiser toute l'activité du praticien sur une période (même les rendez-vous annulés, pour avoir l'historique complet), alors que les créneaux occupés servent à savoir quels horaires sont réellement indisponibles pour un nouveau rendez-vous.

### HATEOAS

Le principe HATEOAS, c'est que les réponses de l'API ne contiennent pas juste les données, mais aussi les liens vers les actions possibles sur la ressource, pour que le client de l'API n'ait pas besoin de deviner ou de coder en dur les URLs des actions disponibles. J'ai mis ça dans une classe dédiée, `app/src/adapters/http/HateoasLinks.php`, avec deux méthodes statiques (`pourRendezVous()` et `pourPraticien()`) qui prennent un DTO et renvoient un tableau de liens. Toutes les actions qui renvoient un rendez-vous ou un praticien ajoutent maintenant une clé `links` dans la réponse JSON.

Le point important : les liens `annuler`, `honorer`, `ignorer` ne sont ajoutés que si le rendez-vous est encore à l'état `CREE`. Un rendez-vous déjà annulé n'affiche plus ces trois liens dans sa réponse. C'est exactement l'esprit de HATEOAS : l'état courant de la ressource détermine quelles actions sont réellement proposées, plutôt que de toujours renvoyer les mêmes liens même quand l'action n'a plus de sens.

### CORS

Pour qu'un futur client web (une application React ou autre, sur un domaine différent) puisse appeler l'API depuis un navigateur, j'ai ajouté un middleware CORS global dans `bootstrap.php`, qui ajoute les en-têtes `Access-Control-Allow-Origin`, `Access-Control-Allow-Headers` et `Access-Control-Allow-Methods` à toutes les réponses. Ce middleware est ajouté après `addErrorMiddleware()`, donc il est le plus extérieur de la pile et il enveloppe aussi les réponses d'erreur, pas seulement les succès (sinon une erreur 404 ou 500 n'aurait pas les en-têtes CORS et le navigateur bloquerait la réponse côté client avant même que le code JS ne puisse lire le message d'erreur).

Il y a aussi une route `OPTIONS` générique (`/{routes:.+}`) qui répond `204` sans corps, pour gérer les requêtes de préflight que le navigateur envoie automatiquement avant certaines requêtes (notamment tout ce qui n'est pas un simple `GET`, comme nos `PATCH`, ou les requêtes avec `Content-Type: application/json`).

## Tests

Les tests sont écrits avec Pest, dans `app/tests/pest/`, organisés en trois dossiers qui suivent plus ou moins les couches de l'architecture :

- `domaine/` : teste les entités directement (`RendezVousTest.php`, `PraticienTest.php`), sans aucune dépendance externe. Par exemple, vérifier qu'un rendez-vous annulé ne peut pas être annulé une deuxième fois, ou qu'un rendez-vous ne peut être marqué honoré que s'il est encore à l'état créé.
- `services/` : teste les cas d'usage (`ServiceRendezVousTest.php`, `PraticienServiceTest.php`) en utilisant les fakes à la place des vrais repositories PDO, pour tester la logique d'orchestration sans base de données.
- `dto/` : teste la transformation des données en DTO (`CreateRdvDTOTest.php`).
- `fakes/` : pas des tests en soi, mais les implémentations en mémoire des interfaces repository, utilisées par les tests de services.

Pour lancer tous les tests : `docker-compose exec api.toubilib vendor/bin/pest`.

## Ce que je ferais différemment avec plus de temps

Quelques points que je note pour moi, pas forcément des choses à corriger dans l'urgence :

- Il n'y a pas encore de validation centralisée des paramètres de requête (`du`/`au` sur l'agenda sont parsés directement dans l'action), ça pourrait être extrait dans un validator comme `CreateRdvValidator` l'est déjà pour la création de rendez-vous.
- Le `Access-Control-Allow-Origin: *` est très permissif, en vrai projet il faudrait le restreindre à l'origine du client front une fois connue.
- Il reste quelques fichiers de test JSON (`rdv_test*.json`) à la racine du projet, utilisés pour tester les routes à la main avec `curl`, qui pourraient être rangés dans un dossier `bruno/` ou supprimés une fois que la collection Bruno est à jour avec les nouvelles routes.
