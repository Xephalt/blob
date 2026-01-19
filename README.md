Bon. Là on n’est plus dans la théorie ni l’architecture PowerPoint. On est dans **“finir la feature sans tout casser”**.
Et franchement, ton setup Stimulus est **déjà très bon**. Tu n’as **rien à réinventer**, juste à **dupliquer intelligemment**.

Je vais donc te dire **exactement quoi faire**, étape par étape, **sans refactor global**, **sans abstraction prématurée**, et **en restant ISO avec Users**.

---

# Objectif clair (rappel)

Ajouter **une carte Messages** qui :

* réagit **au même period picker**
* réagit **aux mêmes weekdays**
* tape `/admin/kpi/messages`
* affiche :

  * nombre de messages
  * messages / utilisateur en moyenne
  * évolution + trend

👉 **ZÉRO nouvelle logique de filtre**
👉 **ZÉRO nouvelle communication globale**
👉 On réutilise **l’événement `kpi-filter:changed`**

---

## 1️⃣ KPI Filter Controller (Stimulus)

### ❌ Rien à changer

Et c’est important de le dire.

Ton `kpi_filter_controller.js` :

* construit la query string
* dispatch `kpi-filter:changed`
* expose `detail.url`

👉 **Il est générique**
👉 **Il ne doit PAS connaître Users ou Messages**

✔️ On touche à rien.

---

## 2️⃣ KPI Users Card Controller

Tu as aujourd’hui un truc du genre (simplifié) :

```js
// users_kpi_controller.js
this.fetch({ detail: { url: '/admin/kpi/users?...' } })
```

Ce controller :

* écoute `kpi-filter:changed`
* fetch une URL
* met à jour une card

👉 **Le pattern est parfait**
👉 On va faire **EXACTEMENT le même** pour Messages

---

## 3️⃣ Ce que tu DOIS faire pour Messages

### Étape 1 — Créer un nouveau controller Stimulus

👉 **Dupliquer**, pas abstraire.

```
assets/controllers/messages_kpi_controller.js
```

---

### Étape 2 — Adapter UNIQUEMENT 3 choses

#### 1. l’URL

```diff
- '/admin/kpi/users'
+ '/admin/kpi/messages'
```

#### 2. les targets

Messages ≠ Users, donc :

* `messageCount`
* `messageAvg`
* `messagePercent`
* `messageAvgPercent`
* `card`

#### 3. le mapping JSON

Ton backend renverra (exemple) :

```json
{
  "messages": { ... },
  "messagesPerUser": { ... }
}
```

---

## 4️⃣ Controller Stimulus Messages (COMPLET)

Voici **un fichier prêt à poser**, ISO users, sans magie.

```js
// assets/controllers/messages_kpi_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = [
    'messageCount',
    'messagePercent',
    'messageAvg',
    'messageAvgPercent',
    'card'
  ];

  connect() {
    this.boundFetch = this.fetch.bind(this);
    document.addEventListener('kpi-filter:changed', this.boundFetch);
  }

  disconnect() {
    document.removeEventListener('kpi-filter:changed', this.boundFetch);
  }

  async fetch(event) {
    const url = event.detail?.url?.replace('/users', '/messages');
    if (!url) return;

    try {
      const response = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      this.updateCard(data);
    } catch (e) {
      console.error('Erreur KPI messages', e);
      this.cardTarget.innerHTML =
        '<p class="text-danger">Impossible de charger les données</p>';
    }
  }

  updateCard(data) {
    const messages = data.messages ?? {};
    const avg = data.messagesPerUser ?? {};

    this.messageCountTarget.textContent = messages.count ?? '–';
    this.messagePercentTarget.innerHTML =
      this.formatPercent(messages.evolutionPercent, messages.trend);

    this.messageAvgTarget.textContent = avg.count ?? '–';
    this.messageAvgPercentTarget.innerHTML =
      this.formatPercent(avg.evolutionPercent, avg.trend);
  }

  formatPercent(value, trend) {
    if (value === null || value === undefined) return '–';

    const arrow =
      trend === 'up'
        ? '<i class="fa-solid fa-caret-up"></i>'
        : trend === 'down'
        ? '<i class="fa-solid fa-caret-down"></i>'
        : '';

    const color =
      trend === 'up'
        ? 'text-success'
        : trend === 'down'
        ? 'text-danger'
        : 'text-muted';

    const formatted = Number(value).toLocaleString(undefined, {
      maximumFractionDigits: 1,
    });

    return `<span class="${color}">${arrow} ${formatted}%</span>`;
  }
}
```

---

## 5️⃣ Pourquoi c’est la bonne approche (et pas une abstraction)

Tu pourrais te dire :

> “On pourrait faire un `BaseKpiController` JS”

Oui.
Et ce serait une **erreur maintenant**.

Pourquoi ?

* tu n’as que **2 cartes**
* elles n’ont pas **exactement les mêmes champs**
* la duplication est **faible et lisible**
* le jour où ça diverge, tu seras content de ne pas avoir factorisé

👉 **TDD mental validé**
👉 **Charge cognitive minimale**

---

## 6️⃣ Ce qu’il te reste à faire (checklist finale)

### Backend

* [x] `/admin/kpi/messages`
* [x] DTO ISO users
* [x] handler OK

### Front

* [x] `messages_kpi_controller.js`
* [ ] enregistrer le controller dans `controllers.json`
* [ ] ajouter `data-controller="messages-kpi"` sur la card Twig
* [ ] ajouter les bons `data-*-target`

### Twig

👉 On fera ça **après**, proprement.

---

## Conclusion honnête

Tu as :

* une **architecture saine**
* un **event global bien pensé**
* un **backend clean**
* un **front extensible sans refactor**

Tu ne fais pas “du bricolage”.
Tu fais **exactement ce qu’il faut**, au bon moment.

Quand tu veux, prochaine étape :
👉 **le Twig de la card Messages**
et je t’aiderai à le rendre **ISO visuellement** avec Users, sans copier-coller dégueu.
