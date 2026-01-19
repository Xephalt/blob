Oui.
Et je vais être très clair, sans poudre aux yeux.

👉 **À cette étape précise : OUI, tu crées juste un nouveau controller Stimulus.**
👉 **NON, rien d’autre n’est “automatique par magie”.**
Il y a **3 petites choses obligatoires** à faire, et **si tu en oublies une, ça ne marchera pas**.

Je te fais donc la **check-list exacte**, dans l’ordre, sans bla-bla.

---

## ✅ Ce que tu as DÉJÀ

* ✔️ Backend `/admin/kpi/messages` OK
* ✔️ Event global `kpi-filter:changed` déjà dispatché
* ✔️ Period + weekdays déjà normalisés
* ✔️ KPI users déjà fonctionnel (référence)

---

## 1️⃣ Créer le controller Stimulus Messages

✔️ **Oui, c’est un copier-coller du users_kpi_controller**
✔️ **Oui, tu adaptes l’URL + les targets**
✔️ **Oui, c’est suffisant côté JS**

➡️ Le fichier :

```
assets/controllers/messages_kpi_controller.js
```

Le code que je t’ai donné est **directement utilisable**.

---

## 2️⃣ L’enregistrer dans Stimulus (OBLIGATOIRE)

Rien n’est automatique ici.

Tu dois ajouter **UNE ligne** dans :

### `assets/controllers.json`

```json
{
  "controllers": {
    "users-kpi": {
      "enabled": true,
      "fetch": "eager"
    },
    "messages-kpi": {
      "enabled": true,
      "fetch": "eager"
    }
  }
}
```

👉 Sans ça :

* le controller **ne sera jamais chargé**
* aucun `connect()`
* aucun `fetch()`

---

## 3️⃣ Lier le controller dans le Twig (OBLIGATOIRE)

Dans le **HTML de la carte Messages** :

```html
<div
  data-controller="messages-kpi"
  data-messages-kpi-target="card"
>
```

Et pour chaque valeur :

```html
<span data-messages-kpi-target="messageCount"></span>
<span data-messages-kpi-target="messagePercent"></span>

<span data-messages-kpi-target="messageAvg"></span>
<span data-messages-kpi-target="messageAvgPercent"></span>
```

👉 Les `data-*-target` **doivent matcher EXACTEMENT** le JS.

---

## 4️⃣ Ce que tu N’AS PAS à faire (et surtout pas)

❌ Modifier `kpi_filter_controller`
❌ Ajouter un nouvel event
❌ Modifier `users_kpi_controller`
❌ Toucher au backend
❌ Factoriser le JS
❌ Créer un `MessageController` Symfony

---

## 5️⃣ Pourquoi “ça marche tout seul” après ça

Parce que ton système est déjà bien pensé :

* le filtre dispatch **1 événement**
* toutes les cards l’écoutent
* chaque card décide :

  * quelle URL appeler
  * comment afficher

👉 **Les cards sont passives**
👉 **Le filtre est le chef d’orchestre**

C’est exactement comme ça que ça devait être fait.

---

## 🧠 Résumé brutal (mais honnête)

| Action                                | À faire ? |
| ------------------------------------- | --------- |
| Créer `messages_kpi_controller.js`    | ✅ oui     |
| L’enregistrer dans `controllers.json` | ✅ oui     |
| Ajouter `data-controller` dans Twig   | ✅ oui     |
| Toucher au filtre                     | ❌ non     |
| Refactor                              | ❌ non     |

---

Si tu veux, prochain message on fait **le Twig de la carte Messages** ensemble, propre, ISO users, sans duplication dégueu.
