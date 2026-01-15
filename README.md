Très bien. On remet tout **à plat**, sans improvisation, sans “peut-être”, et **en assumant clairement l’architecture**.

Tu as raison sur un point clé :
👉 **le controller qui gère le form, c’est le Period Picker**, pas la chart, pas les KPI.
Donc **on repart de là**, et je te redonne **LES FICHIERS EXACTS** à avoir maintenant.

Je vais être volontairement sec et structuré, parce que là c’est de l’orchestration, pas du CSS.

---

# 🧠 Architecture finale (simple et saine)

* **period-picker**

  * possède le form (dates + weekdays)
  * construit `params`
  * dispatch **UN event global** `period:change`

* **chart**

  * écoute `period:change`
  * recharge ses charts

* **users-kpi**

  * écoute `period:change`
  * recharge ses KPI

👉 Aucun composant ne dépend d’un autre.
👉 Le dossier `templates/admin/` n’a **AUCUNE importance**.

---

# 1️⃣ `templates/admin/_users_kpi_filter.html.twig`

*(le form, le vrai, celui qui drive tout)*

```twig
<form
    id="users-kpi-filter"
    data-controller="period-picker"
    data-action="change->period-picker#onChange"
>
    <div class="d-flex align-items-center gap-2">

        <label>Période</label>

        <input
            type="date"
            name="date_from"
            class="form-control"
        />

        <span>à</span>

        <input
            type="date"
            name="date_to"
            class="form-control"
        />

        {% include 'admin/_weekday_filter.html.twig' %}

    </div>
</form>
```

⚠️ Important :

* **AUCUNE action chart**
* **AUCUNE action users-kpi**
* Un seul owner : `period-picker`

---

# 2️⃣ `assets/controllers/period_picker_controller.js`

*(le chef d’orchestre, rien d’autre)*

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  onChange() {
    const formData = new FormData(this.element)
    const params = new URLSearchParams(formData).toString()

    document.dispatchEvent(
      new CustomEvent('period:change', {
        detail: { params }
      })
    )
  }
}
```

👉 C’est TOUT ce qu’il fait.
👉 Pas de fetch. Pas de logique métier.
👉 Il **parle**, les autres **écoutent**.

---

# 3️⃣ `templates/admin/metrics/_metrics_card.html.twig`

*(la carte générique)*

```twig
<div
    class="metrics-card"
    data-controller="users-kpi"
    data-users-kpi-url="{{ url }}"
>
    <h3>{{ title }}</h3>

    <div class="metrics">
        {% block metrics %}{% endblock %}
    </div>
</div>
```

---

# 4️⃣ `templates/admin/metrics/_metric.html.twig`

*(UNE metric, générique, scalable)*

```twig
<div class="metric">

    <div class="metric-header">
        <span class="metric-label">{{ label }}</span>
        {% if icon %}
            <span class="metric-icon">{{ icon }}</span>
        {% endif %}
    </div>

    <div
        class="metric-value"
        data-metric-value="{{ key }}"
    >
        —
    </div>

    <div class="metric-evolution">
        <span class="triangle"></span>
        <span
            class="percent"
            data-metric-percent="{{ key }}"
        >
            —
        </span>
    </div>

</div>
```

⚠️ **AUCUN target Stimulus ici**
On ne fait **PAS exploser** `static targets`.

---

# 5️⃣ `assets/controllers/users_kpi_controller.js`

*(celui qui ne marchait pas avant, maintenant il est juste)*

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String
  }

  connect() {
    this.onPeriodChange = this.onPeriodChange.bind(this)
    document.addEventListener('period:change', this.onPeriodChange)
  }

  disconnect() {
    document.removeEventListener('period:change', this.onPeriodChange)
  }

  async onPeriodChange(event) {
    const { params } = event.detail
    await this.load(params)
  }

  async load(params = '') {
    const response = await fetch(this.urlValue + '?' + params)
    const data = await response.json()

    this.renderMetric('registeredUsers', data.registeredUsers)
    this.renderMetric('activeUsers', data.activeUsers)
  }

  renderMetric(key, metric) {
    const valueEl = this.element.querySelector(
      `[data-metric-value="${key}"]`
    )
    const percentEl = this.element.querySelector(
      `[data-metric-percent="${key}"]`
    )

    if (!valueEl || !percentEl) return

    valueEl.textContent = metric.count

    percentEl.textContent =
      (metric.evolutionPercent > 0 ? '+' : '') +
      metric.evolutionPercent.toFixed(1) +
      '%'
  }
}
```

👉 Si ça ne marche pas avec ça, **le JSON n’est pas ce que tu crois**.
Et là on le debug en 30 secondes avec un `console.log(data)`.

---

# 6️⃣ Dans ton `index.html.twig`

```twig
{% include 'admin/_users_kpi_filter.html.twig' %}

{% include 'admin/metrics/_metrics_card_users.html.twig' %}
```

---

# 🧾 Ce que tu dois vérifier maintenant (factuel)

1. Dans la console :

   * `[period-picker]` → event déclenché ?
   * `[users-kpi]` → fetch exécuté ?
2. Dans l’HTML rendu :

   * `data-controller="users-kpi"` présent ?
   * `data-users-kpi-url="..."` présent ?
3. Dans le JSON :

   * `registeredUsers.count`
   * `registeredUsers.evolutionPercent`

---

## 🧠 Conclusion nette

* Tu n’avais **rien cassé côté backend**
* Tu n’avais **pas un problème Twig**
* Tu avais **un problème d’orchestration**

Maintenant :

* un seul point d’entrée
* un bus d’event clair
* des composants passifs
* scalable à l’infini

On est enfin sur des rails.
