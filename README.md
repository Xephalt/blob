OK. On se calme, on range les outils, et on remet **un plan clair**, parce que là tu as compris le *quoi* mais tu veux le *où* et le *comment* sans improviser. Et tu as raison.

Je vais te donner **des instructions opérationnelles**, **dans l’ordre**, **avec les fichiers exacts**, et **sans rajouter d’architecture inutile**. Rien de plus, rien de moins.

---

# 🎯 OBJECTIF (rappel simple)

* Le **frontend** doit :

  * encoder le `path` en **base64-url-safe**
  * appeler `/api/v2/file?path=ENCODED`
* Le **backend Symfony** doit :

  * recevoir `path`
  * le **forward tel quel** à l’API externe
  * streamer la réponse PDF

👉 **L’encodage NE DOIT PAS être fait dans Symfony**.
👉 **L’encodage DOIT être fait côté navigateur**.

---

# 🧱 OÙ mettre QUOI (réponse courte)

| Responsabilité                 | Fichier                    | Action           |
| ------------------------------ | -------------------------- | ---------------- |
| Encodage base64-url-safe       | `BrowserFileViewer.ts`     | ✅ À FAIRE        |
| Logique métier “ouvrir un doc” | `OpenSourceDocument.ts`    | ❌ NE PAS TOUCHER |
| UI (bouton)                    | `SourceDetails.tsx`        | ❌ NE PAS TOUCHER |
| Réception HTTP                 | `FileViewerController.php` | ❌ NE PAS TOUCHER |
| Appel API externe              | `ApiPdfStreamProvider.php` | ❌ NE PAS TOUCHER |

👉 **UN SEUL fichier à modifier côté front**.

---

# 1️⃣ Fichier concerné côté frontend (LE point clé)

📄 **`assets/core/file-viewer/infrastructure/BrowserFileViewer.ts`**

C’est **le seul endroit légitime** pour :

* construire une URL navigateur
* gérer les contraintes de transport
* faire de l’encodage technique

Pourquoi ?

* ce n’est PAS de l’UI
* ce n’est PAS du métier
* c’est de l’**infrastructure navigateur**

Donc c’est exactement le bon endroit.

---

# 2️⃣ Ce que doit contenir `BrowserFileViewer.ts`

### Version propre et finale du fichier

```ts
export class BrowserFileViewer {
  open(path: string): void {
    const encodedPath = encodePathBase64UrlSafe(path);
    const url = `/api/v2/file?path=${encodedPath}`;

    window.open(url, '_blank');
  }
}

/**
 * Encode un path en base64 URL-safe
 * conforme aux attentes de l'API /file
 */
function encodePathBase64UrlSafe(path: string): string {
  const base64 = btoa(unescape(encodeURIComponent(path)));

  return base64
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=+$/, '');
}
```

✅ Copiable-collable
✅ Isolé
✅ Testable
✅ Jetable si besoin

---

# 3️⃣ Ce que tu NE DOIS PAS faire (important)

❌ Ne pas encoder dans :

* `SourceDetails.tsx`
* `OpenSourceDocument.ts`
* un hook React
* Symfony
* le provider backend

Sinon tu :

* casses la séparation des responsabilités
* risques un double encodage
* rends le code incompréhensible dans 3 mois

---

# 4️⃣ Pourquoi ce placement est le bon (logique simple)

Pose-toi cette question :

> “Est-ce que l’encodage base64-url-safe est une règle métier ?”

➡️ Non.

> “Est-ce que l’encodage dépend du fait que j’ouvre un PDF dans un navigateur ?”

➡️ Oui.

👉 Donc **Infrastructure navigateur**, point.

---

# 5️⃣ Côté backend : rien à faire (et c’est une bonne nouvelle)

Tu laisses **strictement** ce que tu as déjà :

```php
$path = (string) $request->query->get('path');
```

Et dans le provider :

```php
'query' => [
    'path' => $path,
],
```

Symfony ne :

* décode pas
* réencode pas
* transforme pas

Il transporte. C’est tout.

---

# 6️⃣ Checklist finale pour toi (pas de blabla)

* [ ] Modifier **uniquement** `BrowserFileViewer.ts`
* [ ] Ajouter `encodePathBase64UrlSafe`
* [ ] Utiliser cette fonction dans `open()`
* [ ] Tester avec un `path` réel depuis le RAG
* [ ] Vérifier dans Network que `path=` est base64-url-safe
* [ ] Laisser le backend inchangé

Si tout ça est OK → **c’est terminé**.

---

# 7️⃣ Mot de fin (clair, sans flatterie)

Tu étais déjà sur la bonne piste.
Tu avais juste besoin qu’on te dise **où poser le dernier clou**, pas de refaire la charpente.

Maintenant :

* l’encodage est au bon endroit
* l’API externe est respectée
* l’archi est propre
* le ticket est défendable

Tu peux avancer sans crainte.
Et non, tu n’as rien “raté”. Tu as juste voulu bien faire.
