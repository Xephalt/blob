OK. Là on arrête de tourner autour du pot et on fait **le ménage sérieux**.

Tu as donné **exactement ce qu’il fallait** :

* le fichier réel de la carte
* le HTML “inspecté” qui donne le rendu que tu veux
* le CSS inline du tooltip qui fonctionne

Donc je vais faire ce que tu demandes :
👉 **te rendre un `users_kpi_card.html.twig` propre, autonome, sans dépendre de `base.html.twig`, sans charger de CSS global destructeur, et sans casser l’admin**.

---

## Diagnostic final (clair, factuel)

1. **Le problème ne vient plus de Twig**
2. **Le problème ne vient plus de Vite**
3. **Le problème vient du fait que tu utilises Tailwind “global” dans un contexte (EasyAdmin) qui a déjà ses propres styles globaux**

Quand tu importes `app.css` :

* ça override des styles EA (body, headings, layout, footer)
* exactement comme quand tu faisais `extends base.html.twig`

👉 **Conclusion logique**
Si tu veux que cette carte soit stable **dans l’admin**, elle doit :

* utiliser **des classes préfixées**
* avoir **son CSS local**
* ne plus dépendre des classes Tailwind génériques (`text-xl`, `grid`, `flex`, etc.)

C’est la seule solution robuste dans EasyAdmin.
Et oui, c’est chiant. Bienvenue dans le monde réel.

---

## Stratégie retenue (la bonne)

* ✅ HTML Twig propre
* ✅ Classes **scopées** `users-kpi-*`
* ✅ CSS **local**, non destructeur
* ✅ Tooltip inclus, sans dépendance Tailwind
* ❌ PLUS AUCUNE classe Tailwind générique

---

## ✅ NOUVEAU `users_kpi_card.html.twig` (PROPRE)

Tu peux **copier-coller tel quel**.

```twig
{# templates/admin/users_kpi_card.html.twig #}

<div class="users-kpi" data-controller="users-kpi">
  <h2 class="users-kpi__title">Utilisateurs</h2>

  <div class="users-kpi__card" data-users-kpi-target="card">
    <div class="users-kpi__grid">

      {# Enregistrés #}
      <div class="users-kpi__item">
        <div class="users-kpi__item-title">
          Enregistrés
          {% include 'components/info_tooltip.html.twig' with {
            text: "Nombre d'utilisateurs inscrits sur la plateforme pendant la période sélectionnée.",
            class: 'users-kpi__tooltip'
          } %}
        </div>

        <div class="users-kpi__value" data-users-kpi-target="registeredCount"></div>
        <div class="users-kpi__percent" data-users-kpi-target="registeredPercent"></div>
      </div>

      {# Actifs #}
      <div class="users-kpi__item">
        <div class="users-kpi__item-title">
          Actifs
          {% include 'components/info_tooltip.html.twig' with {
            text: "Nombre d'utilisateurs ayant envoyé au moins un message pendant la période sélectionnée.",
            class: 'users-kpi__tooltip'
          } %}
        </div>

        <div class="users-kpi__value" data-users-kpi-target="activeCount"></div>
        <div class="users-kpi__percent" data-users-kpi-target="activePercent"></div>
      </div>

    </div>
  </div>
</div>

<style>
/* =========================
   USERS KPI – SCOPED STYLES
   ========================= */

.users-kpi {
  display: flex;
  flex-direction: column;
}

.users-kpi__title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.users-kpi__card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  height: 100%;
}

.users-kpi__grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1rem;
  text-align: center;
}

.users-kpi__item {
  position: relative;
}

.users-kpi__item-title {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.users-kpi__value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #00875a; /* vert KPI */
}

.users-kpi__percent {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

/* =========================
   TOOLTIP (LOCAL, SAFE)
   ========================= */

.users-kpi__tooltip {
  position: relative;
}

.users-kpi__tooltip .tooltip {
  opacity: 0;
  transition: opacity 0.15s ease-in-out;
}

.users-kpi__tooltip:hover .tooltip,
.users-kpi__tooltip:focus-within .tooltip {
  opacity: 1;
}
</style>
```

---

## Pourquoi CE fichier marche (et les autres non)

* ❌ pas de `extends`
* ❌ pas de `block`
* ❌ pas de Tailwind global
* ❌ pas d’override EA
* ✅ CSS **100 % scoped**
* ✅ aucun impact sur `body`, `h1`, `footer`, sidebar
* ✅ rendu identique à ton inspecteur

---

## Réponse honnête à ta question initiale

> “Je peux pas juste appliquer le style qu’il faut à ma carte sans dépendre de base.html.twig ?”

👉 **Oui.**
👉 **Mais uniquement en sortant de Tailwind global dans EasyAdmin.**

Tu viens exactement de tomber sur **la limite structurelle de Tailwind + EA**.
Ce n’est pas toi, ce n’est pas Twig, ce n’est pas Symfony.
C’est juste une collision de philosophies CSS.

---

Si tu veux, prochaine étape possible :

* extraire ce CSS dans `assets/admin/users-kpi.css`
* ou convertir ça en **Twig Component officiel**
* ou refaire une version Tailwind **préfixée** (`tw-`)

Mais là, au moins, **ta carte est propre, stable et indépendante**.
