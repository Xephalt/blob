Parfait. L’enthousiasme est noté. Profitons-en avant qu’il ne soit écrasé par Symfony.

👉 **Étape 3 uniquement**
👉 **Controller KPI + route HTTP**
👉 **Objectif unique : taper une URL et voir du JSON propre**
👉 **Toujours zéro ECharts, zéro front**

---

# 🥉 ÉTAPE 3 — Endpoint HTTP (KPI Controller)

## Objectif

Brancher ton **use case applicatif** sur le monde réel, sans le polluer.

À la fin de cette étape, tu pourras appeler une URL du genre :

```
/admin/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-25&weekdays[]=1&weekdays[]=2
```

et voir un JSON exploitable.

---

## 1️⃣ Où ajouter la route

Tu as déjà un **KPI controller existant**.
Typiquement chez toi :

```
src/Controller/Admin/KpiController.php
```

(on ne crée PAS un nouveau controller pour ça, sinon c’est du zèle inutile)

---

## 2️⃣ Méthode à ajouter dans le controller

👉 **Tu ajoutes UNE méthode**, rien d’autre.
👉 Tu n’édites pas les routes existantes.

### Code à copier-coller

```php
<?php

namespace App\Controller\Admin;

use App\Application\Admin\Query\ActiveUsersByPoleOverTimeQuery;
use App\Application\Admin\UseCase\GetActiveUsersByPoleOverTimeHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class KpiController extends AbstractController
{
    #[Route(
        '/admin/kpi/active-users-by-pole',
        name: 'admin_kpi_active_users_by_pole',
        methods: ['GET']
    )]
    public function activeUsersByPoleOverTime(
        Request $request,
        GetActiveUsersByPoleOverTimeHandler $handler
    ): JsonResponse {
        $from = new \DateTimeImmutable($request->query->get('from'));
        $to = new \DateTimeImmutable($request->query->get('to'));

        /** @var int[] $weekdays */
        $weekdays = array_map(
            'intval',
            $request->query->all('weekdays') ?? []
        );

        $query = new ActiveUsersByPoleOverTimeQuery(
            from: $from,
            to: $to,
            weekdays: $weekdays
        );

        $result = $handler($query);

        return $this->json($result);
    }
}
```

---

## 3️⃣ Pourquoi ce controller est “propre”

Regarde bien ce qu’il **ne fait pas** :

* ❌ aucun calcul
* ❌ aucune logique métier
* ❌ aucun accès repository
* ❌ aucun `if` métier

Il fait **strictement** :

* HTTP → Query
* Query → Handler
* Handler → JSON

C’est exactement le rôle d’un controller en Clean Architecture.
Pas plus. Pas moins.

---

## 4️⃣ À quoi doit ressembler le JSON retourné

Quand tout est branché correctement, tu dois voir un JSON du genre :

```json
{
  "dates": [
    "2026-01-02",
    "2026-01-03",
    "2026-01-04"
  ],
  "series": [
    {
      "poleName": "Groupe Communication",
      "values": [42.86, 38.12, 51.03]
    },
    {
      "poleName": "IPS Cardiff",
      "values": [21.45, 19.02, 23.88]
    }
  ]
}
```

👉 **Exactement** ce qu’ECharts attend plus tard
👉 Aucun retraitement JS nécessaire

---

## 5️⃣ Test immédiat (à faire maintenant)

Dans ton navigateur ou via curl :

```
/admin/kpi/active-users-by-pole
    ?from=2026-01-01
    &to=2026-01-25
```

Puis avec weekdays :

```
/admin/kpi/active-users-by-pole
    ?from=2026-01-01
    &to=2026-01-25
    &weekdays[]=1
    &weekdays[]=2
    &weekdays[]=3
```

Si :

* la route répond
* le JSON est cohérent
* aucune exception Doctrine

👉 alors ton **back est sain**.

---

## ✅ Ce que tu fais maintenant

1. Ajouter cette méthode au controller
2. Tester l’URL
3. Vérifier que :

   * les dates sont alignées
   * les séries ont toutes la même longueur
4. Me dire **OK j’ai le JSON**

---

### 🔜 Étape suivante (la fun)

👉 **ECharts côté front (Stimulus)**

* init du chart
* mapping direct du DTO
* toggle des séries par pôle

Mais d’abord : **montre-moi ce JSON**.
Sinon on construit un cockpit sans moteur.
