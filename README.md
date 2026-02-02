Tu viens de tomber sur un classique Doctrine : **DQL ne connaît pas `DAYOFWEEK()`** (et parfois même pas `DATE()`), donc il te sort son “expected known function…”. Symfony n’est pas méchant, juste littéral.

## ✅ Correction la plus simple et la plus rapide

En DQL, quand tu veux utiliser une fonction SQL “native”, tu passes par :

```php
FUNCTION('NOM_DE_LA_FONCTION_SQL', ...)
```

Donc dans ta méthode repository, tu remplaces :

* `DAYOFWEEK(m.createdAt)` → `FUNCTION('DAYOFWEEK', m.createdAt)`
* et tant qu’à faire (sinon tu vas te prendre la suivante), `DATE(m.createdAt)` → `FUNCTION('DATE', m.createdAt)`

### ✅ Méthode repository corrigée

Copie-colle cette version :

```php
public function countActiveUsersPerDayByPole(
    ?\DateTimeImmutable $from = null,
    ?\DateTimeImmutable $to = null,
    array $weekdays = []
): array {
    $qb = $this->createQueryBuilder('m');

    $qb
        ->select(
            "FUNCTION('DATE', m.createdAt) AS day",
            'u.pole AS pole',
            'COUNT(DISTINCT u.id) AS active_users'
        )
        ->join('m.conversation', 'c')
        ->join('c.user', 'u')
        ->where('u.pole IS NOT NULL')
        ->groupBy('day')
        ->addGroupBy('u.pole')
        ->orderBy('day', 'ASC');

    if ($from !== null) {
        $qb
            ->andWhere('m.createdAt >= :from')
            ->setParameter('from', $from);
    }

    if ($to !== null) {
        $qb
            ->andWhere('m.createdAt <= :to')
            ->setParameter('to', $to);
    }

    if (!empty($weekdays)) {
        $qb
            ->andWhere("FUNCTION('DAYOFWEEK', m.createdAt) IN (:weekdays)")
            ->setParameter('weekdays', $weekdays);
    }

    return $qb->getQuery()->getArrayResult();
}
```

---

## ⚠️ Attention : tes weekdays risquent d’être faux (et c’est sournois)

* PHP `format('N')` = **1 (lundi)** … **7 (dimanche)**
* MySQL `DAYOFWEEK()` = **1 (dimanche)** … **7 (samedi)**

Donc si ton front envoie `weekdays[]=1` pour “lundi”, MySQL va filtrer “dimanche”. Ambiance.

### ✅ Patch minimal côté Controller (ou là où tu construis la Query)

Juste après avoir récupéré `weekdays`, convertis-les au format MySQL :

```php
$weekdays = array_map('intval', $request->query->all('weekdays') ?? []);

// PHP: 1=Mon..7=Sun  →  MySQL DAYOFWEEK: 1=Sun..7=Sat
$weekdays = array_map(
    static fn (int $n): int => ($n % 7) + 1,
    $weekdays
);
```

Mapping :

* lundi(1) → 2
* mardi(2) → 3
* …
* samedi(6) → 7
* dimanche(7) → 1

---

## Y a-t-il d’autres changements à cause du passage à `user.pole` string ?

Oui, mais **pas tout de suite**.

Ce changement impacte surtout **le Handler** (indexation par `poleName` au lieu de `poleId`, et le dénominateur `countUsersByPoleLabel()` au lieu de `countUsersByPole()`).

Mais tant que ton objectif est “**voir le JSON s’afficher**”, on ne touche pas à ça tant que :

1. la route répond,
2. la requête sort des lignes.

Fais d’abord passer cette requête. Ensuite on ajuste proprement le Handler.

---

### Ce que tu fais maintenant

1. Remplace la méthode repository par la version `FUNCTION(...)` ci-dessus
2. Ajoute la conversion weekday côté controller (si tu utilises ce filtre)
3. Reteste l’URL

Si ça pète encore, copie l’erreur exacte (la prochaine sera probablement sur `DATE()` si tu n’avais pas corrigé, mais là on l’a déjà blindée).
