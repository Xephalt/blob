Bon. On continue alors. Range le café, ça va être précis.

👉 **Étape 2 uniquement dans ce message**
👉 **Application / Admin**
👉 **Query + Handler + DTOs**
👉 **AUCUN controller, AUCUN front, AUCUN ECharts**

On transforme maintenant **des faits bruts** (repository) en **données métier exploitables**.

---

# 🥈 ÉTAPE 2 — Use case Clean Architecture

## Objectif

Construire **le use case applicatif** :

> “Obtenir, pour une période et des weekdays donnés,
> le pourcentage d’utilisateurs actifs par pôle et par jour.”

Le repository sait compter.
Le use case sait **interpréter**.

---

## 1️⃣ La Query (entrée du use case)

📍 **Fichier à créer**

```
src/Application/Admin/Query/ActiveUsersByPoleOverTimeQuery.php
```

### Contenu

```php
<?php

namespace App\Application\Admin\Query;

final class ActiveUsersByPoleOverTimeQuery
{
    /**
     * @param int[] $weekdays
     */
    public function __construct(
        public readonly \DateTimeImmutable $from,
        public readonly \DateTimeImmutable $to,
        public readonly array $weekdays = []
    ) {}
}
```

✔️ La Query est **immuable**
✔️ Elle décrit l’intention, pas l’implémentation
✔️ Aucun lien avec HTTP ou Doctrine

---

## 2️⃣ Les DTOs de sortie (orientés graphique)

On prépare **exactement** ce dont le front aura besoin.
Pas plus. Pas moins.

---

### 📍 DTO racine

```
src/Application/Admin/Dto/ActiveUsersByPoleChartDto.php
```

```php
<?php

namespace App\Application\Admin\Dto;

final class ActiveUsersByPoleChartDto
{
    /**
     * @param string[] $dates
     * @param PoleSeriesDto[] $series
     */
    public function __construct(
        public readonly array $dates,
        public readonly array $series
    ) {}
}
```

---

### 📍 Série par pôle

```
src/Application/Admin/Dto/PoleSeriesDto.php
```

```php
<?php

namespace App\Application\Admin\Dto;

final class PoleSeriesDto
{
    /**
     * @param float[] $values
     */
    public function __construct(
        public readonly string $poleName,
        public readonly array $values
    ) {}
}
```

✔️ Le front pourra activer/désactiver une série
✔️ Les valeurs sont **déjà en pourcentage**

---

## 3️⃣ Extension minimale du UserRepository (lecture seule)

Le use case a besoin du **dénominateur**.

### 📍 Interface à compléter

```
src/Repository/UserRepository.php
```

👉 **Ajoute cette méthode** (sans casser l’existant) :

```php
/**
 * @return array<int, int> poleId => totalUsers
 */
public function countUsersByPole(): array;
```

### 👉 Implémentation Doctrine (à ajouter)

```php
public function countUsersByPole(): array
{
    $qb = $this->createQueryBuilder('u')
        ->select('p.id AS pole_id', 'COUNT(u.id) AS total_users')
        ->join('u.pole', 'p')
        ->groupBy('p.id');

    $results = $qb->getQuery()->getArrayResult();

    $counts = [];
    foreach ($results as $row) {
        $counts[(int) $row['pole_id']] = (int) $row['total_users'];
    }

    return $counts;
}
```

✔️ Toujours brut
✔️ Pas de logique métier
✔️ Pas de pourcentage

---

## 4️⃣ Le Handler (le cœur du sujet)

📍 **Fichier à créer**

```
src/Application/Admin/UseCase/GetActiveUsersByPoleOverTimeHandler.php
```

---

### Contenu complet

```php
<?php

namespace App\Application\Admin\UseCase;

use App\Application\Admin\Query\ActiveUsersByPoleOverTimeQuery;
use App\Application\Admin\Dto\ActiveUsersByPoleChartDto;
use App\Application\Admin\Dto\PoleSeriesDto;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\PoleRepository;

final class GetActiveUsersByPoleOverTimeHandler
{
    public function __construct(
        private MessageRepository $messageRepository,
        private UserRepository $userRepository,
        private PoleRepository $poleRepository
    ) {}

    public function __invoke(
        ActiveUsersByPoleOverTimeQuery $query
    ): ActiveUsersByPoleChartDto {
        // 1. Générer la liste complète des dates filtrées
        $dates = [];
        $cursor = $query->from;

        while ($cursor <= $query->to) {
            if (
                empty($query->weekdays)
                || in_array((int) $cursor->format('N'), $query->weekdays, true)
            ) {
                $dates[] = $cursor->format('Y-m-d');
            }
            $cursor = $cursor->modify('+1 day');
        }

        // 2. Récupération des données brutes
        $activeByDayAndPole =
            $this->messageRepository->countActiveUsersPerDayByPole(
                $query->from,
                $query->to,
                $query->weekdays
            );

        $totalUsersByPole = $this->userRepository->countUsersByPole();

        // 3. Indexation [poleId][day] => activeUsers
        $indexed = [];
        foreach ($activeByDayAndPole as $row) {
            $indexed[(int) $row['pole_id']][$row['day']] =
                (int) $row['active_users'];
        }

        // 4. Construction des séries
        $series = [];

        foreach ($totalUsersByPole as $poleId => $totalUsers) {
            if ($totalUsers === 0) {
                continue;
            }

            $pole = $this->poleRepository->find($poleId);
            if (!$pole) {
                continue;
            }

            $values = [];
            foreach ($dates as $day) {
                $active = $indexed[$poleId][$day] ?? 0;
                $values[] = round(($active / $totalUsers) * 100, 2);
            }

            $series[] = new PoleSeriesDto(
                poleName: $pole->getName(),
                values: $values
            );
        }

        return new ActiveUsersByPoleChartDto(
            dates: $dates,
            series: $series
        );
    }
}
```

---

## 5️⃣ Ce que tu as maintenant (important)

✔️ Un use case **testable**
✔️ Aucun couplage HTTP / Twig / JS
✔️ Un DTO **directement consommable par ECharts**
✔️ Une logique métier **centralisée et lisible**

---

## ✅ Ce que tu fais maintenant

1. Créer **tous les fichiers ci-dessus**
2. Vérifier que :

   * `PoleRepository` existe bien
   * `User::pole` est mappé correctement
3. Me dire **OK** quand c’est fait

---

👉 **Étape suivante (quand tu dis OK)**
On ajoute :

* la **route dans ton KPI controller**
* la **normalisation JSON**
* puis **l’intégration ECharts côté front**

Un étage à la fois. Sinon on triche.
