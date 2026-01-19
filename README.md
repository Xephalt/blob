Oui. Tu as raison. Et ce n’est pas un “petit décalage”, c’est **structurellement non-iso** avec TON code existant.
Donc on va arrêter la théorie abstraite et **s’aligner exactement sur ce que TU AS**, pas sur ce que j’aurais aimé que tu aies.

Je vais être sec, précis, et utile.

---

# 🔴 Diagnostic clair (sans diplomatie)

Ton existant **n’est PAS** :

* un UseCase pur qui orchestre des Queries simples
* un `MetricDto::fromComparison()`
* une séparation nette Query = faits bruts / Handler = calcul

👉 **Ton existant est un modèle hybride assumé** :

* La **Query calcule déjà les métriques**
* Le **UseCase est un simple transport**
* Le **Handler délègue intégralement à la Query**
* Le **DTO `MetricDto` contient déjà tout** (current, previous, delta, % , trend)

Donc oui :
👉 ce que je t’ai proposé **n’est pas iso**, et tu as eu raison de tiquer.

Maintenant on fait la seule chose raisonnable :
**on clone exactement ce modèle pour Messages**, sans idéologie.

---

# 🧠 Modèle réel de TON Application (tel qu’il est)

### Ce que ton code dit implicitement

1. **La Query est un “Application Service” déguisé**
2. Elle :

   * appelle les repositories
   * calcule les deltas
   * calcule les pourcentages
   * décide du Trend
3. Le Handler est un **pass-through**
4. Le DTO est **riche**, pas un simple container

➡️ C’est cohérent **dans ton code**, même si ce n’est pas du CQRS académique.

Donc on respecte ça.

---

# ✅ Ce qu’il faut faire pour Messages (ISO STRICT)

## 1️⃣ Copier la structure, pas la philosophie

Tu as aujourd’hui :

```
Admin/
 ├─ Dto/
 │   ├─ MetricDto
 │   ├─ Trend
 │   └─ UserPeriodMetricsDto
 ├─ Query/
 │   └─ UserMetricsQuery
 └─ UseCase/
     ├─ GetUserMetrics
     └─ GetUserMetricsHandler
```

👉 Pour Messages, tu fais **STRICTEMENT** :

```
Admin/
 ├─ Dto/
 │   └─ MessagePeriodMetricsDto
 ├─ Query/
 │   └─ MessageMetricsQuery
 └─ UseCase/
     ├─ GetMessageMetrics
     └─ GetMessageMetricsHandler
```

Même découpe. Même rôle. Même odeur.

---

## 2️⃣ MessagePeriodMetricsDto (clone du User)

```php
final class MessagePeriodMetricsDto
{
    public function __construct(
        public readonly MetricDto $messagesCount,
        public readonly MetricDto $avgMessagesPerUser,
        public readonly Period $currentPeriod,
        public readonly Period $comparisonPeriod,
    ) {}
}
```

⚠️ **Tu gardes Period dans le DTO**, parce que ton UserPeriodMetricsDto le fait déjà.
Ce n’est pas “propre”, mais c’est **cohérent avec l’existant**.

---

## 3️⃣ MessageMetricsQuery (copie conforme de UserMetricsQuery)

C’est LE point clé.

### Signature

```php
final class MessageMetricsQuery
{
    public function __construct(
        private MessageRepository $messageRepository,
        private UserRepository $userRepository,
    ) {}
```

Oui, **UserRepository ici**, exactement comme tu utilises MessageRepository pour active users dans Users.

---

### Méthode execute (ISO)

```php
public function execute(
    ResolvedPeriod $period,
    ?array $weekdays
): MessagePeriodMetricsDto {
```

Même signature. Même contrat.

---

### Calculs internes (adaptés)

```php
$currentMessages = $this->messageRepository
    ->countMessagesBetween($period->current(), $weekdays);

$previousMessages = $this->messageRepository
    ->countMessagesBetween($period->comparison(), $weekdays);

$currentActiveUsers = $this->messageRepository
    ->countActiveUsersBetween($period->current(), $weekdays);

$previousActiveUsers = $this->messageRepository
    ->countActiveUsersBetween($period->comparison(), $weekdays);
```

Puis :

```php
$currentAvg = $currentActiveUsers === 0
    ? 0
    : (int) round($currentMessages / $currentActiveUsers);

$previousAvg = $previousActiveUsers === 0
    ? 0
    : (int) round($previousMessages / $previousActiveUsers);
```

---

### Construction du DTO (identique à User)

```php
return new MessagePeriodMetricsDto(
    $this->buildMetric($currentMessages, $previousMessages),
    $this->buildMetric($currentAvg, $previousAvg),
    $period->current(),
    $period->comparison(),
);
```

---

### buildMetric = COPIÉ COLLÉ

Tu **ne touches pas** à la logique existante :

```php
private function buildMetric(int $current, int $previous): MetricDto
{
    $delta = $current - $previous;

    if ($previous === 0) {
        $evolution = $current === 0 ? 0.0 : 100.0;
    } else {
        $evolution = ($delta / $previous) * 100;
    }

    $trend = match (true) {
        $delta > 0 => Trend::UP,
        $delta < 0 => Trend::DOWN,
        default => Trend::STABLE,
    };

    return new MetricDto(
        $current,
        $previous,
        $delta,
        $evolution,
        $trend
    );
}
```

Oui, c’est de la duplication.
Oui, ce sera refactorable plus tard.
👉 **Mais aujourd’hui, c’est ISO.**

---

## 4️⃣ UseCase & Handler (aucune créativité)

### Command

```php
final class GetMessageMetrics
{
    public function __construct(
        public readonly ResolvedPeriod $period,
        public readonly ?array $weekdays,
    ) {}
}
```

### Handler

```php
final class GetMessageMetricsHandler
{
    public function __construct(
        private MessageMetricsQuery $query,
    ) {}

    public function handle(GetMessageMetrics $command): MessagePeriodMetricsDto
    {
        return $this->query->execute(
            $command->period,
            $command->weekdays
        );
    }
}
```

---

# 🟢 Conclusion honnête

Tu avais raison de dire :

> “ce n’est pas iso avec l’existant”

✔️ Mon précédent message était **architecturalement juste**,
❌ mais **contextuellement faux pour TON code**.

La bonne solution ici n’est pas la pureté, c’est **l’alignement**.

👉 On clone **exactement** le pattern UserMetrics.
👉 On ne refactore RIEN tant que la carte Messages n’est pas verte.
👉 On parlera factorisation **après**, pas avant.

Si tu veux, prochaine étape :

* je te propose **le diff exact fichier par fichier**
* ou on prépare **le futur refactor commun MetricBuilder** (mais pas maintenant)

Tu choisis.
