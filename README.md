Très bonne question. Et non, ce n’est pas un “raccourci de flemme”. C’est **un choix de conception délibéré**. Je vais t’expliquer **pourquoi**, puis je te donne **le code exact à copier-coller**, sans t’envoyer refaire la cathédrale Notre-Dame en CQRS.

---

## 🎯 Le vrai sujet derrière ta question

Tu demandes en gros :

> Est-ce que calculer
> `avg = messages / utilisateurs actifs`
> **dans la Query** à partir de deux compteurs existants
> est mieux ou pire que
> **une requête SQL dédiée** `AVG(messages_per_user)` ?

Spoiler : **dans TON contexte, la première option est objectivement meilleure**.

---

## 1️⃣ Pourquoi je NE crée PAS `countAverageMessagesPerUserBetween()`

### ❌ Option “requête dédiée” (celle que tu proposes)

```sql
SELECT AVG(message_count)
FROM (
  SELECT COUNT(*) AS message_count
  FROM message m
  GROUP BY user_id
) t
```

Ça a l’air “propre” sur le papier. En réalité :

### Problèmes réels

#### 1. **Responsabilité floue**

Le repository ferait :

* du comptage
* de l’agrégation
* de la logique métier implicite (qu’est-ce qu’un “utilisateur actif” ?)

👉 Le repo devient **opinionated**.

---

#### 2. **Couplage sémantique caché**

Ta moyenne dépend implicitement de :

* la définition de “utilisateur actif”
* la période
* les weekdays
* les jointures

Si demain tu changes **la définition d’un utilisateur actif** :

* la méthode “average” devient fausse
* sans que rien ne te le dise

C’est du couplage **silencieux**. Le pire.

---

#### 3. **Refactor futur plus dur**

Si plus tard tu veux :

* afficher le nombre d’utilisateurs actifs **et** la moyenne
* changer la formule
* exclure certains users

👉 Tu devras :

* soit refaire une deuxième requête
* soit dédoubler la logique SQL

Donc **moins réutilisable**, pas plus.

---

## 2️⃣ Pourquoi la solution “2 compteurs + division” est la bonne ici

### ✅ Ce que fait le repository (et UNIQUEMENT ça)

Le repository répond à des questions **factuelles** :

* “Combien de messages ?”
* “Combien d’utilisateurs distincts ont envoyé au moins un message ?”

👉 **Aucune interprétation. Juste des chiffres.**

---

### ✅ Ce que fait la Query (c’est son rôle)

La Query :

* orchestre
* combine
* dérive des métriques
* applique des règles simples (division, delta, %)

👉 **C’est exactement sa responsabilité.**

---

### 🧠 En clair

| Approche               | Lisibilité | Réutilisable | Refactor-safe |
| ---------------------- | ---------- | ------------ | ------------- |
| 2 compteurs + division | ✅ Très     | ✅ Oui        | ✅ Oui         |
| Requête “AVG magique”  | ❌ Moyenne  | ❌ Non        | ❌ Fragile     |

Donc non, ce n’est pas “coupler pour rien”.
C’est **découpler intelligemment**.

---

## 3️⃣ Et côté perf alors ?

Tu pourrais penser :

> “Une requête SQL = plus rapide que deux”

Dans 99 % des dashboards :

* les deux requêtes sont indexées
* les volumes sont raisonnables
* le coût est négligeable

Mais surtout :
👉 **la lisibilité et la maintenabilité priment largement ici**.

Si un jour c’est critique :

* tu optimises
* tu ajoutes une vue SQL
* ou un cache

Pas aujourd’hui. Pas maintenant.

---

## 4️⃣ Code final à copier-coller (propre et ISO)

---

### 📁 `MessageRepository.php`

#### A. Compter les messages

```php
public function countMessagesBetween(
    Period $period,
    ?array $weekdays
): int {
    $sql = <<<SQL
        SELECT COUNT(m.id)
        FROM message m
        WHERE m.created_at BETWEEN :from AND :to
    SQL;

    if ($weekdays !== null) {
        $sql .= ' AND DAYOFWEEK(m.created_at) IN (:weekdays)';
    }

    $params = [
        'from' => $period->from()->format('Y-m-d H:i:s'),
        'to'   => $period->to()->format('Y-m-d H:i:s'),
    ];

    if ($weekdays !== null) {
        $params['weekdays'] = array_map(
            static fn (int $d): int => $d === 7 ? 1 : $d + 1,
            $weekdays
        );
    }

    $conn = $this->getEntityManager()->getConnection();

    return (int) $conn->executeQuery(
        $sql,
        $params,
        $weekdays !== null
            ? ['weekdays' => \Doctrine\DBAL\ArrayParameterType::INTEGER]
            : []
    )->fetchOne();
}
```

#### B. Utilisateurs actifs

👉 **Tu l’as déjà. On ne touche pas.**

---

### 📁 `MessageMetricsQuery.php`

#### Helpers

```php
private function average(int $numerator, int $denominator): float
{
    if ($denominator === 0) {
        return 0.0;
    }

    return $numerator / $denominator;
}

private function buildFloatMetric(float $current, float $previous): MetricDto
{
    $delta = $current - $previous;

    if ($previous == 0.0) {
        $evolution = $current == 0.0 ? 0.0 : 100.0;
    } else {
        $evolution = ($delta / $previous) * 100.0;
    }

    $trend = match (true) {
        $evolution > 0 => Trend::UP,
        $evolution < 0 => Trend::DOWN,
        default => Trend::STABLE,
    };

    return new MetricDto(
        (int) round($current),
        (int) round($previous),
        (int) round($delta),
        round($evolution, 1),
        $trend
    );
}
```

---

#### Dans `execute(...)`

```php
$currentMessages = $this->messageRepository
    ->countMessagesBetween($period->current(), $weekdays);

$previousMessages = $this->messageRepository
    ->countMessagesBetween($period->comparison(), $weekdays);

$currentActiveUsers = $this->messageRepository
    ->countActiveUsersBetween($period->current(), $weekdays);

$previousActiveUsers = $this->messageRepository
    ->countActiveUsersBetween($period->comparison(), $weekdays);

$currentAvg = $this->average($currentMessages, $currentActiveUsers);
$previousAvg = $this->average($previousMessages, $previousActiveUsers);

return new MessagePeriodMetricsDto(
    $this->buildMetric($currentMessages, $previousMessages),
    $this->buildFloatMetric($currentAvg, $previousAvg),
    $period->current(),
    $period->comparison()
);
```

---

## 🧠 Conclusion honnête

* Tu **aurais pu** faire une requête SQL dédiée
* Mais tu aurais :

  * perdu en lisibilité
  * couplé des règles métier
  * compliqué les refactors futurs

Là :

* chaque méthode a **un sens clair**
* chaque chiffre est **réutilisable**
* la moyenne est **une dérivation explicite**

C’est du **CQRS pragmatique**, pas académique, pas paresseux.

Et franchement, ton instinct était bon de poser la question. C’est exactement ce genre de décision qui distingue “ça marche” de “ça tient dans le temps”.
