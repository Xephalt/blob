Oui. Bonne demande. Là on fige une **version cohérente et isolée**, alignée avec **ce que ton infra fait vraiment** (endpoint dynamique + API externe), sans mélange ni dette cachée.

Je vais te donner **UNIQUEMENT** :

1. 🔧 le **Fake provider** mis à jour
2. 🌐 le **Vrai provider API** mis à jour
3. 📐 le **contrat (interface) final** sur lequel les deux s’alignent

Le tout **copiable-collable**, **cohérent**, **sans ambiguïté**.

---

# 🧩 0. Contrat final (PORT – référence unique)

📄 `src/Application/FileViewer/Port/FileStreamProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\FileViewer\Port;

interface FileStreamProvider
{
    /**
     * @param string $baseUrl      URL de base de l’API distante (résolue par EndpointRepository)
     * @param string $encodedPath  Path encodé en base64-url-safe (fourni par le frontend)
     */
    public function stream(string $baseUrl, string $encodedPath): iterable;
}
```

👉 **Ceci est la vérité**.
Tout le reste s’aligne là-dessus.

---

# 🧪 1. Fake provider (LOCAL, DEV, TEST)

📄 `src/Infrastructure/FileViewer/FakePdfStreamProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\FileViewer;

use App\Application\FileViewer\Port\FileStreamProvider;

final class FakePdfStreamProvider implements FileStreamProvider
{
    private const CHUNK_SIZE = 8192;

    public function stream(string $baseUrl, string $encodedPath): iterable
    {
        // Fake = on ignore totalement baseUrl et encodedPath
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

### ✔️ Ce que garantit ce fake

* même signature que le vrai provider
* aucune dépendance réseau
* aucun effet de bord
* jetable sans refactor

---

# 🌐 2. Provider API réel (PROD / INT)

📄 `src/Infrastructure/FileViewer/ApiPdfStreamProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\FileViewer;

use App\Application\FileViewer\Port\FileStreamProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiPdfStreamProvider implements FileStreamProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey
    ) {
    }

    public function stream(string $baseUrl, string $encodedPath): iterable
    {
        $url = rtrim($baseUrl, '/') . '/file';

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'path' => $encodedPath,
            ],
            'headers' => [
                'Accept' => 'application/pdf',
                'CommsGPT-Main-API-Key' => $this->apiKey,
            ],
            'buffer' => false, // streaming réel
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf('Failed to retrieve file: HTTP %d', $response->getStatusCode())
            );
        }

        foreach ($this->httpClient->stream($response) as $chunk) {
            if ($chunk->isTimeout()) {
                continue;
            }

            yield $chunk->getContent();
        }
    }
}
```

### ✔️ Points importants (et corrigés)

* ❌ **aucune construction d’URL dynamique ici**
* ❌ **aucune logique EndpointRepository ici**
* ✅ l’URL de base est **injectée par l’orchestrateur**
* ✅ le `path` est **déjà encodé**, jamais modifié
* ✅ streaming natif Symfony HttpClient

---

# 🧠 3. Pourquoi cette version est la bonne (résumé rapide)

* Le **contrôleur** :

  * résout l’endpoint dynamique
  * orchestre le use case
* Le **use case / handler** :

  * ne connaît que le port
* Le **provider** :

  * exécute un détail technique (IO / HTTP)
* Le **frontend** :

  * encode le path correctement (base64-url-safe)

👉 Chacun fait **exactement son job**.
👉 Rien n’est codé au mauvais endroit.

---

# ✅ 4. Tu es maintenant “ISO” comme tu dis

Avec cette base :

* fake ↔ api sont interchangeables
* l’erreur 404 n’est plus ambiguë
* l’encodage est **contractuellement clair**
* tu peux tester, brancher, switcher

Si tu veux, prochaine étape logique (au choix) :

* 🔁 adapter `OpenFileHandler` proprement (si pas déjà fait)
* 🧪 écrire un test d’intégration minimal
* 🔐 ajouter une validation base64 côté backend
* 🧹 cleanup avant PR

Mais **ce que tu demandais ici est fait** :
👉 fake + réel **propres, alignés, et isolés**.
