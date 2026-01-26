Parfait. Là on n’est plus dans le gloubiboulga, on est dans du **refactoring sous contrainte réelle**. Et oui, tu as raison sur un point clé :
👉 **tu ne veux pas “nettoyer le monde”**, tu veux **ajouter une feature propre sans casser le reste**. C’est exactement comme ça qu’on introduit de la Clean Archi dans un projet vivant. Pas autrement.

Je vais donc faire trois choses, dans l’ordre logique d’un humain fatigué mais compétent :

1. **Diagnostiquer honnêtement l’état actuel de ton projet** (front + back)
2. **Définir une cible Clean Architecture réaliste**, compatible avec l’existant
3. **Te dire exactement où créer quoi**, fichier par fichier, sans déplacer le legacy

Zéro dogme, zéro big-bang, zéro “on réécrit tout”.

---

## 1️⃣ Diagnostic réaliste de ton projet

### Backend (Symfony)

* `src/Controller/*` = **MVC classique**, très couplé, mais fonctionnel
* `src/Application/` = **embryon de Clean Archi**, déjà une excellente porte d’entrée
* Le domaine est **anémique** (Entities Doctrine), mais on s’en fout pour cette feature
* Tu **ne maîtrises pas** l’endpoint `/api/v2/file` (API externe) → très bien, ça force un bon découplage

👉 Conclusion backend
Tu peux **ajouter une vraie couche Application propre**, sans toucher aux contrôleurs existants, **en façade**.

---

### Frontend (React)

* Architecture **feature-oriented**, pas clean archi formelle
* Beaucoup de logique HTTP dans les hooks (`useGetMessages`, etc.)
* Les composants (`SourceDetails.tsx`) font déjà trop de choses, mais on ne va pas les réécrire

👉 Conclusion frontend
On va **introduire une micro-clean-archi locale**, limitée à la feature “ouvrir une source PDF”.

---

## 2️⃣ Principe directeur (important)

> **On ne déplace rien. On n’efface rien.
> On ajoute une feature propre, isolée, branchée au dernier moment.**

Donc :

* ❌ Pas de refactor massif
* ❌ Pas de renommage global
* ❌ Pas de réorganisation de dossiers existants
* ✅ Ajout de nouveaux dossiers
* ✅ Injection par les bords

---

## 3️⃣ Découpage CIBLE – Vue d’ensemble

### Backend (Symfony)

```
src/
 └── Application/
     └── FileViewer/
         ├── OpenFileQuery.php
         ├── OpenFileHandler.php
         ├── Port/
         │    └── FileStreamProvider.php
         └── Dto/
              └── FileDescriptor.php
```

👉 **Aucun contrôleur Symfony ici**
👉 **Aucune dépendance HTTP ici**

Le contrôleur Symfony futur fera juste :

```
HTTP → Application → Infra
```

---

### Frontend (React)

```
assets/
 └── core/
     └── file-viewer/
         ├── domain/
         │    └── SourceDocument.ts
         ├── application/
         │    ├── OpenSourceDocument.ts
         │    └── FileViewerPort.ts
         └── infrastructure/
              └── BrowserFileViewer.ts
```

Et ton code existant (`SourceDetails.tsx`) :
👉 **appelle le use case**, point.

---

## 4️⃣ Backend – Où placer quoi exactement

### 📁 `src/Application/FileViewer/`

Pourquoi ici ?

* Tu as déjà `Application/Admin`
* `FileViewer` est **un cas transverse**, pas admin
* On respecte ton intention initiale

---

### `OpenFileQuery.php`

```php
final class OpenFileQuery
{
    public function __construct(
        public readonly string $path
    ) {}
}
```

➡️ Représente **l’intention métier**
➡️ Aucun HTTP, aucun Symfony

---

### `FileStreamProvider.php` (PORT)

```php
interface FileStreamProvider
{
    public function stream(string $path): iterable;
}
```

➡️ L’API externe implémentera ça plus tard
➡️ Pour l’instant : mockable / fakeable

---

### `OpenFileHandler.php`

```php
final class OpenFileHandler
{
    public function __construct(
        private FileStreamProvider $provider
    ) {}

    public function handle(OpenFileQuery $query): iterable
    {
        return $this->provider->stream($query->path);
    }
}
```

➡️ Le handler **ne sait rien** de PDF, HTTP ou Symfony
➡️ Il orchestre, c’est tout

---

### Quand l’endpoint `/file` arrivera

Tu ajouteras **un contrôleur Symfony très fin** :

```
Controller → Query → Handler → Provider → Response(stream)
```

Sans toucher à l’application.

---

## 5️⃣ Frontend – Clean Archi locale, sans douleur

### Problème actuel

Dans `SourceDetails.tsx`, tu as ça :

```tsx
<a href={item.url} target="_blank">
```

C’est :

* du HTTP
* du métier
* du UI
* tout mélangé

On **ne supprime pas**, on **dévie**.

---

## 6️⃣ Nouveau code Frontend (à ajouter)

### 📁 `assets/core/file-viewer/domain/SourceDocument.ts`

```ts
export interface SourceDocument {
  title: string;
  path: string;
}
```

---

### 📁 `application/FileViewerPort.ts`

```ts
export interface FileViewerPort {
  open(path: string): void;
}
```

---

### 📁 `application/OpenSourceDocument.ts`

```ts
export class OpenSourceDocument {
  constructor(private viewer: FileViewerPort) {}

  execute(path: string) {
    this.viewer.open(path);
  }
}
```

---

### 📁 `infrastructure/BrowserFileViewer.ts`

```ts
export class BrowserFileViewer implements FileViewerPort {
  open(path: string) {
    const encoded = encodeURIComponent(path);
    window.open(`/api/v2/file?path=${encoded}`, '_blank');
  }
}
```

➡️ Toute la merde HTTP est **ici**, confinée, assumée

---

## 7️⃣ Branchement MINIMAL dans `SourceDetails.tsx`

Tu **ne refactors pas** le composant. Tu ajoutes juste :

```ts
const fileViewer = new BrowserFileViewer();
const openSourceDocument = new OpenSourceDocument(fileViewer);
```

Et tu remplaces le lien :

```tsx
<button
  onClick={() => openSourceDocument.execute(item.path)}
  className="bnpp-color-green hover:underline"
>
  {item.title}
</button>
```

➡️ Le composant **ne sait pas** :

* ce qu’est `/file`
* ce qu’est un PDF
* ce qu’est un stream

---

## 8️⃣ Ce que tu peux faire MAINTENANT (sans l’API)

✔️ Créer toute l’arborescence
✔️ Écrire tous les use cases
✔️ Mettre un fake `BrowserFileViewer` qui log
✔️ Adapter l’UI pour utiliser `path`
✔️ Préparer le handler backend

Quand l’endpoint arrive :
👉 **tu branches, point**

---

## 9️⃣ Règles que je te propose (et que tu peux refuser)

Je te demande juste validation sur ces points :

1. Le `path` est **opaque et non typé**
2. L’ouverture se fait **toujours via le navigateur**
3. Pas d’iframe interne pour l’instant
4. Pas de preview page par page
5. Pas de téléchargement forcé

Si un de ces points est faux, dis-le maintenant. Sinon, cette archi est **safe pour le taf**, défendable en review, et extensible.

---

## Conclusion honnête

Tu fais exactement ce qu’un bon dev fait en entreprise :

* tu respectes l’existant
* tu ajoutes proprement
* tu prépares l’avenir sans le forcer

Ce n’est pas “pas clean archi”.
C’est **la seule clean archi qui marche en vrai**.

Si tu veux, au prochain message, on peut :

* écrire le **fake provider backend**
* écrire un **test minimal du use case frontend**
* préparer le **commit plan** pour que ça passe crème en PR

Et non, tu n’es pas en retard. Tu fais juste les choses dans le bon ordre.
