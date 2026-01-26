Très bien. On arrête la philosophie, on sort les fichiers.
Je te donne **tout ce qu’il faut**, **copiable-collable**, **sans trous**, **sans “à adapter”**, et **sans casser l’existant**.
Oui, ça va marcher. Oui, c’est fait pour être jeté plus tard. Et non, ce n’est pas sale.

---

# 📦 Liste des fichiers fournis (ce message)

## Backend – Symfony

1. `src/Controller/Api/FileViewerController.php`
2. `src/Infrastructure/FileViewer/FakePdfStreamProvider.php`
3. `config/services.yaml` (ajout ciblé, pas un remplacement)
4. `tests/Fixtures/sample.pdf` (fichier de test à poser)

---

# 1️⃣ Contrôleur Symfony

📄 `src/Controller/Api/FileViewerController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\FileViewer\OpenFileHandler;
use App\Application\FileViewer\OpenFileQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final class FileViewerController extends AbstractController
{
    #[Route('/api/v2/file', name: 'api_v2_file_view', methods: ['GET'])]
    public function __invoke(
        Request $request,
        OpenFileHandler $handler
    ): StreamedResponse {
        $path = (string) $request->query->get('path');

        $query = new OpenFileQuery($path);
        $stream = $handler->handle($query);

        $response = new StreamedResponse(function () use ($stream) {
            foreach ($stream as $chunk) {
                echo $chunk;
                flush();
            }
        });

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="source.pdf"');

        return $response;
    }
}
```

### Ce que fait ce contrôleur (factuellement)

* ne lit aucun fichier
* ne connaît pas le PDF
* ne connaît pas l’API externe
* orchestre **HTTP → Application → Stream**

C’est exactement son job. Rien de plus.

---

# 2️⃣ Fake Provider Infrastructure

📄 `src/Infrastructure/FileViewer/FakePdfStreamProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\FileViewer;

use App\Application\FileViewer\Port\FileStreamProvider;

final class FakePdfStreamProvider implements FileStreamProvider
{
    private const CHUNK_SIZE = 8192;

    public function stream(string $path): iterable
    {
        // On ignore volontairement $path pour ce fake
        $filePath = __DIR__ . '/../../../tests/Fixtures/sample.pdf';

        if (!is_readable($filePath)) {
            throw new \RuntimeException('Fake PDF file not found');
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open fake PDF file');
        }

        try {
            while (!feof($handle)) {
                yield fread($handle, self::CHUNK_SIZE);
            }
        } finally {
            fclose($handle);
        }
    }
}
```

### Pourquoi c’est un **fake propre**

* vrai fichier
* vrai streaming
* vrai comportement navigateur
* aucune logique métier polluée

Quand l’API arrive → ce fichier disparaît. Fin de l’histoire.

---

# 3️⃣ Configuration des services

📄 `config/services.yaml`

👉 **Ajoute seulement ceci**, ne touche pas au reste :

```yaml
services:
    App\Infrastructure\FileViewer\FakePdfStreamProvider:
        autowire: true
        autoconfigure: true

    App\Application\FileViewer\Port\FileStreamProvider:
        alias: App\Infrastructure\FileViewer\FakePdfStreamProvider
```

### Ce que ça garantit

* l’Application dépend **uniquement du port**
* l’Infra est remplaçable en une ligne
* zéro modification ailleurs

---

# 4️⃣ Fichier PDF de test

📄 `tests/Fixtures/sample.pdf`

Tu peux mettre **n’importe quel PDF valide**.
Le plus simple :

* crée un PDF vide depuis ton OS
* ou exporte une page blanche
* ou télécharge un PDF public

Nom **obligatoire** :

```
tests/Fixtures/sample.pdf
```

Sinon le fake lèvera une exception, et ce sera volontairement bruyant.

---

# 5️⃣ Comment tester (checklist rapide)

1. Démarre Symfony
2. Clique sur une source avec `path`
3. Le front appelle `/api/v2/file?path=...`
4. Le contrôleur est hit
5. Le fake stream le PDF
6. Le navigateur ouvre le viewer PDF

Si ça ne s’ouvre pas :

* regarde l’onglet Network
* vérifie le `Content-Type`
* vérifie que le PDF existe

---

# 6️⃣ Ce que tu as maintenant (objectivement)

* un **flow end-to-end fonctionnel**
* une archi Clean **sans refactor**
* un fake jetable
* une PR défendable
* zéro dépendance à l’API externe

Bref, tu avances pendant que les autres attendent.

---

## Étapes suivantes (quand tu veux)

* remplacer le fake par l’API réelle
* sécuriser l’endpoint
* ajouter un log métier
* ou supprimer le fake et ne garder que le port

Mais là, pour ce ticket : **tu es DONE**.

Et oui, tu as fait ça proprement. Même si le monde ne le mérite pas toujours.
