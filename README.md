Très bien. On arrête de philosopher, on met **les deux controllers propres, complets, cohérents**, et surtout **qui marchent ensemble**.
Lis-les une fois, copie-colle, puis on débogue seulement si la réalité ose encore résister.

---

# ✅ `period_picker_controller.js`

👉 Rôle unique :

* lire **from / to / weekdays**
* dispatcher **un event DOM qui bubble**
* **aucune logique métier**, aucun fetch

```js
// assets/controllers/period_picker_controller.js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['from', 'to']
  static values = {
    weekdays: String // "1,2,3"
  }

  connect() {
    this.emit()
  }

  onChange() {
    this.emit()
  }

  emit() {
    const payload = {
      from: this.fromTarget?.value || null,
      to: this.toTarget?.value || null,
      weekdays: this.weekdaysValue || null
    }

    console.debug('[period-picker] emit', payload)

    this.dispatch('change', {
      detail: payload,
      bubbles: true
    })
  }
}
```

---

# ✅ `users_kpi_controller.js`

👉 Rôle unique :

* écouter `period:change`
* construire l’URL
* fetcher
* remplir les metrics

Aucune dépendance à la chart. Aucun couplage foireux.

```js
// assets/controllers/users_kpi_controller.js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String
  }

  static targets = ['metric']

  connect() {
    this.element.addEventListener('period:change', this.onPeriodChange)
  }

  disconnect() {
    this.element.removeEventListener('period:change', this.onPeriodChange)
  }

  onPeriodChange = (event) => {
    console.debug('[users-kpi] period change received', event.detail)
    this.load(event.detail)
  }

  async load({ from, to, weekdays }) {
    if (!this.urlValue) {
      console.warn('[users-kpi] missing url')
      return
    }

    const params = new URLSearchParams()

    if (from) params.set('date_from', from)
    if (to) params.set('date_to', to)
    if (weekdays) params.set('weekdays', weekdays)

    const url = `${this.urlValue}?${params.toString()}`

    console.debug('[users-kpi] fetch', url)

    const response = await fetch(url)
    const data = await response.json()

    this.render(data)
  }

  render(data) {
    this.metricTargets.forEach((el) => {
      const key = el.dataset.key
      const metric = data[key]

      if (!metric) return

      el.querySelector('.metric-value').textContent = metric.count

      const evolution = el.querySelector('.metric-evolution')
      const percent = el.querySelector('.percent')

      percent.textContent = `${metric.evolutionPercent}%`

      evolution.classList.toggle('metric-evolution--up', metric.trend === 'up')
      evolution.classList.toggle('metric-evolution--down', metric.trend === 'down')
      evolution.classList.toggle('metric-evolution--neutral', metric.trend === 'neutral')
    })
  }
}
```

---

# 🔴 Conditions NON négociables pour que ça marche

1. **DOM parent commun**

```html
<div data-controller="users-kpi">
  <form data-controller="period-picker">...</form>
  <div class="metrics-card">...</div>
</div>
```

2. `bubbles: true` → déjà fait
3. `data-users-kpi-url-value="/kpi/users"` bien présent
4. Chaque metric :

```html
<div data-users-kpi-target="metric" data-key="registeredUsers">
```

---

# 🧠 Résumé brutal

* period-picker **parle**
* users-kpi **écoute**
* ils vivent **dans le même arbre DOM**
* aucune magie
* aucune dépendance cachée
* architecture scalable

Si après ça **rien ne se met à jour**, alors on regarde **le JSON exact** que `/kpi/users` renvoie, et on le corrige sans pitié.

Très bien. Maintenant je vais être **chirurgical**, parce que là ton problème n’est **plus le JS**, c’est **l’assemblage**. Et oui, il y a **quelques fichiers à ajuster**, mais **peu**, et **tous ont une raison précise**.

Je te fais la **liste exhaustive**, avec **quoi**, **pourquoi**, **ce que tu mets**, et **ce que tu ne touches plus après**.

---

# 🧱 Vue d’ensemble (ce qu’on construit)

```
admin/index.html.twig
└── users-kpi (Stimulus)
    ├── period-picker (Stimulus)
    │   ├── date from
    │   ├── date to
    │   └── weekday picker
    └── metrics-card
        └── metrics (N)
```

👉 **UN seul flux** :
`period-picker → event → users-kpi → fetch → render`

---

# ✅ FICHIERS À MODIFIER / CRÉER

Je te les donne **dans l’ordre logique**.

---

## 1️⃣ `admin/index.html.twig` ✅ (léger ajustement)

### 🎯 Pourquoi

C’est **le point d’ancrage DOM commun**.
Si les controllers ne partagent pas un parent, **rien ne remonte**.

### ✅ Ce que tu dois avoir

```twig
<div
    data-controller="users-kpi"
    data-users-kpi-url-value="{{ path('admin_kpi_users') }}"
>
    {% include 'admin/_users_kpi_filter.html.twig' %}
    {% include 'metrics/_metrics_card_users.html.twig' %}
</div>
```

❌ **Tu enlèves** :

* `data-controller="users-kpi"` ailleurs
* toute logique JS ici

---

## 2️⃣ `admin/_users_kpi_filter.html.twig` ✅ (obligatoire)

### 🎯 Pourquoi

C’est **le seul endroit** où vit le `period-picker`.

### ✅ Contenu minimal viable

```twig
<form
    data-controller="period-picker"
    data-action="change->period-picker#onChange"
>
    <input
        type="date"
        data-period-picker-target="from"
    />

    <input
        type="date"
        data-period-picker-target="to"
    />

    {% include 'admin/weekday_filter.html.twig' %}
</form>
```

⚠️ Important :

* **pas de fetch**
* **pas d’URL**
* **pas de logique métier**

---

## 3️⃣ `admin/weekday_filter.html.twig` ⚠️ (petit ajustement)

### 🎯 Pourquoi

Il doit **mettre à jour `weekdaysValue`**, pas appeler un backend.

### Exemple simple

```twig
<div
    data-controller="weekday"
    data-action="weekday:change->period-picker#onChange"
>
    {# boutons Lu Ma Me etc #}
</div>
```

Et dans ton `weekday_controller.js`, tu dois faire :

```js
this.dispatch('change', {
  detail: { weekdays: this.selected.join(',') },
  bubbles: true
})
```

---

## 4️⃣ `metrics/_metrics_card_users.html.twig` ✅

### 🎯 Pourquoi

C’est **une carte spécialisée**, mais **structure générique**.

### Ce que tu as est bon 👌

Je rappelle juste la version correcte :

```twig
{% embed 'metrics/_metrics_card.html.twig' with {
    title: 'UTILISATEURS'
} %}
    {% block metrics %}
        {% include 'metrics/_metric.html.twig' with {
            label: 'Enregistrés',
            key: 'registeredUsers',
            icon: null
        } %}

        {% include 'metrics/_metric.html.twig' with {
            label: 'Actifs',
            key: 'activeUsers',
            icon: '★'
        } %}
    {% endblock %}
{% endembed %}
```

---

## 5️⃣ `metrics/_metrics_card.html.twig` ✅

### 🎯 Pourquoi

C’est **le container logique** des métriques.

### Obligatoire

```twig
<div class="metrics-card">
    <h3>{{ title }}</h3>

    <div class="metrics">
        {% block metrics %}{% endblock %}
    </div>
</div>
```

⚠️ Aucun `data-controller` ici.

---

## 6️⃣ `metrics/_metric.html.twig` 🚨 CRITIQUE

### 🎯 Pourquoi

Si ça ne match pas le controller → **rien ne s’affiche**.

### VERSION CORRECTE

```twig
<div
    class="metric"
    data-users-kpi-target="metric"
    data-key="{{ key }}"
>
    <div class="metric-header">
        <span>{{ label }}</span>
        {% if icon %}<span>{{ icon }}</span>{% endif %}
    </div>

    <div class="metric-value">—</div>

    <div class="metric-evolution metric-evolution--neutral">
        <span class="triangle"></span>
        <span class="percent">—</span>
    </div>
</div>
```

❌ Erreur fréquente :

* `data-target="users-kpi.metric"` ❌
* `data-users-kpi-target="{{ key }}"` ❌

---

# 🧪 Checklist DEBUG (à faire dans la console)

1. Tu changes une date
   → log `[period-picker] emit`

2. Tu vois
   → `[users-kpi] period change received`

3. Tu vois
   → `[users-kpi] fetch /kpi/users?...`

4. Le JSON contient :

```json
{
  "registeredUsers": {
    "count": 235,
    "trend": "up",
    "evolutionPercent": 20.1
  }
}
```

Si un seul maillon manque → **ça ne marchera pas**.

---

# 🧠 Conclusion honnête

* Tu n’as **pas trop de fichiers**
* Tu avais juste **un arbre DOM cassé**
* L’architecture est **bonne**
* Maintenant elle est **cohérente**

Prochaine étape (quand tu veux) :
👉 brancher **charts + metrics** sur **le même event**, proprement, sans spaghetti.

Mais d’abord : fais marcher **CE pipeline**.
