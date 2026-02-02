Parfait, tu viens d’atteindre **le vrai mur Doctrine**, pas une erreur de logique. Et la bonne nouvelle, c’est que tu n’as rien “mal fait” conceptuellement.
La mauvaise, c’est que **Doctrine DQL n’accepte pas `FUNCTION()` chez toi**.

On va régler ça **proprement**, en comprenant **pourquoi**, puis je te donne **la correction minimale**.

---

# 🧨 Le problème exact (et pourquoi il arrive)

Erreur :

> `Syntax Error: Expected known function, got FUNCTION`

Ça veut dire **une seule chose** :

👉 **Ton Doctrine n’a PAS activé les fonctions SQL custom via `FUNCTION()`**

Selon la version / config :

* `FUNCTION()` **n’est pas toujours disponible**
* ou nécessite l’enregistrement explicite des fonctions SQL
* ou est désactivé par défaut (très courant)

Donc Doctrine voit littéralement :

```sql
FUNCTION('DATE', m.createdAt)
```

et répond :

> “Je ne sais pas ce que c’est, dégage.”

---

# ✅ Solution pragmatique et sûre (celle que je te recommande)

👉 **On enlève TOUTES les fonctions SQL du DQL**
👉 **On fait le groupement côté PHP**, pas côté SQL
👉 **La requête redevient 100 % DQL-compatible**

C’est :

* plus robuste
* plus portable
* largement assez performant pour un KPI

---

# 🔧 Correction IMMÉDIATE du MessageRepository

### ❌ Ce qu’on SUPPRIME

* `DATE(...)`
* `DAYOFWEEK(...)`
* `FUNCTION(...)`

### ✅ Ce qu’on fait à la place

* on récupère les messages avec `createdAt`
* on groupe **par jour en PHP**

---

## 🧩 Nouvelle méthode repository (fonctionnelle à coup sûr)

👉 **Remplace entièrement ta méthode par celle-ci** :

```php
public function countActiveUsersPerDayByPole(
    ?\DateTimeImmutable $from = null,
    ?\DateTimeImmutable $to = null
): array {
    $qb = $this->createQueryBuilder('m');

    $qb
        ->select(
            'm.createdAt AS createdAt',
            'u.pole AS pole',
            'u.id AS user_id'
        )
        ->join('m.conversation', 'c')
        ->join('c.user', 'u')
        ->where('u.pole IS NOT NULL');

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

    $rows = $qb->getQuery()->getArrayResult();

    /**
     * Format final :
     * [day][pole] = array of unique user ids
     */
    $grouped = [];

    foreach ($rows as $row) {
        /** @var \DateTimeInterface $dt */
        $dt = $row['createdAt'];
        $day = $dt->format('Y-m-d');
        $pole = $row['pole'];
        $userId = $row['user_id'];

        $grouped[$day][$pole][$userId] = true;
    }

    // Conversion en counts
    $result = [];
    foreach ($grouped as $day => $poles) {
        foreach ($poles as $pole => $users) {
            $result[] = [
                'day' => $day,
                'pole' => $pole,
                'active_users' => count($users),
            ];
        }
    }

    return $result;
}
```

---

# 🧠 Pourquoi cette solution est la BONNE ici

* ✔️ **Zéro fonction SQL exotique**
* ✔️ 100 % DQL standard
* ✔️ Aucun mapping Doctrine requis
* ✔️ Le filtrage weekday pourra se faire **dans le handler** (plus propre)
* ✔️ La logique reste localisée (repo = données brutes)

Oui, on fait un peu plus de travail en PHP.
Non, ce n’est pas un problème pour un KPI.

---

# 🔄 Conséquence sur la suite (important)

👉 On **RETIRE `weekdays` du repository**
👉 Le filtrage par jour de semaine se fera :

* soit dans le handler
* soit avant l’appel au handler

C’est même **plus Clean** :

* le repo ne connaît que des dates
* le métier filtre les jours

---

# ✅ Ce que tu fais maintenant

1. Remplace la méthode du repository par celle-ci
2. Recharge :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-25
```

3. Vérifie :

   * plus d’erreur DQL
   * JSON qui sort (même brut)

---

Ensuite, **étape suivante** (et là on revient au fun) :
👉 adapter le **handler** pour :

* filtrer les weekdays
* calculer les %
* préparer le DTO final

Mais d’abord : **fais tomber cette erreur Doctrine**.
