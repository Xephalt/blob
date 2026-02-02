Oui, tu as **100 % raison**. Et ce n’est pas un “petit détail”, c’est exactement le genre de micro-incohérence qui pourrit un système si on la laisse passer. Bonne pioche.

Je vais être clair, puis te donner **la correction propre**, sans mapping chelou.

---

## 1️⃣ Le vrai problème (et tu l’as identifié)

Aujourd’hui, dans TON système :

* **Stimulus / UI** envoie :

  * `weekdays[]=0` → **lundi**
  * `weekdays[]=1` → mardi
  * …
  * `weekdays[]=6` → dimanche

👉 Donc **0-based, ISO-like, lundi = 0**

Mais :

* PHP `DateTime::format('N')` → **1 = lundi**
* MySQL `DAYOFWEEK()` → **1 = dimanche**

Bref : **trois conventions différentes**, et aucune n’est “mauvaise”, mais les mélanger est une erreur.

👉 **Tu as raison : on ne doit PAS adapter l’UI à la DB.**
👉 **On doit adapter le backend au contrat UI.**

---

## 2️⃣ Décision Clean Architecture (la bonne)

On fait ceci :

### ❌ On NE FAIT PLUS :

* `DAYOFWEEK()` en SQL
* mapping ISO → MySQL
* conversion “bizarre” dans le controller

### ✅ On FAIT :

* le **repository ne connaît PAS les weekdays**
* le **handler filtre les jours en PHP**
* les weekdays restent **0-based, lundi = 0**, partout

👉 Résultat :

* le front ne change pas
* le controller ne bidouille pas
* le repository reste simple
* le handler porte la règle métier (logique)

C’est **exactement** ce que la Clean Arch demande.

---

## 3️⃣ Concrètement : ce qu’on change

### 🔥 À SUPPRIMER (si ce n’est pas déjà fait)

Dans le **controller** ❌ :

```php
$weekdays = array_map(
    static fn (int $n): int => ($n % 7) + 1,
    $weekdays
);
```

👉 **On enlève ça complètement.**

Le controller doit juste faire :

```php
$weekdays = array_map(
    'intval',
    $request->query->all('weekdays') ?? []
);
```

Et basta.

---

## 4️⃣ Où on filtre maintenant les weekdays (le bon endroit)

👉 **Dans le Handler**, pas ailleurs.

Rappel :

* Repository → données brutes
* Handler → logique métier

---

## 5️⃣ Correction du Handler (ISO, 0-based, propre)

📍 **Fichier**

```
src/Application/Admin/UseCase/GetActiveUsersByPoleOverTimeHandler.php
```

### 5.1 Génération des dates (ISO, lundi = 0)

Remplace la génération actuelle par **celle-ci** :

```php
$dates = [];
$cursor = $query->from;

while ($cursor <= $query->to) {
    // ISO weekday: Monday = 0, Sunday = 6
    $weekday = ((int) $cursor->format('N')) - 1;

    if (
        empty($query->weekdays)
        || in_array($weekday, $query->weekdays, true)
    ) {
        $dates[] = $cursor->format('Y-m-d');
    }

    $cursor = $cursor->modify('+1 day');
}
```

👉 Maintenant :

* UI envoie `weekdays[]=0` → lundi
* Handler comprend `0` → lundi
* aucune conversion
* aucune ambiguïté

---

## 6️⃣ Et le repository dans tout ça ?

Il reste **inchangé** (et c’est une bonne chose).

Il retourne :

```php
day (Y-m-d)
pole (string)
active_users
```

Le handler décide **quoi garder**, **quoi ignorer**.

---

## 7️⃣ Format `weekdays[]` vs `weekdays=0,1,2`

Tu as aussi raison là-dessus.

### Ce que tu fais déjà (et qui est très bien) :

```
?weekdays[]=0&weekdays[]=1
```

Symfony :

```php
$request->query->all('weekdays') // [0, 1]
```

👉 **C’est le format recommandé.**
👉 Pas de CSV.
👉 Pas de parsing manuel.
👉 Aucun changement à faire.

---

## 8️⃣ Résumé clair (à garder en tête)

* ✔️ **Convention unique** : lundi = 0
* ✔️ UI, Controller, Handler alignés
* ✔️ Repository ignorant des weekdays
* ✔️ Aucune fonction SQL fragile
* ✔️ Aucune conversion “magique”

Tu viens de faire un **vrai choix d’architecture**, pas un patch.

---

## 9️⃣ Ce que tu fais maintenant

1. Supprime toute conversion weekday dans le controller
2. Mets le filtrage ISO (0-based) **dans le handler**
3. Recharge :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-25&weekdays[]=0
```

👉 Si tu vois que seuls les lundis sortent → 🎯 gagné
👉 Ensuite → **ECharts**

Dis-moi quand le JSON est bon.
Après ça, on passe au visuel, et là tu vas enfin respirer.
