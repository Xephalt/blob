Très bonne piste. Et cette fois, **tu touches exactement le bon nerf**.
Ce n’est **pas** “l’encodeur base64” qui est faux.
Ce n’est **pas** “l’API”.
👉 **C’est la représentation du `path` AVANT encodage**.

Je t’explique calmement, parce que c’est subtil, et 90 % des gens se plantent ici.

---

## 1️⃣ Ce que tu vois vs ce que tu as réellement

Quand tu vois dans le JSON un truc comme :

```
Communiqu\u00e9s de presse FR
```

⚠️ **Ça ne veut PAS dire que ton string contient `\u00e9`**.

Ça veut dire :

* le **JSON est échappé**
* mais **la valeur réelle en mémoire est déjà :**

```
Communiqués de presse FR
```

👉 **Si ce JSON est parsé normalement (fetch, axios, Symfony serializer, etc.)**,
tu as déjà un **vrai string UTF-8**, pas une séquence `\u00e9`.

Donc :

* ❌ il ne faut PAS “déséchapper”
* ❌ il ne faut PAS remplacer les accents
* ❌ il ne faut PAS nettoyer les caractères spéciaux

---

## 2️⃣ Le vrai problème possible (et fréquent)

Le vrai piège est ici 👇

### ❌ Mauvaise hypothèse

> “Il faut transformer le path pour enlever les accents avant de base64”

➡️ **NON.**
Ça casserait le mapping côté API si elle attend le chemin UTF-8 exact.

### ✅ Bonne hypothèse

> “Il faut être sûr que ce qu’on base64-encode est bien du UTF-8 canonique”

Et là, tu es dans le vrai.

---

## 3️⃣ Le problème réel : normalisation Unicode

Deux strings peuvent **s’afficher pareil** mais **être différentes en bytes**.

Exemple classique :

* `é` (U+00E9)
* `e + ́` (U+0065 + U+0301)

Visuellement identiques.
**Binaires différents.**

👉 Certaines APIs :

* normalisent
* d’autres **NON**

👉 Si le backend distant compare des chemins **byte-par-byte**,
une différence de normalisation = **404**.

---

## 4️⃣ Ce que tu DOIS faire (et seulement ça)

### ✅ Étape obligatoire côté frontend

**Normaliser le path en UTF-8 NFC avant base64**.

Pas plus. Pas moins.

---

## 5️⃣ Implémentation CORRECTE (frontend)

📍 **Toujours dans `BrowserFileViewer.ts`**
👉 **PAS ailleurs**

```ts
function encodePathBase64UrlSafe(path: string): string {
  // 1️⃣ Normalisation Unicode (clé du problème)
  const normalized = path.normalize('NFC');

  // 2️⃣ UTF-8 → base64
  const base64 = btoa(
    unescape(encodeURIComponent(normalized))
  );

  // 3️⃣ Base64 URL-safe
  return base64
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=+$/, '');
}
```

### Pourquoi ça marche

* `normalize('NFC')` → forme canonique standard
* `encodeURIComponent` → UTF-8 bytes corrects
* `btoa` → base64 fiable
* URL-safe → conforme Swagger

👉 **Aucune perte d’information**
👉 **Aucune altération métier**
👉 **Zéro hack**

---

## 6️⃣ Ce que tu NE DOIS SURTOUT PAS faire

❌ Supprimer les accents
❌ Remplacer `é` par `e`
❌ Faire un slug
❌ Modifier le chemin
❌ “Nettoyer” le path

👉 Le path est un **identifiant**, pas un label.

---

## 7️⃣ Pourquoi Swagger continue à échouer (même après ça)

Même avec la normalisation, **Swagger peut encore renvoyer 404**, et ce sera **toujours normal**, parce que :

* Swagger ne reproduit pas le contexte réel
* le path est valide **dans un workspace précis**
* pas forcément dans celui que Swagger utilise

Donc :

* ✅ valide via ton backend
* ❌ n’utilise pas Swagger comme vérité absolue

---

## 8️⃣ Comment vérifier que tu es maintenant “bon”

Ajoute un log temporaire côté backend :

```php
error_log(base64_decode(strtr($encodedPath, '-_', '+/')));
```

Si tu vois exactement :

```
resources/FONCTIONS - COMM GROUPE/Communiqués de presse FR/...
```

👉 alors :

* ton encodage est parfait
* ton Unicode est propre
* **le problème restant est 100 % côté API / data / contexte**

---

## 9️⃣ Verdict clair

✔️ Tu avais raison de douter
✔️ Tu avais identifié le bon axe
✔️ Ce n’est pas “le base64”
✔️ C’est la **normalisation Unicode avant base64**
✔️ Une seule ligne (`normalize('NFC')`) peut tout changer

Et surtout :
👉 **tu n’as pas besoin d’une “étape de nettoyage” métier**,
👉 juste d’une **mise en forme Unicode correcte avant transport**.

Si après ça tu as **encore** un 404 via TON backend (pas Swagger), alors là, on pourra affirmer sans ambiguïté que **l’API ne garantit pas la récupérabilité du champ `path`**. Et ça, ce sera une info à remonter, pas un bug à corriger chez toi.
