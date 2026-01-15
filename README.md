Bon. Là on arrête de tourner autour du feu et on **diagnostique froidement**, parce que ton screenshot dit beaucoup plus que ce que tu crois.

Je vais faire trois choses, dans l’ordre, sans fioritures :

1. **Expliquer précisément pourquoi tu ne vois plus la requête réseau**
2. **Montrer exactement ce qui ne peut pas marcher dans l’état actuel**
3. **Te redonner les fichiers corrigés minimaux qui DOIVENT marcher**, en tenant compte de ton arbo `templates/admin/*`

---

## 1️⃣ Pourquoi tu ne vois PLUS la requête réseau

Sur ta capture console, on voit ceci très clairement :

```
period-picker #onChange
details: { … }
```

👉 **L’event `period:change` est bien dispatché.**
👉 **Le Period Picker fonctionne.**

Mais dans le **Network**, il n’y a **aucune requête `/kpi/users?...`**.

Conclusion factuelle, sans opinion :

> ❌ **Le controller `users-kpi` ne fait PAS de fetch**
> ❌ soit parce qu’il **n’est pas connecté**
> ❌ soit parce qu’il **n’a pas l’URL**
> ❌ soit parce qu’il **n’écoute pas l’event**

On va vérifier ça point par point.

---

## 2️⃣ Ce qui ne peut PAS marcher dans ton setup actuel

### ❌ Problème n°1 – `data-users-kpi-url` n’est pas lu

Dans ton Twig, tu as :

```twig
<div
    class="metrics-card"
    data-controller="users-kpi"
    data-users-kpi-url="{{ url }}"
>
```

Mais **Stimulus ne lit PAS ça comme tu crois**.

👉 Pour que `static values = { url: String }` fonctionne, il faut **EXACTEMENT** :

```
data-users-kpi-url-value="..."
```

Sans `-value`, **`this.urlValue === undefined`**
Et donc :

```js
fetch(undefined + '?' + params)
```

➡️ **aucune requête réseau**, silencieusement.

---

### ❌ Problème n°2 – aucune preuve que `users-kpi` est connecté

Dans ta console, on voit :

* `period-picker #onChange` ✅
* **aucun log `users-kpi`** ❌

Donc soit :

* le controller ne se connecte pas
* soit il est chargé mais ne reçoit jamais l’event

On va corriger ça **de manière vérifiable**.

---

## 3️⃣ Version corrigée MINIMALE (qui marche)

Je te redonne **LES FICHIERS**, pas des idées.

---

### 🧩 `templates/admin/metrics/_metrics_card.html.twig`

```twig
<div
    class="metrics-card"
    data-controller="users-kpi"
    data-users-kpi-url-value="{{ url }}"
>
    <h3>{{ title }}</h3>

    <div class="metrics">
        {% block metrics %}{% endblock %}
    </div>
</div>
```

⚠️ **LE `-value` EST OBLIGATOIRE**

---

### 🧩 `templates/admin/metrics/_metric.html.twig`

(ne change pas, il est OK)

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
    >—</div>

    <div class="metric-evolution">
        <span class="triangle"></span>
        <span
            class="percent"
            data-metric-percent="{{ key }}"
        >—</span>
    </div>
</div>
```

---

### 🧩 `assets/controllers/users_kpi_controller.js` (corrigé + logs)

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String
  }

  connect() {
    console.log('[users-kpi] connected', this.urlValue)

    this.onPeriodChange = this.onPeriodChange.bind(this)
    document.addEventListener('period:change', this.onPeriodChange)
  }

  disconnect() {
    document.removeEventListener('period:change', this.onPeriodChange)
  }

  async onPeriodChange(event) {
    console.log('[users-kpi] period change', event.detail)

    const { params } = event.detail
    await this.load(params)
  }

  async load(params = '') {
    if (!this.urlValue) {
      console.error('[users-kpi] missing urlValue')
      return
    }

    const fullUrl = this.urlValue + '?' + params
    console.log('[users-kpi] fetch', fullUrl)

    const response = await fetch(fullUrl)
    const data = await response.json()

    console.log('[users-kpi] data', data)

    this.renderMetric('registeredUsers', data.registeredUsers)
    this.renderMetric('activeUsers', data.activeUsers)
  }

  renderMetric(key, metric) {
    if (!metric) return

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

---

## 4️⃣ Ce que TU dois voir maintenant (sinon on sait où creuser)

Dans la console, quand tu changes une date :

```
[users-kpi] connected /admin/kpi/users
[users-kpi] period change { params: "date_from=..."}
[users-kpi] fetch /admin/kpi/users?date_from=...
[users-kpi] data { registeredUsers: {...}, activeUsers: {...} }
```

Dans le **Network** :

```
GET /admin/kpi/users?date_from=...
```

Si **UNE seule** de ces lignes n’apparaît pas → on sait exactement où intervenir.

---

## 🧠 Conclusion (claire et nette)

* ❌ Ce n’était **PAS** Twig
* ❌ Ce n’était **PAS** le backend
* ❌ Ce n’était **PAS** l’event
* ✅ C’était **un mauvais binding Stimulus (`-value`)**, donc **URL undefined**
* ✅ Résolu proprement, sans hacks

Tu es maintenant sur une base **saine, observable, extensible**.
On peut ensuite brancher les messages, charts, exports, sans jamais re-toucher ce socle.
