# Diagramme complet – Flux JoinFiles

Ce document décrit **de manière exhaustive et correcte** :

* les **entrées**
* les **transformations successives**
* les **fichiers intermédiaires**
* les **sorties finales**
* le **cas spécifique des personnes désactivées**

Il correspond **exactement** à la version réelle de `JoinFiles.sh` que tu as montrée (script orchestrateur monolithique).

---

## 1. Vue macro – pipeline global

```
┌──────────────────────────┐
│   FICHIERS CHIFFRÉS      │
│  (.csv.gpg / .zip.gpg)  │
└────────────┬─────────────┘
             │
             ▼
┌──────────────────────────┐
│       JoinFiles.sh       │
│  (orchestrateur maître) │
└────────────┬─────────────┘
             │
     uncypher_all_files
             │
             ▼
┌──────────────────────────┐
│     CSV SOURCES CLAIRS   │
│ Personne / UO / JAD /…  │
└────────────┬─────────────┘
             │
   unzip ELOQUA (EDGAHR/JAD)
             │
             ▼
      ┌───────────────┐
      │               │
      ▼               ▼
processEdjad     processRefog
      │               │
      ▼               ▼
Import_EdJad.csv  ImportRefog.csv (intermédiaire)
                  ImportRefogDesact.csv (delta)
                          │
                          ▼
                 joinEdjadToRefog
                          │
                          ▼
                ImportRefog.csv (FINAL)
                          │
                          ▼
                     cleanupFiles
```

---

## 2. Détail par étape

---

## 2.1 Déchiffrement & préparation (JoinFiles.sh)

### Entrées

* Immeuble.csv.gpg
* Personne.csv.gpg
* PersonneDesact.csv.gpg
* UO.csv.gpg
* UOHierarchique.csv.gpg
* TypeFiliere.csv.gpg
* Filiere.csv.gpg
* SousFiliere.csv.gpg
* ELOQUA.zip.gpg

### Traitements

* Déchiffrement GPG → CSV clairs
* Dézip de ELOQUA.zip → EDGAHR / JAD

### Sorties

* CSV sources exploitables

---

## 2.2 Enrichissement métier RH – processEdjad

### Entrées

* EMPLOYEE_RTL.csv (EDGAHR)
* JAD_JL.csv (JAD)

### Transformations

* Extraction hiérarchique JAD :

  * Domaine
  * Famille
  * Emploi
  * Position
* Normalisation des codes EDGAHR
* Jointure EDGAHR ↔ JAD
* Ajout des libellés FR / EN

### Sortie

```
Import_EdJad.csv
```

> ⚠️ Fichier **INTERMÉDIAIRE** uniquement

---

## 2.3 Consolidation personnes & structure – processRefog

### Entrées

* Personne.csv
* PersonneDesact.csv
* UO.csv
* UOHierarchique.csv
* Immeuble.csv
* Filiere / SousFiliere

---

### 2.3.1 Séparation des flux personnes

#### Personnes actives (VISIBLE = 1)

* Jointure avec :

  * Immeubles
  * UO
  * Hiérarchies
  * Filières

➡️ Produit :

```
ImportRefog.csv (snapshot intermédiaire)
```

---

#### Personnes désactivées (VISIBLE = 0)

```
PersonneDesact.csv
        │
        ▼
PersonneDesactTmp.csv   (état absolu du jour)
        │
        │ comparaison
        ▼
ImportRefogDesactVieille.csv (état J-1)
        │
        ▼
ImportRefogDesact.csv   (DELTA uniquement)
```

➡️ **Flux événementiel séparé**

---

## 2.4 Jointure finale EdJad → Refog

### Entrées

* ImportRefog.csv (intermédiaire)
* Import_EdJad.csv

### Traitements

* Suppression des entêtes
* Tri par UID
* Jointure :

  * 1 personne Refog
  * * données métier EdJad

### Sortie

```
ImportRefog.csv (FINAL, enrichi métier)
```

---

## 2.5 Nettoyage

### cleanupFiles

* Supprime tous les fichiers intermédiaires
* Conserve uniquement :

  * ImportRefog.csv
  * ImportRefogDesact.csv
  * ImportRefogDesactVieille.csv
  * scripts .sh

---

## 3. Fichiers finaux réellement utilisés

| Fichier                      | Rôle                   | Nature             |
| ---------------------------- | ---------------------- | ------------------ |
| ImportRefog.csv              | Import principal ReFOG | Snapshot enrichi   |
| ImportRefogDesact.csv        | Désactivations         | Delta événementiel |
| ImportRefogDesactVieille.csv | Mémoire                | État cumulatif     |

`Import_EdJad.csv` **n’existe plus à la fin**.

---

## 4. Modèle conceptuel sous-jacent

* Snapshot = état courant
* Delta = événement unique
* OLD = mémoire persistante

Implémenté :

* sans base
* sans ETL
* sans API
* uniquement par fichiers

---

## 5. Phrase de clôture

> Ce script n’est pas un simple batch.
> C’est un **ETL complet déguisé en shell**, avec état, événements et projections.

Fin du diagramme complet.
