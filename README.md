Oui. Normal. Et même **prévisible**.
Tu n’as rien cassé “par erreur”, tu as juste **fait évoluer la source de vérité** (string `u.pole`) sans encore **aligner le handler**. Donc le handler lit encore un monde qui n’existe plus. Doctrine n’y est pour rien cette fois.

On corrige **une seule chose : le handler**. Rien d’autre.

---

# 🎯 Pourquoi tu as cette erreur

Erreur :

```
undefined array key "pole_id"
```

Dans :

```php
$indexed[(int) $row['pole_id']][$row['day']] = ...
```

👉 Or, **ton repository ne retourne PLUS `pole_id`**.
Il retourne maintenant :

```php
[
  'day' => '2026-01-02',
  'pole' => 'Groupe Communication',
  'active_users' => 42
]
```

Donc :

* ❌ `pole_id` n’existe plus
* ❌ cast en `(int)` n’a plus aucun sens
* ❌ le handler est encore en version “ID-based”

On le passe en **string-based**, comme tu l’as décidé.

---

# ✅ Correction EXACTE du handler

📍 **Fichier**

```
src/Application/Admin/UseCase/GetActiveUsersByPoleOverTimeHandler.php
```

---

## 1️⃣ Remplacer l’indexation (le cœur du bug)

### ❌ Ancien code (à SUPPRIMER)

```php
$indexed = [];
foreach ($activeByDayAndPole as $row) {
    $indexed[(int) $row['pole_id']][$row['day']] =
        (int) $row['active_users'];
}
```

---

### ✅ Nouveau code (ALIGNÉ avec `user.pole` string)

👉 **Remplace par ceci** :

```php
$indexed = [];
foreach ($activeByDayAndPole as $row) {
    $pole = $row['pole'];        // STRING
    $day = $row['day'];
    $indexed[$pole][$day] = (int) $row['active_users'];
}
```

---

## 2️⃣ Boucle de construction des séries (à adapter aussi)

### ❌ Ancien code (ID-based)

```php
foreach ($totalUsersByPole as $poleId => $totalUsers) {
    if ($totalUsers === 0) {
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
```

---

### ✅ Nouveau code (STRING-based, SIMPLE)

👉 **Remplace entièrement la boucle par celle-ci** :

```php
$series = [];

foreach ($totalUsersByPole as $poleLabel => $totalUsers) {
    if ($totalUsers === 0) {
        continue;
    }

    $values = [];
    foreach ($dates as $day) {
        $active = $indexed[$poleLabel][$day] ?? 0;
        $values[] = round(($active / $totalUsers) * 100, 2);
    }

    $series[] = new PoleSeriesDto(
        poleName: $poleLabel,
        values: $values
    );
}
```

👉 Plus de `PoleRepository`
👉 Plus de `find()`
👉 Plus d’ID
👉 Juste la string, comme voulu

---

## 3️⃣ Résumé des changements (pour être sûr)

Tu as maintenant :

### Repository

* retourne `day`, `pole` (string), `active_users`

### UserRepository

* retourne `poleLabel => totalUsers`

### Handler

* indexe par **string**
* calcule les %
* expose `poleName` directement

Tout est **cohérent**.

---

## ✅ Ce que tu fais maintenant

1. Corrige le handler comme ci-dessus
2. Recharge :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-25
```

3. Résultat attendu :

   * plus d’erreur PHP
   * JSON avec :

     ```json
     {
       "dates": [...],
       "series": [
         { "poleName": "Groupe Communication", "values": [...] },
         { "poleName": "IPS Cardiff", "values": [...] }
       ]
     }
     ```

---

## 🔜 Ensuite (et là c’est la récompense)

👉 **ECharts + Stimulus**

* mapping direct du DTO
* aucune transformation JS
* juste du visuel

Mais d’abord : **confirme-moi que le JSON sort**.
On ne dessine pas tant que le moteur ne tourne pas.
