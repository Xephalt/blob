Bon. On va arrêter de bricoler dans le noir et remettre un minimum de **discipline mentale** dans tout ça, sinon tu vas continuer à courir après des events fantômes toute la nuit.

Je vais faire trois choses, dans l’ordre logique (pas l’ordre “Stimulus roulette”) :

1. **Expliquer précisément pourquoi ça casse aujourd’hui**
2. **Te donner les DEUX controllers Stimulus corrects, complets**
3. **Lister exactement les fichiers Twig à modifier et comment les brancher**
4. **Corriger le bug UX du date picker qui bloque la page**

Aucun bullshit, que du copiable.

---

## 1️⃣ Pourquoi ça ne marche PAS aujourd’hui (la vraie raison)

### ❌ Erreur clé

```
Error invoking action "change->period-picker#onChange"
references undefined method "onChange"
```

👉 Ça veut dire **une seule chose** :
**Ton controller `period-picker` n’a PAS de méthode `onChange()` exposée**, alors que ton HTML la référence.

Stimulus n’invente rien.
Si le HTML dit `period-picker#onChange`, **la méthode doit exister**.

---

### ❌ Deuxième problème silencieux

Tu dispatches des events… mais **personne ne les écoute correctement**.

* Le chart écoute encore son propre `onPeriodChange`
* Le KPI n’écoute rien du tout (ou écoute un event jamais dispatché)

👉 Il faut **UN event métier clair** :

```
period:change
```

Et **tout le monde s’aligne dessus**.

---

## 2️⃣ Architecture SIMPLE (et saine)

### Principe

* **PeriodPicker = source unique de vérité**

* Il :

  * lit `from_date`, `to_date`, `weekdays`
  * construit une query string
  * dispatch **un CustomEvent**

* Les consommateurs :

  * `chart`
  * `users-kpi`

👉 **ZÉRO dépendance directe entre eux**

---

## 3️⃣ Controller `period_picker_controller.js` (FINAL)

📁 `assets/controllers/period_picker_controller.js`

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['from', 'to']
  static values = {
    eventName: { type: String, default: 'period:change' }
  }

  connect() {
    console.log('[period-picker] connected')
  }

  onChange() {
    const params = new URLSearchParams()

    if (this.hasFromTarget && this.fromTarget.value) {
      params.set('date_from', this.fromTarget.value)
    }

    if (this.hasToTarget && this.toTarget.value) {
      params.set('date_to', this.toTarget.value)
    }

    // weekdays[] inputs générés par weekday controller
    const weekdays = Array.from(
      this.element.querySelectorAll('input[name="weekday[]"]')
    ).map(i => i.value)

    if (weekdays.length > 0) {
      params.set('weekdays', weekdays.join(','))
    }

    console.log('[period-picker] dispatch', params.toString())

    this.element.dispatchEvent(
      new CustomEvent(this.eventNameValue, {
        bubbles: true,
        detail: {
          params: params.toString()
        }
      })
    )
  }
}
```

✔ Méthode `onChange` existe
✔ Event unique
✔ Format backend OK
✔ Aucun couplage

---

## 4️⃣ Weekday filter : CE QUE TU DOIS CHANGER

### ❌ À SUPPRIMER

Dans `_weekday_filter.html.twig` :

```twig
data-action="change->weekday#toggle change->chart#onPeriodChange"
```

👉 **Le weekday ne parle PLUS au chart**

---

### ✅ À METTRE À LA PLACE

```twig
data-action="change->weekday#toggle change->period-picker#onChange"
```

Le weekday **informe le period picker**, point.

Ton `weekday_controller.js` peut rester tel quel.
Il fait déjà exactement ce qu’il faut (inputs hidden `weekday[]`).

---

## 5️⃣ Controller `users_kpi_controller.js` (COMPLET)

📁 `assets/controllers/users_kpi_controller.js`

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['value', 'percent', 'trend']
  static values = {
    url: String
  }

  connect() {
    console.log('[users-kpi] connected')

    this.element.addEventListener('period:change', (e) => {
      this.load(e.detail.params)
    })
  }

  async load(params) {
    const url = `${this.urlValue}?${params}`
    console.log('[users-kpi] fetch', url)

    const response = await fetch(url)
    const data = await response.json()

    this.render(data)
  }

  render(data) {
    for (const [key, metric] of Object.entries(data.metrics)) {
      const el = this.element.querySelector(`[data-metric="${key}"]`)
      if (!el) continue

      el.querySelector('.metric-value').textContent = metric.value
      el.querySelector('.percent').textContent = `${metric.delta_percent}%`

      el.classList.toggle('up', metric.delta > 0)
      el.classList.toggle('down', metric.delta < 0)
    }
  }
}
```

---

## 6️⃣ `_metrics_card_users.html.twig` (PROPRE)

📁 `templates/admin/metrics/_metrics_card_users.html.twig`

```twig
<div
  class="metrics-card"
  data-controller="users-kpi"
  data-users-kpi-url="{{ path('admin_kpi_users') }}"
>
  <h3>UTILISATEURS</h3>

  <div class="metrics">
    <div class="metric" data-metric="registeredUsers">
      <span class="label">Enregistrés</span>
      <span class="metric-value">—</span>
      <span class="percent">—</span>
    </div>

    <div class="metric" data-metric="activeUsers">
      <span class="label">Actifs</span>
      <span class="metric-value">—</span>
      <span class="percent">—</span>
    </div>
  </div>
</div>
```

---

## 7️⃣ `index.html.twig` – VERSION CORRECTE

```twig
<form
  data-controller="period-picker"
  data-action="change->period-picker#onChange"
>
  <input
    type="date"
    name="from"
    data-period-picker-target="from"
  >

  <input
    type="date"
    name="to"
    data-period-picker-target="to"
  >

  {% include 'admin/_weekday_filter.html.twig' %}
</form>

{% include 'admin/metrics/_metrics_card_users.html.twig' %}

<div data-controller="chart"></div>
```

---

## 8️⃣ Bug UX : impossible de cliquer ailleurs après date picker

👉 **Cause réelle** :
Bootstrap dropdown + `<input type="date">` = focus trap

### ✅ Fix SIMPLE

Dans `_weekday_filter.html.twig`, sur le bouton dropdown :

```html
<button
  type="button"
  data-bs-auto-close="outside"
  ...
>
```

Et **ne mets PAS le date picker dans un dropdown**.
Jamais.
C’est un piège connu.

---

## 9️⃣ Résumé brutal (mais utile)

* ❌ Chart qui écoute le form → MAUVAIS
* ❌ Weekday qui parle au chart → MAUVAIS
* ✅ Period picker = source unique
* ✅ Event métier `period:change`
* ✅ Chart + KPI = listeners passifs

---

Si tu veux, prochain message :
👉 **on branche le chart PROPREMENT sur `period:change` sans toucher son code métier**
et on règle l’erreur ECharts “already initialized” une bonne fois pour toutes.

Respire. Là, on est enfin sur quelque chose de solide.
