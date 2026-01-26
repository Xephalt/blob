Très bien, demande raisonnable. On ne touche **ni à l’archi**, ni au flux, on ajoute juste un **feedback visuel explicite** pour que ton cerveau arrête de douter à chaque clic. Debug UX, pas feature produit.

Je t’explique d’abord **la logique**, puis je te donne **le fichier complet modifié**, prêt à coller.

---

## 🎯 Logique d’affichage des icônes (claire et déterministe)

À côté de l’icône “arrow-up-right-from-square” (qui reste l’icône d’action), on ajoute **une icône d’état** :

| Cas                 | Condition                 | Icône      | Sens                        |
| ------------------- | ------------------------- | ---------- | --------------------------- |
| URL dispo           | `item.url`                | ⭐ étoile   | Source web classique        |
| Pas d’URL mais path | `!item.url && item.path`  | 📄 fichier | PDF reconstruit via `/file` |
| Rien                | `!item.url && !item.path` | ❌ croix    | Source non consultable      |

Ces icônes :

* sont **purement visuelles**
* n’impactent **aucune logique**
* pourront être supprimées plus tard sans effet de bord

---

## 🧠 Pourquoi c’est bien fait

* Le **clic reste unique**
* L’icône reflète **la décision qui sera prise**
* Tu vois instantanément **quel chemin est utilisé**
* Zéro dette technique ajoutée

---

## 📄 FICHIER COMPLET — `SourceDetails.tsx` (avec icônes)

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

  const renderSourceStateIcon = () => {
    if (item.url) {
      // Source web
      return <i className="fa-solid fa-star ms-2 text-yellow-500" />;
    }

    if (item.path) {
      // PDF via /file
      return <i className="fa-solid fa-file-pdf ms-2 text-red-600" />;
    }

    // Non consultable
    return <i className="fa-solid fa-xmark ms-2 text-gray-400" />;
  };

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
            className="bnpp-color-green hover:underline break-all text-left flex items-center"
          >
            {item.title}

            {/* Icône d’action */}
            <i
              role="presentation"
              className="ps-3 fa-solid fa-arrow-up-right-from-square"
            />

            {/* Icône d’état */}
            {renderSourceStateIcon()}
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

## ✅ Résultat visuel attendu

* ⭐ + flèche → clic = URL web
* 📄 + flèche → clic = `/api/v2/file`
* ❌ + flèche → rien ne se passe (normal, pas de source)

Tu peux maintenant :

* vérifier en live que **tes données backend sont cohérentes**
* spotter immédiatement les cas foireux
* supprimer ces icônes plus tard **sans toucher à la logique**

---

Si tu veux ensuite :

* remplacer les icônes FontAwesome par des SVG maison
* griser le bouton quand ❌
* ou logguer les clics par type de source

Dis-le.
Là, tu as un **debug visuel propre**, pas un bricolage.
