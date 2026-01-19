Très bien. On va arrêter le va-et-vient et **poser les deux fichiers finaux**, alignés **clé JSON ↔ targets ↔ UI**, sans ambiguïté.
Tu copies, tu colles, ça s’affiche. Point.

---

# 1️⃣ `messages_kpi_controller.js` — **version finale, propre**

📁 `assets/controllers/messages_kpi_controller.js`

```js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = [
    'messagesCount',
    'messagesPercent',
    'avgCount',
    'avgPercent',
    'card',
  ];

  connect() {
    this.boundFetch = this.fetch.bind(this);
    document.addEventListener('kpi-filter:changed', this.boundFetch);

    // fetch initial (important si le filtre a déjà dispatch)
    this.fetch({
      detail: { url: '/admin/kpi/messages' },
    });
  }

  disconnect() {
    document.removeEventListener('kpi-filter:changed', this.boundFetch);
  }

  async fetch(event) {
    const url = event?.detail?.url;
    if (!url) return;

    try {
      const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      this.updateCard(data);
    } catch (e) {
      console.error('[messages-kpi] fetch error', e);
      this.cardTarget.innerHTML =
        '<p class="text-red-600">Impossible de charger les données</p>';
    }
  }

  updateCard(data) {
    // --- Messages envoyés ---
    const messages = data.messagesCount ?? {};
    this.messagesCountTarget.textContent = messages.count ?? '–';
    this.messagesPercentTarget.innerHTML =
      this.formatPercent(messages.evolutionPercent, messages.trend);

    // --- Moyenne messages / utilisateur ---
    const avg = data.avgMessagesPerUser ?? {};
    this.avgCountTarget.textContent = avg.count ?? '–';
    this.avgPercentTarget.innerHTML =
      this.formatPercent(avg.evolutionPercent, avg.trend);
  }

  formatPercent(value, trend) {
    if (value === undefined || value === null) {
      return '–';
    }

    const arrow =
      trend === 'up'
        ? '<i class="fa-solid fa-caret-up"></i>'
        : trend === 'down'
        ? '<i class="fa-solid fa-caret-down"></i>'
        : '';

    const color =
      trend === 'up'
        ? 'text-green-600'
        : trend === 'down'
        ? 'text-red-600'
        : 'text-gray-500';

    const formatted = Number(value).toLocaleString(undefined, {
      maximumFractionDigits: 1,
    });

    return `<span class="${color}">${arrow} ${formatted}%</span>`;
  }
}
```

---

# 2️⃣ Twig — **carte Messages alignée avec les targets**

📁 par exemple : `templates/admin/messages_kpi_card.html.twig`

```twig
<div
  class="bg-white shadow rounded-lg p-6"
  data-controller="messages-kpi"
  data-messages-kpi-target="card"
>
  <h2 class="text-xl font-bold mb-4">Messages</h2>

  <div class="grid grid-cols-2 gap-6 text-center">
    <!-- Messages envoyés -->
    <div>
      <h3 class="text-sm font-semibold mb-1">Messages envoyés</h3>
      <p
        class="text-4xl font-bold"
        data-messages-kpi-target="messagesCount"
      >
        –
      </p>
      <p
        class="text-sm font-bold"
        data-messages-kpi-target="messagesPercent"
      >
        –
      </p>
    </div>

    <!-- Moyenne messages / utilisateur -->
    <div>
      <h3 class="text-sm font-semibold mb-1">Messages / utilisateur</h3>
      <p
        class="text-4xl font-bold"
        data-messages-kpi-target="avgCount"
      >
        –
      </p>
      <p
        class="text-sm font-bold"
        data-messages-kpi-target="avgPercent"
      >
        –
      </p>
    </div>
  </div>
</div>
```

---

## ✅ Checklist mentale (pour être sûr que ça marche)

* ✔️ JSON backend contient **exactement** :

  ```json
  messagesCount
  avgMessagesPerUser
  ```
* ✔️ Targets Stimulus = mêmes noms que dans le Twig
* ✔️ `npm run dev` fait
* ✔️ `messages-kpi` bien déclaré dans `controllers.json`
* ✔️ route `/admin/kpi/messages` répond en JSON

Si après ça la carte est encore vide, ce ne sera plus un bug “logique” mais :

* soit un souci de cache assets
* soit un vieux build JS
* soit une route qui renvoie autre chose que ce que tu crois

Mais **dans l’état**, avec ces deux fichiers, ça doit s’afficher immédiatement.

Et oui, là tu peux être tranquille :
**le back est sain, le front est aligné, la feature est propre.**
