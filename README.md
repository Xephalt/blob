Parfait, on va être **chirurgical**, parce que là c’est juste un problème de **placement**, pas de concept. Et tu as raison de bloquer avant de coller n’importe où.

---

# 🎯 Où mettre EXACTEMENT ce code

Ce code **n’a rien à faire dans le repository**.
Il n’a rien à faire non plus dans le handler.

👉 **Il va dans le Controller**, au moment où tu traduis l’HTTP → Query applicative.

Pourquoi ?

* parce que le format des weekdays dépend **du protocole HTTP**
* parce que MySQL est un **détail d’infrastructure**
* parce que la Query applicative doit recevoir des données **cohérentes et prêtes**

---

## 📍 Fichier concerné

```
src/Controller/Admin/KpiController.php
```

Dans **la méthode** :

```php
public function activeUsersByPoleOverTime(...)
```

---

## 🧩 Version AVANT (simplifiée)

Tu as probablement quelque chose comme :

```php
$weekdays = array_map(
    'intval',
    $request->query->all('weekdays') ?? []
);

$query = new ActiveUsersByPoleOverTimeQuery(
    from: $from,
    to: $to,
    weekdays: $weekdays
);
```

---

## ✅ Version APRÈS (corrigée, complète)

👉 **Remplace ce bloc par celui-ci**, exactement là :

```php
$weekdays = array_map(
    'intval',
    $request->query->all('weekdays') ?? []
);

// Conversion PHP (1=Mon..7=Sun) → MySQL DAYOFWEEK (1=Sun..7=Sat)
$weekdays = array_map(
    static fn (int $n): int => ($n % 7) + 1,
    $weekdays
);

$query = new ActiveUsersByPoleOverTimeQuery(
    from: $from,
    to: $to,
    weekdays: $weekdays
);
```

C’est **tout**.

---

## 🧠 Pourquoi c’est le bon endroit (et pas ailleurs)

* Le front parle en **jours ISO** (1=lundi)
* PHP `DateTime::format('N')` parle en **jours ISO**
* MySQL `DAYOFWEEK()` parle un **autre dialecte**

👉 La conversion est une **adaptation HTTP → infra**, donc **Controller**.

Le Handler doit pouvoir raisonner comme si le monde était logique.
Le Repository doit juste exécuter.

---

## 🔴 Ce que tu ne dois PAS faire

* ❌ Mettre ça dans le repository
* ❌ Mettre ça dans la Query
* ❌ Mettre ça dans le handler
* ❌ Mettre ça dans le front “parce que c’est plus simple”

Sinon, tu crées un **couplage invisible** qui te pètera à la figure plus tard.

---

## ✅ Ce que tu fais maintenant

1. Colle exactement ce bloc **dans le controller**
2. Recharge l’URL :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-25&weekdays[]=1&weekdays[]=2
```

3. Vérifie :

   * pas d’erreur DQL
   * JSON qui sort
   * les jours filtrés correctement

---

Si ça marche → 🎉 **on attaque ECharts**
Si ça casse → copie **le message exact**, on le démonte.

Tu fais exactement ce qu’il faut.
