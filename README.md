Tu as raison sur un point essentiel : **si ton contrat d’entrée est “1 = lundi”**, alors on arrête de jouer aux devinettes avec des mappings MySQL/JS/PHP. On prend **ISO (1..7, lundi..dimanche)** partout côté backend, point.

Et oui : si tu envoies `weekdays=1,2` (CSV), Symfony te donne une **string**, alors que `weekdays[]=1&weekdays[]=2` donne un **array**. Donc si ton handler “expect array” et qu’il reçoit une string, il râle. Normal.

On corrige ça **proprement** :

* **le handler doit normaliser** `weekdays` qu’il reçoive un array ou une string CSV
* le filtrage doit comparer avec `DateTimeImmutable::format('N')` (ISO) directement
* `1` doit sélectionner **lundi**, pas mercredi, pas une autre dimension mystique

---

# ✅ Patch minimal à faire

## 1) Handler: normaliser weekdays (array OU CSV string)

📍 `src/Application/Admin/UseCase/GetActiveUsersByPoleOverTimeHandler.php`

Ajoute cette fonction privée dans la classe (en bas du fichier par exemple) :

```php
/**
 * @return int[] ISO weekdays: 1=Mon ... 7=Sun
 */
private function normalizeIsoWeekdays(mixed $weekdays): array
{
    // Accept: [] | [1,2] | ["1","2"] | "1,2" | "1"
    if ($weekdays === null || $weekdays === '' || $weekdays === []) {
        return [];
    }

    $list = [];

    if (is_string($weekdays)) {
        $list = array_filter(array_map('trim', explode(',', $weekdays)), static fn($v) => $v !== '');
    } elseif (is_array($weekdays)) {
        $list = $weekdays;
    } else {
        // Unexpected type (object/int/etc.) => ignore
        return [];
    }

    $ints = array_map('intval', $list);

    // Keep only 1..7, unique, sorted (stable output)
    $ints = array_values(array_unique(array_filter(
        $ints,
        static fn (int $n) => $n >= 1 && $n <= 7
    )));
    sort($ints);

    return $ints;
}
```

Ensuite, dans `__invoke()`, **tout en haut**, normalise :

```php
$isoWeekdays = $this->normalizeIsoWeekdays($query->weekdays);
```

Puis remplace ta génération de dates par une version ISO simple :

```php
$dates = [];
$cursor = $query->from;

while ($cursor <= $query->to) {
    // ISO: 1=Mon ... 7=Sun
    $weekdayIso = (int) $cursor->format('N');

    if (empty($isoWeekdays) || in_array($weekdayIso, $isoWeekdays, true)) {
        $dates[] = $cursor->format('Y-m-d');
    }

    $cursor = $cursor->modify('+1 day');
}
```

✅ Là, `weekdays=1` => **lundi**.
✅ `weekdays=1,2` => **lundi + mardi**.
✅ `weekdays[]=1&weekdays[]=2` marche pareil.

---

## 2) Controller: accepter CSV + array sans broncher

Même si tu insistes pour “le handler doit s’adapter”, je te donne quand même le patch controller parce que c’est le bon endroit pour gérer les formats HTTP. Et ça ne casse rien.

📍 `src/Controller/Admin/KpiController.php` (dans ta méthode de route KPI)

Remplace ta lecture de weekdays par ça :

```php
$rawWeekdays = $request->query->all('weekdays');

if ($rawWeekdays === []) {
    // Support CSV: ?weekdays=1,2
    $rawWeekdays = $request->query->get('weekdays', []);
}

$weekdays = is_array($rawWeekdays)
    ? array_map('intval', $rawWeekdays)
    : array_map('intval', array_filter(array_map('trim', explode(',', (string) $rawWeekdays))));
```

Et tu passes `$weekdays` tel quel dans la Query.

---

# Pourquoi tu voyais “mercredi” quand tu envoyais 1

Parce qu’à un moment, tu avais un mélange de conventions (0-based, ISO, MySQL `DAYOFWEEK`) et tu comparais des valeurs qui ne parlent pas le même langage. Résultat : `1` (lundi) finissait interprété comme autre chose. Maintenant on force **ISO (N)**, donc `1` est **toujours lundi**.

---

# Ce que tu testes maintenant

* CSV :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31&weekdays=1
```

Tu dois voir **uniquement les lundis** dans `dates`.

* Array :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31&weekdays[]=1&weekdays[]=2
```

Tu dois voir **lundis + mardis**.

Si après ça tu vois encore des mercredis, ce ne sera plus “un mapping”, ce sera un bug de construction des dates (et on le démonte, mais là on vient de verrouiller la convention ISO proprement).
