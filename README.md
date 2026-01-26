Oui, normal que ça soit passé au rouge. Et non, tu n’as rien “cassé” tout seul.
Tu viens juste de toucher **au contrat du port**, donc **le handler DOIT être réaligné**. C’est mécanique.

Je vais être très clair, très court, et te donner **uniquement ce qui doit changer**, fichiers complets inclus. Rien d’autre.

---

# 🧠 Ce qui s’est passé (en une phrase)

Avant :

```php
FileStreamProvider::stream(string $path)
```

Maintenant :

```php
FileStreamProvider::stream(string $baseUrl, string $encodedPath)
```

👉 **Donc tout code qui appelait `stream($path)` doit maintenant fournir DEUX arguments.**
C’est exactement ce que te dit PHP, et il a raison.

---

# 🎯 Ce qui DOIT changer (et seulement ça)

| Fichier                    | Action          |
| -------------------------- | --------------- |
| `OpenFileQuery.php`        | ✅ DOIT évoluer  |
| `OpenFileHandler.php`      | ✅ DOIT évoluer  |
| `FileViewerController.php` | ❌ NE BOUGE PAS  |
| DTO / Front                | ❌ NE BOUGE PAS  |
| Fake provider              | déjà corrigé ✔️ |

---

# 1️⃣ OpenFileQuery — version corrigée (COMPLÈTE)

📄 `src/Application/FileViewer/OpenFileQuery.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\FileViewer;

final class OpenFileQuery
{
    public function __construct(
        private string $baseUrl,
        private string $encodedPath
    ) {}

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function encodedPath(): string
    {
        return $this->encodedPath;
    }
}
```

👉 Le Query transporte **les deux infos nécessaires**.
👉 Il ne fait **aucune logique**.
👉 Il est immutable.
👉 Il est propre.

---

# 2️⃣ OpenFileHandler — version corrigée (COMPLÈTE)

📄 `src/Application/FileViewer/OpenFileHandler.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\FileViewer;

use App\Application\FileViewer\Port\FileStreamProvider;

final class OpenFileHandler
{
    public function __construct(
        private FileStreamProvider $fileStreamProvider
    ) {}

    public function handle(OpenFileQuery $query): iterable
    {
        return $this->fileStreamProvider->stream(
            $query->baseUrl(),
            $query->encodedPath()
        );
    }
}
```

👉 **C’est TOUT.**
👉 Pas de logique métier ajoutée.
👉 Juste un réalignement du contrat.

---

# 3️⃣ Pourquoi le contrôleur et les DTO ne changent PAS

Tu l’as très bien senti, et tu avais raison.

Le contrôleur fait déjà :

```php
$query = new OpenFileQuery(
    $baseUrl . '/file',
    $encodedPath
);
```

Donc :

* il connaît l’endpoint dynamique ✔️
* il connaît le path encodé ✔️
* il orchestre ✔️

👉 **Aucune raison de le toucher.**

---

# 4️⃣ Règle mentale pour la suite (à retenir)

> Quand tu changes un **port**,
> **le handler et le query suivent**,
> **le contrôleur orchestre**,
> **le front ne bouge pas**.

C’est exactement ce que tu viens de vivre.
Et c’est sain.

---

# ✅ Résumé final (sans détour)

* Oui, le rouge est normal
* Oui, tu dois modifier `OpenFileQuery`
* Oui, tu dois modifier `OpenFileHandler`
* Non, tu ne touches pas au reste
* Oui, le fake provider est déjà OK
* Oui, maintenant tout est cohérent

Tu n’as pas fait une erreur.
Tu as **fait évoluer un contrat**, et tu l’as fait proprement.

Si tu veux, prochaine étape possible :

* ajouter une validation de query
* ajouter un test unitaire du handler
* ou figer l’API avant PR

Mais là, **le build doit repasser au vert**.
