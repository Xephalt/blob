D’accord. Tu veux **le fichier complet**, pas un patch à trous, pas un “remplace ça par ça”.
Donc voilà **`SourceDetails.tsx` en entier**, cohérent, lisible, et surtout **avec une logique de clic correcte et unique**.

Je pars de **ton fichier tel qu’il apparaît sur la capture**, je n’invente rien, je ne “nettoie” pas au-delà de ce qui est strictement nécessaire pour que ça marche.

---

## 📄 `assets/components/chat/SourceDetails.tsx`

```tsx
import React, { useRef } from 'react';

import { Source, SelectedSourceType } from '../../Interfaces';

import { OpenSourceDocument } from '../../core/file-viewer/application/OpenSourceDocument';
import { BrowserFileViewer } from '../../core/file-viewer/infrastructure/BrowserFileViewer';

interface Props {
  sources: Source;
  selectedSource: SelectedSourceType | undefined;
  onClose: () => void;
}

const SourceDetails = ({ sources, selectedSource, onClose }: Props) => {
  const fileViewer = new BrowserFileViewer();
  const openSourceDocument = new OpenSourceDocument(fileViewer);

  const bottomOfSource = useRef<HTMLDivElement>(null);

  let item: any;

  if (selectedSource?.sourceType === 'attachment') {
    item = sources.attachments?.[selectedSource.index];
  } else if (selectedSource?.sourceType === 'rag') {
    item = sources.rag?.[selectedSource.index];
  }

  if (!selectedSource || !item) {
    return null;
  }

  return (
    <div className="flex border bg-white rounded p-4 flex-col gap-3 text-sm ms-12">
      <div className="flex justify-between">
        <div className="flex gap-2 items-center">
          <span>Source :</span>

          {/* POINT DE CLIC UNIQUE */}
          <button
            type="button"
            onClick={() => {
              if (item.url) {
                window.open(item.url, '_blank');
              } else if (item.path) {
                openSourceDocument.execute(item.path);
              }
            }}
            className="bnpp-color-green hover:underline break-all text-left"
          >
            {item.title}
            <i
              role="presentation"
              className="ps-3 fa-solid fa-arrow-up-right-from-square"
            />
          </button>
        </div>

        <button
          type="button"
          className="rounded-full hover:bg-gray-100 px-2 py-1 transition"
          onClick={onClose}
        >
          <i role="presentation" className="fa-solid fa-xmark" />
        </button>
      </div>

      {Array.isArray(item.details) &&
        item.details.map((src: any, index: number) => (
          <div
            key={index}
            className="w-full flex flex-col bnpp-gray-bg rounded-md p-4"
          >
            <span className="uppercase">P. {src.page}</span>
            <p className="break-words">{src.excerpt}</p>
          </div>
        ))}

      <div ref={bottomOfSource} />
    </div>
  );
};

export default SourceDetails;
```

---

## ✅ Ce que ce fichier fait maintenant (sans ambiguïté)

* **Un seul élément cliquable** pour la source
* Priorité claire :

  1. `item.url` → ouverture directe (legacy, inchangé)
  2. `item.path` → `/api/v2/file` via le use case
* **Aucun bouton fantôme**
* **Aucun cas où l’utilisateur ne peut pas cliquer**
* Clean Archi respectée :

  * le composant décide **quand**
  * le use case décide **comment**

---

## Pourquoi c’est important (et pas juste “plus propre”)

Avant :

* 2 points de clic concurrents
* 1 jamais visible
* 1 logique implicite et bancale

Maintenant :

* **1 intention = 1 action**
* lisible en 10 secondes
* explicable en review sans dessin

---

Si tu veux, prochaine étape logique :

* typer `item` correctement (`RagSource | AttachmentSource`)
* extraire la logique de clic dans une fonction pure
* préparer le jour où `url` disparaît complètement

Mais là, honnêtement :
👉 **tu peux commit sans rougir**, et retourner bosser sans que quelqu’un te demande “mais ce bouton sert à quoi ?”.
