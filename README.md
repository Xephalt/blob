Oui. Normal. Et ce n’est **pas** parce que “le code est faux”, c’est parce que **le rendu que tu veux dépend encore de règles implicites que tu n’as pas recopiées**.

On va remettre les choses à plat calmement, sinon tu vas continuer à avoir l’impression que tout te glisse entre les doigts.

---

## Pourquoi “le rendu n’a rien à voir” (factuel, pas philosophique)

Le rendu que tu montres sur la capture “qui est bien” repose sur **4 choses cachées** :

1. **Un container parent EA**

   * padding horizontal
   * fond gris clair
   * border-radius global
   * shadow douce déjà posée

2. **Des styles globaux EA sur les titres**

   * `h2`, `h3` ont déjà des margins, line-height, font-family
   * toi tu as remis des tailles “neutres”

3. **Une largeur contrainte**

   * ta carte n’est PAS full-width
   * elle est dans un wrapper EA avec max-width + spacing

4. **Des couleurs héritées**

   * le vert n’est pas un simple `#00875a`
   * le rouge est un token EA (pas un rouge brut)

👉 Donc quand tu fais une carte **100 % autonome**, elle est visuellement correcte… mais **elle n’est plus “habillée” par EA**.
C’est exactement ce que tu observes.

---

## Ce qu’on va faire maintenant (la bonne approche)

👉 **On ne revient PAS à Tailwind global**
👉 **On ne dépend PAS de `base.html.twig`**
👉 **On aligne visuellement la carte sur EasyAdmin**

Donc :

* CSS scoped
* MAIS **tokens visuels proches de EA**
* ET structure identique à ce que tu as inspecté

---

## Version corrigée – rendu proche de ta capture

### ✅ `users_kpi_card.html.twig` (HTML quasi inchangé)

```twig
<div class="users-kpi" data-controller="users-kpi">
  <h2 class="users-kpi__title">Utilisateurs</h2>

  <div class="users-kpi__card">
    <div class="users-kpi__grid">

      <div class="users-kpi__item">
        <div class="users-kpi__item-title">
          Enregistrés
          {% include 'components/info_tooltip.html.twig' with {
            text: "Nombre d'utilisateurs inscrits sur la plateforme pendant la période sélectionnée.",
            class: 'users-kpi__tooltip'
          } %}
        </div>

        <div class="users-kpi__value users-kpi__value--positive"
             data-users-kpi-target="registeredCount"></div>

        <div class="users-kpi__percent users-kpi__percent--negative"
             data-users-kpi-target="registeredPercent"></div>
      </div>

      <div class="users-kpi__item">
        <div class="users-kpi__item-title">
          Actifs
          {% include 'components/info_tooltip.html.twig' with {
            text: "Nombre d'utilisateurs ayant envoyé au moins un message pendant la période sélectionnée.",
            class: 'users-kpi__tooltip'
          } %}
        </div>

        <div class="users-kpi__value users-kpi__value--positive"
             data-users-kpi-target="activeCount"></div>

        <div class="users-kpi__percent users-kpi__percent--negative"
             data-users-kpi-target="activePercent"></div>
      </div>

    </div>
  </div>
</div>
```

---

## ✅ CSS ajusté pour matcher EasyAdmin (important)

```html
<style>
/* === Container === */
.users-kpi {
  margin-top: 1rem;
}

.users-kpi__title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #1f2937;
}

/* === Card === */
.users-kpi__card {
  background: #f9fafb; /* gris EA */
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* === Grid === */
.users-kpi__grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
  text-align: center;
}

/* === Items === */
.users-kpi__item-title {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

/* === Values === */
.users-kpi__value {
  font-size: 3rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.users-kpi__value--positive {
  color: #065f46; /* vert EA */
}

/* === Percent === */
.users-kpi__percent {
  font-size: 0.875rem;
  font-weight: 600;
}

.users-kpi__percent--negative {
  color: #b91c1c; /* rouge EA */
}

/* === Tooltip === */
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

## Ce que tu dois comprendre (et retenir)

* ❌ Le rendu “EA” n’est PAS neutre
* ❌ Tu ne peux pas l’avoir “gratuitement” sans importer ses styles
* ✅ Mais tu peux **t’en approcher visuellement sans dépendance**
* ✅ C’est ce qu’on vient de faire

Tu as maintenant :

* une carte **stable**
* qui **ne casse rien**
* qui **ressemble à l’admin**
* et que tu peux déplacer ailleurs sans surprise

---

## Dernière vérité (sans fioritures)

Tu étais bloqué parce que tu pensais que :

> “le style vient du base”

Alors qu’en réalité :

> **le style vient d’un écosystème CSS implicite**

Une fois que tu l’acceptes, tout devient plus simple.

Si tu veux, prochaine étape possible :

* harmoniser TOUTES les KPI cards avec ce mini-design system
* ou factoriser le CSS dans `assets/admin/kpi.css`
* ou refaire le tooltip sans inline `<style>`

Mais là, **on est enfin au bon niveau de compréhension**.
