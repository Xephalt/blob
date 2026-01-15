Bon. On va faire **simple, propre, et surtout cohérent avec ton visuel**.
Pas d’architecture cosmique, pas de magie noire. Juste **une Metrics Card Users** qui :

* a **le bon HTML / style**
* dépend **uniquement** d’un endpoint `/kpi/users`
* sait afficher **value + évolution**
* est **facilement clonable** pour “Messages”, “Likes”, etc.

Je te donne **exactement ce qu’il faut**, au format **copier-coller**.

---

## 1️⃣ Dépendances réelles (claires, limitées)

### La metrics card **dépend de** :

* ✅ un endpoint JSON `/kpi/users`
* ✅ un controller Stimulus `metrics-card`
* ❌ PAS du chart
* ❌ PAS du date picker directement
* ❌ PAS d’un DTO frontend compliqué

Elle **reçoit juste des params** (date_from, date_to, weekdays) via l’URL.

---

## 2️⃣ `_metrics_card_users.html.twig`

👉 **Ce fichier est spécifique Users**, mais basé sur une card générique.

```twig
{# templates/metrics/_metrics_card_users.html.twig #}

<div
    class="metrics-card"
    data-controller="metrics-card"
    data-metrics-card-url="{{ path('admin_kpi_users') }}"
>
    <div class="metrics-card-header">
        <h3>UTILISATEURS</h3>
    </div>

    <div class="metrics-card-body metrics-grid">

        {% include 'metrics/_metric.html.twig' with {
            label: 'Enregistrés',
            key: 'registeredUsers',
            color: 'gold'
        } %}

        {% include 'metrics/_metric.html.twig' with {
            label: 'Actifs',
            key: 'activeUsers',
            color: 'green',
            icon: '★'
        } %}

    </div>
</div>
```

---

## 3️⃣ `_metric.html.twig` (atomique, réutilisable partout)

```twig
{# templates/metrics/_metric.html.twig #}

<div
    class="metric"
    data-metrics-card-target="metric"
    data-metric-key="{{ key }}"
>
    <div class="metric-header">
        <span class="metric-label">
            {{ label }}
        </span>

        {% if icon is defined %}
            <span class="metric-icon">{{ icon }}</span>
        {% endif %}
    </div>

    <div class="metric-value metric-value--{{ color|default('default') }}">
        —
    </div>

    <div class="metric-evolution metric-evolution--neutral">
        <span class="triangle"></span>
        <span class="percent">—</span>
    </div>
</div>
```

---

## 4️⃣ CSS minimal (pour matcher ton screenshot)

👉 **Juste ce qu’il faut**, pas un framework.

```css
.metrics-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 6px 20px rgba(0,0,0,.08);
}

.metrics-card-header h3 {
  margin: 0 0 16px;
  font-weight: 700;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 32px;
}

.metric-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
}

.metric-value {
  font-size: 42px;
  font-weight: 700;
  margin: 8px 0;
}

.metric-value--gold {
  color: #d4b000;
}

.metric-value--green {
  color: #0f5c50;
}

.metric-evolution {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
}

.metric-evolution--positive {
  color: #2e7d32;
}

.metric-evolution--negative {
  color: #c62828;
}

.triangle {
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-bottom: 10px solid currentColor;
}
```

---

## 5️⃣ Ce que le backend doit renvoyer (contrat clair)

Ton endpoint `/kpi/users` doit renvoyer **exactement ça** (exemple) :

```json
{
  "registeredUsers": {
    "value": 235,
    "evolutionPercent": 20.1,
    "trend": "up"
  },
  "activeUsers": {
    "value": 180,
    "evolutionPercent": 20.1,
    "trend": "up"
  }
}
```

➡️ Rien d’autre n’est requis pour afficher la card.

---

## 6️⃣ Ce que cette card **ne fait PAS** (volontairement)

* ❌ elle ne connaît pas le date picker
* ❌ elle ne connaît pas le chart
* ❌ elle ne stocke pas d’état global
* ❌ elle ne sait pas “pourquoi” les chiffres changent

Elle **affiche**. Point.

---

## 7️⃣ Pourquoi c’est la bonne base (et pas du bullshit)

* Tu peux créer :

  * `_metrics_card_messages.html.twig`
  * `_metrics_card_revenue.html.twig`
  * `_metrics_card_usage.html.twig`
* **sans toucher au JS**
* **sans toucher au CSS**
* **sans toucher au chart**

Tu voulais une base **qui scale sans devenir un enfer**.
👉 Celle-ci le fait.

---

Si tu veux, prochain pas logique :

* je te donne **le metrics-card Stimulus controller exact**
* ou on branche **le date picker dessus proprement**
* ou on aligne le backend DTO avec ce contrat

Mais là, **l’UI est enfin saine**.
