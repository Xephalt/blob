Très bien. On arrête les envolées architecturales, on sort le tournevis, et on fait **un truc simple qui marche maintenant**.
Objectif clair, périmètre clair, zéro magie.

---

# 🎯 Objectif

Construire **vite** :

* une UI **date picker + weekdays**
* un **controller Stimulus**
* qui déclenche une requête **exactement** de la forme :

```
/kpi/users?date_from=2026-01-01&date_to=2026-01-15&weekdays=1,2
```

Pas de period DTO, pas de dispatch global, pas de refacto chart.
Juste **UI → URL → fetch**. Point.

---

# 1️⃣ Twig – UI minimale (copier-coller)

### `templates/admin/_users_kpi_filters.html.twig`

```twig
<div
    class="kpi-filters"
    data-controller="kpi-filters"
    data-kpi-filters-url="{{ path('admin_kpi_users') }}"
>

    <div class="d-flex align-items-center gap-2">

        <label>Période</label>

        <input
            type="date"
            data-kpi-filters-target="fromDate"
            class="form-control"
        />

        <span>→</span>

        <input
            type="date"
            data-kpi-filters-target="toDate"
            class="form-control"
        />
    </div>

    <div class="d-flex gap-2 mt-2">
        {% for i, day in {
            1:'Lun',2:'Mar',3:'Mer',4:'Jeu',5:'Ven',6:'Sam',7:'Dim'
        } %}
            <label class="form-check">
                <input
                    type="checkbox"
                    class="form-check-input"
                    value="{{ i }}"
                    data-kpi-filters-target="weekday"
                    checked
                />
                {{ day }}
            </label>
        {% endfor %}
    </div>

</div>
```

👉 UI volontairement brute
👉 Pas de CSS sophistiqué
👉 Tout est **adressable en JS**

---

# 2️⃣ Stimulus – Controller simple et lisible

### `assets/controllers/kpi_filters_controller.js`

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['fromDate', 'toDate', 'weekday']

  connect() {
    // valeurs par défaut simples
    const today = new Date().toISOString().slice(0, 10)
    this.toDateTarget.value = today

    const from = new Date()
    from.setDate(from.getDate() - 14)
    this.fromDateTarget.value = from.toISOString().slice(0, 10)

    this.fetch()
  }

  fetch() {
    const from = this.fromDateTarget.value
    const to = this.toDateTarget.value

    const weekdays = this.weekdayTargets
      .filter(cb => cb.checked)
      .map(cb => cb.value)
      .join(',')

    const url =
      `${this.data.get('url')}` +
      `?date_from=${from}` +
      `&date_to=${to}` +
      `&weekdays=${weekdays}`

    console.log('[KPI USERS]', url)

    fetch(url)
      .then(r => r.json())
      .then(data => {
        // POUR L’INSTANT on log
        // tu brancheras l’UI ensuite
        console.log('[KPI DATA]', data)
      })
  }

  // appelé automatiquement quand un input change
  fromDateTargetConnected() {
    this.fromDateTarget.addEventListener('change', () => this.fetch())
  }

  toDateTargetConnected() {
    this.toDateTarget.addEventListener('change', () => this.fetch())
  }

  weekdayTargetConnected(target) {
    target.addEventListener('change', () => this.fetch())
  }
}
```

👉 volontairement **verbeux**
👉 aucune abstraction prématurée
👉 facile à debugger
👉 URL visible dans la console

---

# 3️⃣ Utilisation dans ta page

Dans ton `index.html.twig` :

```twig
{% include 'admin/_users_kpi_filters.html.twig' %}

{% include 'metrics/_metrics_card_users.html.twig' %}
```

Rien d’autre.

---

# 4️⃣ Ce que tu obtiens immédiatement

* ✅ Un vrai date picker
* ✅ Un filtre weekdays simple
* ✅ Une URL correcte
* ✅ Un fetch fonctionnel
* ✅ Aucun impact chart
* ✅ Aucune dépendance cachée

Tu peux maintenant :

* vérifier l’URL
* vérifier le backend
* brancher l’UI des metrics tranquillement

---

# 5️⃣ Ce qu’on **ne fait PAS** maintenant (et c’est volontaire)

* ❌ pas de period picker global
* ❌ pas d’event bus
* ❌ pas de DTO front
* ❌ pas de refacto chart
* ❌ pas de clean archi prématurée

On fait marcher le produit **avant** de faire joli.
C’est comme ça qu’on évite de “bien architecturer un truc cassé”.

---

Quand tu verras **les chiffres bouger** avec ce filtre,
on passera à l’étape suivante proprement.

