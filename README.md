Bon. On respire deux secondes, on enlève le bruit UI, et on fait ce que tu demandes vraiment : **la logique métier de la carte “Réponses”**, propre, lisible, et ISO avec ce que tu as déjà.

Je vais être cash, parce que là on est exactement au moment où on peut soit faire un truc propre, soit fabriquer un futur plat de spaghetti statistiques.

---

## 1️⃣ Ce que dit le métier (pas la DB, pas le UI)

Indépendamment de la table `message`, **le besoin métier est clair** :

> Sur une période donnée, je veux **des métriques agrégées sur les réponses** :

* Temps moyen de réponse
* Proportion de réponses :

  * likées
  * dislikées
  * sans évaluation
* Et pour chacune, **l’évolution par rapport à la période précédente**

Ça, c’est **un Use Case de lecture**, pur CQRS.

Pas un controller.
Pas un repository exposé au twig.
Pas une bidouille dans le front.

---

## 2️⃣ Où ça vit dans une archi propre

### 👉 **Dans l’APPLICATION**, pas ailleurs.

Concrètement :

```
src/
 └─ Application/
     └─ Query/
         └─ MessageResponseMetrics/
             ├─ MessageResponseMetricsQuery.php
             ├─ MessageResponseMetricsHandler.php
             ├─ MessageResponseMetrics.php   ← DTO de sortie
```

Pourquoi ici ?

* Ce n’est **pas du domaine pur** (pas d’invariant métier)
* Ce n’est **pas de l’infra** (SQL, Doctrine, etc.)
* C’est une **orchestration de lecture** → Application Query

---

## 3️⃣ Le modèle mental (très important)

### Ce Use Case répond à UNE question :

> “Quelles sont les métriques de réponses sur une période donnée, comparées à la période précédente ?”

Donc :

* **1 Query**
* **1 Handler**
* **1 DTO de sortie**

Pas 4 méthodes éparpillées.
Pas de calculs dans le controller.
Pas de logique cachée dans Twig.

---

## 4️⃣ La Query (intention métier, rien d’autre)

```php
// src/Application/Query/MessageResponseMetrics/MessageResponseMetricsQuery.php

final class MessageResponseMetricsQuery
{
    public function __construct(
        public readonly \DateTimeImmutable $from,
        public readonly \DateTimeImmutable $to,
    ) {}
}
```

Simple. Immuable. Lisible.
Elle dit **quoi**, pas **comment**.

---

## 5️⃣ Le DTO de sortie (ce que la carte consomme)

```php
// src/Application/Query/MessageResponseMetrics/MessageResponseMetrics.php

final class MessageResponseMetrics
{
    public function __construct(
        public readonly float $avgResponseTimeSeconds,

        public readonly float $likedRatio,
        public readonly float $dislikedRatio,
        public readonly float $noEvaluationRatio,

        public readonly float $avgResponseTimeEvolution,
        public readonly float $likedRatioEvolution,
        public readonly float $dislikedRatioEvolution,
        public readonly float $noEvaluationRatioEvolution,
    ) {}
}
```

Important :

* **Pas de logique**
* **Pas de calcul**
* C’est un **contrat de sortie**, point

Le front ou Twig n’a plus à deviner quoi que ce soit.

---

## 6️⃣ Le Handler (là où la logique vit vraiment)

### Responsabilités claires :

* Calculer la période courante
* Calculer la période précédente
* Comparer
* Normaliser (ratios, pourcentages)

```php
// src/Application/Query/MessageResponseMetrics/MessageResponseMetricsHandler.php

final class MessageResponseMetricsHandler
{
    public function __construct(
        private MessageRepository $messageRepository,
    ) {}

    public function __invoke(MessageResponseMetricsQuery $query): MessageResponseMetrics
    {
        $current = $this->messageRepository->responseStatsBetween(
            $query->from,
            $query->to
        );

        $previousPeriod = $this->previousPeriod($query->from, $query->to);

        $previous = $this->messageRepository->responseStatsBetween(
            $previousPeriod['from'],
            $previousPeriod['to']
        );

        return new MessageResponseMetrics(
            avgResponseTimeSeconds: $current['avg_response_time'],
            likedRatio: $current['liked_ratio'],
            dislikedRatio: $current['disliked_ratio'],
            noEvaluationRatio: $current['no_eval_ratio'],

            avgResponseTimeEvolution: $this->evolution(
                $current['avg_response_time'],
                $previous['avg_response_time']
            ),
            likedRatioEvolution: $this->evolution(
                $current['liked_ratio'],
                $previous['liked_ratio']
            ),
            dislikedRatioEvolution: $this->evolution(
                $current['disliked_ratio'],
                $previous['disliked_ratio']
            ),
            noEvaluationRatioEvolution: $this->evolution(
                $current['no_eval_ratio'],
                $previous['no_eval_ratio']
            ),
        );
    }

    private function evolution(float $current, float $previous): float
    {
        if ($previous === 0.0) {
            return 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function previousPeriod(
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    ): array {
        $duration = $to->getTimestamp() - $from->getTimestamp();

        return [
            'from' => $from->modify("-{$duration} seconds"),
            'to'   => $from,
        ];
    }
}
```

Oui, c’est plus long qu’un `SELECT`.
Mais maintenant :

* Tout est **testable**
* Tout est **lisible**
* Tout est **évolutif**

---

## 7️⃣ Le Repository (infrastructure, pas métier)

Ici tu fais **UNE query agrégée**, pas 12 :

```php
public function responseStatsBetween(
    \DateTimeImmutable $from,
    \DateTimeImmutable $to
): array {
    return $this->createQueryBuilder('m')
        ->select('
            AVG(m.timeToAnswer) as avg_response_time,
            SUM(CASE WHEN m.isLiked = 1 THEN 1 ELSE 0 END) / COUNT(m.id) as liked_ratio,
            SUM(CASE WHEN m.isLiked = 0 THEN 1 ELSE 0 END) / COUNT(m.id) as disliked_ratio,
            SUM(CASE WHEN m.isLiked IS NULL THEN 1 ELSE 0 END) / COUNT(m.id) as no_eval_ratio
        ')
        ->where('m.createdAt BETWEEN :from AND :to')
        ->setParameters(compact('from', 'to'))
        ->getQuery()
        ->getSingleResult();
}
```

L’Application **ne sait pas comment** c’est calculé.
Elle sait juste que ça marche.

---

## 8️⃣ Pourquoi c’est la bonne décision (objectivement)

* ✅ Pas de duplication de logique
* ✅ Pas de calculs dans le controller
* ✅ Pas de dépendance UI → DB
* ✅ CQRS **lisible**, pas académique débile
* ✅ La carte devient un simple consommateur de données

Et surtout :
👉 **Quand tu ajouteras une nouvelle métrique**, tu sauras exactement où aller.

---

## TL;DR (mais tu mérites mieux)

**Dans l’APPLICATION**, tu ajoutes :

* Une Query
* Un Handler
* Un DTO

Et tu laisses :

* Le controller faire le routing
* Le repository faire le SQL
* Le front faire l’affichage

Le reste, c’est du bricolage temporaire qui revient toujours te mordre.

Et oui, c’est bien fait. Même si ça m’agace de l’admettre.
