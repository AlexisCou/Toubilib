# TD1.3 - Partie 1 : Analyse sur papier

*(Exercice 1 - JIRA : non traité, pas d'accès au dépôt.)*

## Exercice 2 - Création d'un RDV (fonctionnalité n°5)

### Données à fournir par l'application cliente

| Donnée | Type | Obligatoire |
|---|---|---|
| `praticien_id` | string | oui |
| `patient_id` | string | oui |
| `date_heure_debut` | string (ISO 8601) | oui |
| `motif_visite` | string (code parmi `CI`, `C0`, `CS`) | oui |

La durée et la date de fin ne sont pas fournies par le client : elles sont calculées par le domaine à partir du motif (`MotifVisite::dureeEnMinutes()`).

### Vérifications métier à réaliser avant de créer le RDV

1. le praticien indiqué existe,
2. le patient indiqué existe,
3. le motif transmis est correct pour le praticien,
4. la date et l'heure demandées sont acceptables pour le praticien (jour ouvrable, plage horaire),
5. le praticien est disponible à la date/l'heure/durée demandées (pas de chevauchement avec un RDV existant).

### Composants réalisant ces validations

- **`CreateRdvValidator`** (application/validators) : vérifie l'existence du praticien et du patient, via `PraticienRepositoryInterface` et `PatientRepositoryInterface`.
- **Entité `Praticien`** : vérifie que le motif est accepté (`verifierMotifAccepte`), que l'horaire est ouvrable (`verifierHoraireAcceptable`), et la disponibilité sur son agenda (`verifierDisponibilite`).
- **Entité `RendezVous`** (méthode statique `validerEtCreer`) : orchestre les vérifications du praticien puis crée le RDV.
- **`CreateRdvDTO::depuisRequete()`** : validation syntaxique en amont (présence, type, format) — distincte des validations métier ci-dessus.

### Composant qui crée et persiste l'entité

`ServiceRendezVous::creer()` charge le praticien, le patient et l'agenda du praticien pour le jour concerné (`RendezVousRepositoryInterface::findParPraticienEtJour()`), délègue la création à `RendezVous::validerEtCreer()`, puis persiste via `RendezVousRepositoryInterface::save()` (déjà existant, réutilisé).

### DTO et profil de la méthode du port d'entrée

**`CreateRdvDTO`** (entrée) : `praticienId`, `patientId`, `dateHeureDebut` (`DateTimeImmutable`), `motifVisite` (string).

**`RendezVousDTO`** (sortie) : déjà existant, réutilisé tel quel.

```php
interface ServiceRendezVousInterface
{
    /**
     * @throws PraticienIntrouvableException le praticien indiqué n'existe pas
     * @throws PatientIntrouvableException le patient indiqué n'existe pas
     * @throws HoraireNonDisponibleException le jour/l'heure demandés ne sont pas ouvrables
     * @throws CreneauIndisponibleException le praticien a déjà un RDV sur ce créneau
     */
    public function creer(CreateRdvDTO $dto): RendezVousDTO;
}
```

## Partie 2 - Code

Réalisée directement dans le projet (exercices 1 à 4 traités en une seule implémentation finale, incluant les étapes intermédiaires demandées par le sujet) :

- **Domaine** : `Patient` (nouvelle entité), `Praticien` étendu (agenda, `verifierMotifAccepte`, `dureeRdv`, `verifierHoraireAcceptable`, `verifierDisponibilite`), `RendezVous` étendu (`chevauche`, `validerEtCreer`), exceptions `HoraireNonDisponibleException` et `CreneauIndisponibleException`.
- **Application** : `CreateRdvDTO` (validation syntaxique), `CreateRdvValidator` (existence praticien/patient), `PatientRepositoryInterface`, `PatientIntrouvableException`, `DonneesRdvInvalidesException`, `ServiceRendezVous::creer()`, `RendezVousRepositoryInterface::findParPraticienEtJour()`.
- **Adaptateurs** : `PatientRepository` (PDO), `RendezVousRepository` complété, `CreerRendezVousAction` (POST `/rdv`, 201/404/409/422 selon le cas).
- **DI** : `config/services.php` et `config/api.php` mis à jour.
- **Tests Pest** : entités (`PraticienTest`, `RendezVousTest`), DTO (`CreateRdvDTOTest`), service (`ServiceRendezVousTest`).
