Parfait. Merci pour le Twig, là on parle concret.
Et bonne nouvelle : **ton Twig Users est propre**. Vraiment. Donc l’adaptation Messages est **bête et disciplinée**, pas une refonte.

Je vais faire trois choses :

1. Te dire **ce qui ne change pas**
2. Te dire **ce qui change exactement**
3. Te donner **le fichier Twig Messages complet**, prêt à coller

Sans abstraction “intelligente”, sans macro Twig prématurée, sans templating ésotérique.

---

## 1️⃣ Ce qui NE change PAS

* La structure HTML globale
* Le layout en `grid grid-cols-2`
* La logique “une valeur + un pourcentage”
* Les classes Tailwind
* Le fait que **Stimulus pilote tout**

👉 On reste **ISO Users**, c’est voulu.

---

## 2️⃣ Ce qui change (et seulement ça)

### A. Le controller Stimulus

```diff
- data-controller="users-kpi"
+ data-controller="messages-kpi"
```

### B. Le namespace des targets

```diff
- data-users-kpi-target="..."
+ data-messages-kpi-target="..."
```

### C. Le contenu métier affiché

* “Enregistrés” → **Messages envoyés**
* “Actifs” → **Messages / utilisateur (moy.)**

C’est tout. Le reste, tu ne touches pas.

---

## 3️⃣ Twig Messages — VERSION COMPLÈTE

👉 Fichier suggéré :

```
templates/admin/messages_kpi_card.html.twig
```

```twig
{# templates/admin/messages_kpi_card.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
<div class="container mx-auto p-4" data-controller="messages-kpi">
    <h2 class="text-2xl font-bold">Messages</h2>

    <div
        class="bg-white shadow rounded-lg p-6"
        data-messages-kpi-target="card"
    >
        <div class="grid grid-cols-2 gap-4 text-center">

            {# Messages envoyés #}
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Messages envoyés
                </h3>

                <p
                    class="text-4xl font-bold bnpp-color-green"
                    data-messages-kpi-target="messageCount"
                >
                    –
                </p>

                <p
                    class="text-sm font-bold"
                    data-messages-kpi-target="messagePercent"
                >
                    –
                </p>
            </div>

            {# Messages / utilisateur #}
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Messages / utilisateur
                </h3>

                <p
                    class="text-4xl font-bold bnpp-color-green"
                    data-messages-kpi-target="messageAvg"
                >
                    –
                </p>

                <p
                    class="text-sm font-bold"
                    data-messages-kpi-target="messageAvgPercent"
                >
                    –
                </p>
            </div>

        </div>
    </div>
</div>
{% endblock %}
```

---

## 4️⃣ Pourquoi c’est la BONNE adaptation (et pas une rustine)

* ✅ **Aucun JS conditionnel**
* ✅ **Aucun if Twig**
* ✅ **Aucun couplage Users/Messages**
* ✅ **Même expérience UX**
* ✅ **Même event global**
* ✅ **Même logique mentale**

Chaque carte :

* écoute le filtre
* fetch sa route
* sait mapper SON JSON

C’est propre. Et surtout, **ça vieillit bien**.

---

## 5️⃣ Ce que tu n’as toujours PAS à faire (je le répète)

❌ Créer un macro Twig
❌ Factoriser Users/Messages dans un seul template
❌ Ajouter des `if` sur le type de KPI
❌ Toucher au filtre
❌ Toucher au controller Symfony

---

## 6️⃣ Checklist finale (rapide)

* [x] Backend `/admin/kpi/messages`
* [x] `messages_kpi_controller.js`
* [x] Enregistré dans `controllers.json`
* [x] Twig Messages branché
* [ ] Tester un changement de période
* [ ] Tester weekdays
* [ ] Vérifier JSON mapping

---

Franchement :
ce que tu fais là, ce n’est pas “rajouter une carte”.
C’est **poser un pattern scalable sans se tirer une balle dans le pied**.

Quand tu veux, prochaine étape :
👉 vérifier ensemble que le JSON Messages colle parfaitement au JS (et ajuster **une seule ligne si besoin**).
