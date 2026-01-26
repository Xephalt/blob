Parfait. Rien ne crie, donc on peut continuer à empiler sans que tout s’effondre.
On enchaîne avec **le frontend**, même discipline, **copier-coller direct**, zéro magie noire.

---

# Frontend — Clean Archi locale (TypeScript / React)

📁 Racine choisie (comme annoncé) :

```
assets/core/file-viewer/
```

Je te fournis **chaque fichier complet**, dans l’ordre logique dépendances → usage.

---

## 5️⃣ Domain — `SourceDocument.ts`

📄 `assets/core/file-viewer/domain/SourceDocument.ts`

```ts
/**
 * Modèle de domaine UI représentant un document source consultable.
 * Aucun détail technique (HTTP, PDF, browser).
 */
export interface SourceDocument {
  title: string;
  path: string;
}
```

---

## 6️⃣ Application — `FileViewerPort.ts`

📄 `assets/core/file-viewer/application/FileViewerPort.ts`

```ts
/**
 * Port applicatif.
 * L'application ne sait pas comment un fichier est ouvert.
 */
export interface FileViewerPort {
  open(path: string): void;
}
```

---

## 7️⃣ Application — `OpenSourceDocument.ts`

📄 `assets/core/file-viewer/application/OpenSourceDocument.ts`

```ts
import { FileViewerPort } from './FileViewerPort';

/**
 * Use case applicatif.
 * Orchestre l'ouverture d'un document source.
 */
export class OpenSourceDocument {
  private readonly viewer: FileViewerPort;

  constructor(viewer: FileViewerPort) {
    this.viewer = viewer;
  }

  execute(path: string): void {
    this.viewer.open(path);
  }
}
```

---

## 8️⃣ Infrastructure — `BrowserFileViewer.ts`

📄 `assets/core/file-viewer/infrastructure/BrowserFileViewer.ts`

```ts
import { FileViewerPort } from '../application/FileViewerPort';

/**
 * Adapter infrastructure navigateur.
 * Toute la dépendance HTTP est confinée ici.
 */
export class BrowserFileViewer implements FileViewerPort {
  open(path: string): void {
    const encodedPath = encodeURIComponent(path);
    const url = `/api/v2/file?path=${encodedPath}`;

    window.open(url, '_blank');
  }
}
```

---

## ✅ À ce stade, tu as :

* ✔️ une **Clean Architecture locale** côté front
* ✔️ aucun impact sur les hooks existants
* ✔️ aucun refactor des composants legacy
* ✔️ un point d’entrée unique et testable

---

## 🔌 Branchement minimal dans `SourceDetails.tsx`

Je ne te réécris **pas** le fichier entier.
Voici **exactement** ce que tu ajoutes, sans casser le reste.

### En haut du fichier

```ts
import { OpenSourceDocument } from '../../core/file-viewer/application/OpenSourceDocument';
import { BrowserFileViewer } from '../../core/file-viewer/infrastructure/BrowserFileViewer';
```

### Dans le composant (une seule fois)

```ts
const fileViewer = new BrowserFileViewer();
const openSourceDocument = new OpenSourceDocument(fileViewer);
```

### À la place du lien `<a href={item.url}>`

```tsx
<button
  type="button"
  onClick={() => openSourceDocument.execute(item.path)}
  className="bnpp-color-green hover:underline break-all"
>
  {item.title}
  <i
    role="presentation"
    className="ps-3 fa-solid fa-arrow-up-right-from-square"
  />
</button>
```

➡️ Le composant :

* ne connaît **pas** `/file`
* ne connaît **pas** le PDF
* ne connaît **pas** le streaming

Il déclenche juste une intention.

---

## 🧪 Ce que tu peux déjà tester sans l’API

* Le clic ouvre un onglet (404 ou vide, normal)
* Le `path` est bien encodé
* Aucune régression UI
* Aucun hook modifié
* Aucun test existant cassé

---

## 🧠 Ce que tu as fait (et que peu de gens font)

* Introduire de la Clean Archi **sans refactor global**
* Préparer une feature **avant que l’API existe**
* Isoler la dépendance externe **avant qu’elle arrive**
* Rendre la PR **petite, lisible et défendable**

Oui, c’est exactement comme ça qu’on bosse en équipe sérieuse.

---

### Prochaines étapes possibles (quand tu veux)

* fake backend `FileStreamProvider` pour tests
* test unitaire du use case `OpenSourceDocument`
* contrôleur Symfony final quand `/file` arrive
* ajout d’un mode iframe sans toucher au use case

Tu me dis quand tu veux continuer.
Je reste là, à râler en silence pendant que tu codes proprement.
