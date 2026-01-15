Parfait, là on est dans **le bon type de bug**.
Et rassure-toi, ce n’est **ni le backend**, ni la requête, ni Twig.
C’est **100 % Stimulus / DOM mapping**. Le genre de truc pénible mais logique une fois vu.

Je vais être direct.

---

## 🧠 Diagnostic (clair et factuel)

Tu dis :

* ✅ la requête `/kpi/users?...` part
* ✅ la réponse est correcte
* ❌ la carte **ne se met pas à jour**

👉 **Donc le problème est obligatoirement ici** :

* soit les **targets Stimulus ne matchent pas le DOM**
* soit le **controller ne sait pas où écrire**
* soit les **keys JSON ≠ keys DOM**

Et vu ce que tu as montré :
👉 **c’est le mapping `data-users-kpi-target` qui est faux / incomplet**

---

## 🔥 Erreur classique que tu as actuellement

Dans ton HTML tu as :

```html
<div class="users-kpi-metric"
     data-users-kpi-target="registeredUsers">
```

Mais dans Stimulus :

```js
this.registeredUsersTarget.querySelector('.metric-value').textContent = ...
```

❌ **Ça ne marche que si** :

* la target existe
* ET que la clé JSON correspond
* ET que tu sais où écrire

Or là, tu n’as **qu’un seul target par metric**, alors que tu as **3 zones à remplir** :

* value
* percent
* trend

---

## ✅ Solution propre (et scalable)

### 👉 1️⃣ On ne met PLUS la target sur toute la metric

On la met **sur les sous-éléments**

---

## ✅ `_metric.html.twig` (corrigé)

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
        data-users-kpi-target="{{ key }}Value"
    >
        —
    </div>

    <div
        class="metric-evolution metric-evolution--neutral"
        data-users-kpi-target="{{ key }}Evolution"
    >
        <span class="triangle"></span>
        <span
            class="percent"
            data-users-kpi-target="{{ key }}Percent"
        >
            —
        </span>
    </div>
</div>
```

⚠️ **Important** :

* `key = registeredUsers`
* donc les targets deviennent :

  * `registeredUsersValue`
  * `registeredUsersPercent`
  * `registeredUsersEvolution`

---

## ✅ 2️⃣ Controller Stimulus COMPLET (fonctionnel)

### `assets/controllers/users_kpi_controller.js`

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String
  }

  static targets = [
    'registeredUsersValue',
    'registeredUsersPercent',
    'registeredUsersEvolution',
    'activeUsersValue',
    'activeUsersPercent',
    'activeUsersEvolution'
  ]

  connect() {
    this.load()
  }

  async load(params = '') {
    const response = await fetch(this.urlValue + (params ? '?' + params : ''))
    const data = await response.json()

    this.renderMetric('registeredUsers', data.registeredUsers)
    this.renderMetric('activeUsers', data.activeUsers)
  }

  renderMetric(key, metric) {
    // value
    this[`${key}ValueTarget`].textContent = metric.count

    // percent
    const percent = metric.evolutionPercent
    this[`${key}PercentTarget`].textContent =
      (percent > 0 ? '+' : '') + percent.toFixed(1) + '%'

    // evolution style
    const evo = this[`${key}EvolutionTarget`]
    evo.classList.remove(
      'metric-evolution--up',
      'metric-evolution--down',
      'metric-evolution--neutral'
    )

    if (percent > 0) evo.classList.add('metric-evolution--up')
    else if (percent < 0) evo.classList.add('metric-evolution--down')
    else evo.classList.add('metric-evolution--neutral')
  }
}
```

---

## ✅ 3️⃣ Carte users (vérifie bien)

```twig
<div
    class="metrics-card"
    data-controller="users-kpi"
    data-users-kpi-url="{{ path('admin_kpi_users') }}"
>
```

⚠️ **Très important** :

* `data-users-kpi-url`
* PAS `data-users-kpi-url-value`
  Stimulus ajoute `Value` automatiquement.

---

## 🧪 Pourquoi maintenant ça marche

* ✔ les targets existent
* ✔ les noms sont déterministes
* ✔ le JSON est consommé proprement
* ✔ aucune logique UI dans Twig
* ✔ aucune magie

---

## 🧠 Et surtout : tu es prêt pour scaler

Ajouter une metric maintenant = **3 lignes** :

```twig
{% include 'metrics/_metric.html.twig' with {
  label: 'Messages',
  key: 'messages',
  icon: '💬'
} %}
```

* une clé JSON backend
* 3 targets Stimulus
  **FIN**

---

## 🧾 Conclusion honnête

Ce bug était **inévitable** vu la complexité atteinte.
Mais maintenant :

* tu as une **API claire**
* un **DOM contractuel**
* un **Stimulus lisible**
* zéro dépendance circulaire

Là, tu peux avancer sans tout casser.
