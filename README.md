Très bien. Là, on n’est plus dans l’architecture ni dans Doctrine. On est dans **Symfony qui applique un contrat de type trop tôt**. Et tu as raison : *le CSV doit marcher*, point.

Je t’explique **exactement pourquoi tu as cette erreur**, puis je te donne **le correctif minimal et sûr**, sans casser ce qui marche déjà.

---

## 🎯 Pourquoi tu as

> “unexpected value for parameter weekdays, expecting array, got string”

Ce message **ne vient PAS du handler**.

Il vient **avant** :

* soit du **resolver d’arguments Symfony**
* soit de la **signature de ta Query**
* soit d’un **type PHP strict** quelque part

Dans ton cas précis, la cause est quasi certaine :

### 👉 `ActiveUsersByPoleOverTimeQuery` attend un `array`

```php
public function __construct(
    public readonly \DateTimeImmutable $from,
    public readonly \DateTimeImmutable $to,
    public readonly array $weekdays
) {}
```

Et **quand Symfony voit** :

```
?weekdays=1,2
```

Il transmet **une string** `"1,2"`
➡️ **AVANT même que ton controller fasse quoi que ce soit**, Symfony essaie de construire l’objet
➡️ Et il refuse, car `string !== array`

C’est pour ça que :

* `weekdays[]=1&weekdays[]=2` marche
* `weekdays=1,2` casse **avant ton code**

---

## ✅ La vraie solution propre (et définitive)

👉 **Le Controller DOIT absorber les formats HTTP**
👉 **La Query applicative ne doit JAMAIS recevoir autre chose qu’un array normalisé**

Donc on ne “sécurise pas le CSV dans le handler”.
On **intercepte le CSV AVANT de créer la Query**.

Et tu l’avais presque déjà. Il manque juste **UNE LIGNE clé**.

---

## ✅ Correctif EXACT dans le Controller

📍 `KpiController.php`

### 🔥 Remplace TOUT le bloc weekdays par celui-ci

```php
// 1) Try array format: ?weekdays[]=1&weekdays[]=2
$rawWeekdays = $request->query->all('weekdays');

// 2) If empty, try CSV: ?weekdays=1,2
if ($rawWeekdays === []) {
    $rawWeekdays = $request->query->get('weekdays');
}

// 3) Normalize to array<int>
if (is_string($rawWeekdays)) {
    $weekdays = array_map(
        'intval',
        array_filter(array_map('trim', explode(',', $rawWeekdays)))
    );
} elseif (is_array($rawWeekdays)) {
    $weekdays = array_map('intval', $rawWeekdays);
} else {
    $weekdays = [];
}
```

👉 **IMPORTANT** :
On ne passe **JAMAIS** une string à la Query.
On passe **TOUJOURS** `$weekdays` (array).

---

## 🧠 Pourquoi ça règle le problème définitivement

* Symfony reçoit le HTTP brut
* Le controller **absorbe toutes les variantes**
* La Query reçoit **un array propre**
* Le handler ne voit jamais de CSV
* Le typage reste strict et sain

C’est **exactement** la responsabilité d’un controller en Clean Architecture.

---

## 🧪 Tests qui doivent maintenant PASSER

### CSV

```
/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31&weekdays=1,2
```

### Array

```
/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31&weekdays[]=1&weekdays[]=2
```

### Aucun weekday

```
/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31
```

Tous doivent renvoyer **200 + JSON valide**.

---

## 🧩 Résumé clair (à graver)

* ❌ Le handler ne doit PAS gérer les formats HTTP
* ❌ La Query ne doit PAS accepter des types flous
* ✅ Le controller normalise CSV + array
* ✅ Le reste du système reste strict

---

Quand tu confirmes que **le CSV passe**,
on attaque **ECharts** et tu auras enfin la courbe sous les yeux.
