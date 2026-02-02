Parfait. On va faire ça **proprement, lentement, et sans magie**.
Tu veux avancer **fichier par fichier**, tu valides, puis on continue. C’est exactement comme ça qu’on évite les usines à gaz.

👉 **Étape 1 uniquement dans ce message**
👉 **Repository seulement**
👉 **Aucune Clean Arch autour pour l’instant**
👉 **Aucune route, aucun DTO, aucun ECharts**

---

# 🥇 ÉTAPE 1 — MessageRepository

## Objectif

Ajouter **UNE** méthode qui permet de répondre à la question suivante :

> Pour chaque jour et pour chaque pôle,
> combien d’utilisateurs distincts ont envoyé au moins un message ?

Sans pourcentage. Sans nom de pôle. Juste des faits.

---

## 📍 Où intervenir exactement

**Fichier existant** (chez toi) :

```
src/Repository/MessageRepository.php
```

On **n’en modifie aucune méthode existante**.
On **ajoute une nouvelle méthode**, point.

---

## 🧠 Contrat de la méthode (important)

Signature claire, sans ambiguïté :

```php
public function countActiveUsersPerDayByPole(
    ?\DateTimeImmutable $from = null,
    ?\DateTimeImmutable $to = null,
    array $weekdays = []
): array
```

Pourquoi :

* `from / to` optionnels → cohérent avec ton existant
* `weekdays` optionnel → filtre métier mais **technique côté repo**
* retour `array` → brut, exploité plus tard par le use case

---

## 📦 Format de retour attendu

La méthode retournera **une liste plate** (Doctrine style), par exemple :

```php
[
  [
    'day' => '2026-01-02',
    'pole_id' => 5,
    'active_users' => 42,
  ],
  [
    'day' => '2026-01-02',
    'pole_id' => 2,
    'active_users' => 17,
  ],
]
```

👉 Pas de regroupement ici
👉 Pas de transformation
👉 Le handler s’en chargera plus tard

---

## 🧩 Implémentation complète à ajouter

👉 **À copier-coller telle quelle** dans `MessageRepository.php`

```php
public function countActiveUsersPerDayByPole(
    ?\DateTimeImmutable $from = null,
    ?\DateTimeImmutable $to = null,
    array $weekdays = []
): array {
    $qb = $this->createQueryBuilder('m');

    $qb
        ->select(
            'DATE(m.createdAt) AS day',
            'p.id AS pole_id',
            'COUNT(DISTINCT u.id) AS active_users'
        )
        ->join('m.conversation', 'c')
        ->join('c.user', 'u')
        ->join('u.pole', 'p')
        ->groupBy('day')
        ->addGroupBy('p.id')
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
        // Doctrine: 1 = Sunday, 7 = Saturday (MySQL)
        $qb
            ->andWhere('DAYOFWEEK(m.createdAt) IN (:weekdays)')
            ->setParameter('weekdays', $weekdays);
    }

    return $qb->getQuery()->getArrayResult();
}
```

---

## ⚠️ Points importants (à lire avant de dire OK)

* ✔️ On **utilise Message comme source de vérité**
* ✔️ Un user est actif **s’il a au moins un message**
* ✔️ Le `DISTINCT u.id` empêche le multi-message
* ✔️ Le repository **ne calcule aucun pourcentage**
* ✔️ Le repository **ne connaît pas les noms de pôles**
* ✔️ Aucune logique métier haut niveau ici

Si plus tard :

* tu changes la définition d’“actif”
* tu ajoutes un autre axe (jour ouvré, heure, etc.)

👉 ce sera **un autre use case**, pas ici.

---

## ✅ Ce que tu dois faire maintenant

1. Ajouter **exactement** cette méthode
2. Vérifier :

   * que `u.pole` existe bien dans ton mapping Doctrine
   * que `DAYOFWEEK` correspond à ce que ton front envoie
3. Me dire **OK** quand c’est fait

---

Ensuite, **étape 2** :
👉 on crée **la Query + le Handler** qui consomment cette méthode
👉 là seulement on commence la vraie Clean Architecture

Un pas à la fois. Sinon on retombe dans le bricolage.
