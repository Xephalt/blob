Bon. Respire. Rien de mystique, rien de Symfony qui “mange” tes données. C’est juste un **problème de chaîne d’événements cassée**. Et oui, ça arrive quand on compose Stimulus sans décider QUI parle à QUI. Bienvenue dans le monde réel.

Je vais être très clair, et on repart **proprement**, sans magie.

---

## 🎯 Objectif réel (rappel)

* **Un seul point de vérité** : le *period picker* (dates + weekdays)
* Il **émet un événement**
* Les consommateurs (chart, users KPI, plus tard messages KPI, etc.) **écoutent**
* Le weekday filter **ne déclenche rien tout seul**, il informe le period picker

Actuellement, ton weekday controller déclenche `chart#onPeriodChange` directement.
➡️ **Erreur conceptuelle** : il court-circuite le dispatcher.

---

# ✅ Architecture correcte (simple et scalable)

```
[date inputs] ┐
               ├──▶ period-picker (Stimulus)
[weekday UI]  ┘          |
                           ├── dispatch("period:change", payload)
                           |
                ┌──────────┴───────────┐
                ▼                      ▼
           chart controller      users-kpi controller
```

---

# 1️⃣ CE QUE TU DOIS CHANGER (résumé rapide)

### ❌ À SUPPRIMER

Dans `_weekday_filter.html.twig` :

```twig
data-action="change->weekday#toggle change->chart#onPeriodChange"
```

👉 **Le weekday ne parle PLUS au chart. Jamais.**

---

# 2️⃣ `_weekday_filter.html.twig` (corrigé)

```twig
<div class="dropdown" data-controller="weekday">
    <button
        class="btn btn-outline-secondary weekday-btn"
        type="button"
        data-bs-toggle="dropdown"
    >
        <div class="weekday-btn-content">
            <div class="weekday-chips" data-weekday-target="chips">
                <span class="weekday-placeholder">Tous les jours</span>
            </div>
            <div class="weekday-arrow">
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>
    </button>

    <div class="dropdown-menu p-3">
        {% for value, label in {
            1:'Lundi',2:'Mardi',3:'Mercredi',4:'Jeudi',
            5:'Vendredi',6:'Samedi',7:'Dimanche'
        } %}
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    value="{{ value }}"
                    id="weekday-{{ value }}"
                    data-weekday-target="checkbox"
                    data-action="change->weekday#toggle"
                >
                <label class="form-check-label" for="weekday-{{ value }}">
                    {{ label }}
                </label>
            </div>
        {% endfor %}
    </div>

    {# Valeurs exposées au parent #}
    <div data-weekday-target="inputs"></div>
</div>
```

---

# 3️⃣ `weekday_controller.js` (corrigé)

👉 **Il ne dispatch plus rien vers le chart**
👉 **Il émet un événement DOM local que le period picker écoutera**

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['checkbox', 'chips', 'inputs']

  toggle() {
    this.chipsTarget.innerHTML = ''
    this.inputsTarget.innerHTML = ''

    const checked = this.checkboxTargets.filter(cb => cb.checked)

    if (checked.length === 0) {
      this.renderPlaceholder()
    } else {
      checked.forEach(cb => {
        this.renderChip(cb)
        this.renderHiddenInput(cb)
      })
    }

    // 🔥 EVENT UNIQUE ET PROPRE
    this.element.dispatchEvent(
      new CustomEvent('weekday:change', { bubbles: true })
    )
  }

  renderPlaceholder() {
    const span = document.createElement('span')
    span.className = 'weekday-placeholder'
    span.textContent = 'Tous les jours'
    this.chipsTarget.appendChild(span)
  }

  renderChip(checkbox) {
    const chip = document.createElement('div')
    chip.className = 'weekday-chip'
    chip.textContent = checkbox.nextElementSibling.textContent.slice(0, 2)

    chip.addEventListener('click', e => {
      e.stopPropagation()
      checkbox.checked = false
      this.toggle()
    })

    this.chipsTarget.appendChild(chip)
  }

  renderHiddenInput(checkbox) {
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = 'weekdays[]'
    input.value = checkbox.value
    this.inputsTarget.appendChild(input)
  }
}
```

---

# 4️⃣ Le **period picker controller** (clé du système)

👉 Lui seul écoute les dates + weekday
👉 Lui seul dispatch `period:change`

```js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['from', 'to', 'form']

  connect() {
    this.element.addEventListener('weekday:change', () => this.emit())
  }

  onDateChange() {
    this.emit()
  }

  emit() {
    const formData = new FormData(this.formTarget)
    const params = new URLSearchParams(formData).toString()

    this.element.dispatchEvent(
      new CustomEvent('period:change', {
        bubbles: true,
        detail: { params }
      })
    )
  }
}
```

---

# 5️⃣ Chart controller (adaptation MINIMALE)

👉 Tu ne touches **pas** à `load()`
👉 Tu ajoutes juste un listener

```js
connect() {
  this.element.addEventListener('period:change', e => {
    this.load(e.detail.params)
  })
}
```

---

# 6️⃣ Users KPI controller (raison pour laquelle ta carte reste vide)

**Bug actuel** :

* Le controller est bien chargé
* MAIS **il n’écoute aucun événement**
* Donc il ne fetch jamais

### ✅ Fix :

```js
connect() {
  this.element.addEventListener('period:change', e => {
    this.load(e.detail.params)
  })
}

async load(params) {
  const url = `${this.urlValue}?${params}`
  const res = await fetch(url)
  const data = await res.json()
  this.render(data)
}
```

---

## ❗ Pourquoi “plus aucun appel réseau” ?

Parce que :

* Le weekday parlait au chart
* Le chart n’était plus au bon endroit
* Le users-kpi n’écoutait RIEN

➡️ Résultat : **événements dans le vide**

---

## ✅ Conclusion honnête

* Ton idée est **100% faisable**
* L’architecture est **saine**
* Le problème n’était **pas Symfony**
* C’était juste un **chaînage d’événements mal défini**

Maintenant :

* period picker = cerveau
* weekday = UI stupide
* chart / KPI = consommateurs

C’est propre. Ça scale. Et cette fois, ça va marcher.
