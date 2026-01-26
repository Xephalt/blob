OK. Là on va arrêter de tourner autour du pot et **poser un diagnostic clair**, parce que tu as raison sur un point fondamental : **ce n’est plus un problème d’encodage seulement**. Et oui, **le contrôleur est incomplet par rapport à votre infra réelle**.

Je vais faire ça proprement, en trois parties :

1. **Pourquoi ton encodage “correct” continue de donner des 404**
2. **Ce qui manque réellement dans ton contrôleur (le vrai bug)**
3. **La version corrigée du contrôleur + règles d’encodage définitives**

Respire, c’est logique.

---

## 1️⃣ Diagnostic réel (et il est important)

Tu observes :

* même avec un `path` base64-url-safe
* même copié-collé depuis Swagger
* même avec une clé API valide

👉 **toujours un `404 Not Found` renvoyé par l’API distante**

Et le message clé est ici :

> `Failed to retrieve file: 401/404 Not Found for url https://.../rag-api-dev/.../file?path=...`

### 🔴 Indice critique

Dans ton **exemple fonctionnel existant** (`DownloadController`), tu ne tapes **jamais directement** une URL fixe.

Tu fais ceci :

1. Tu appelles un **EndpointRepository**
2. Il te retourne **l’URL dynamique active**
3. Tu concatènes ensuite le *vrai endpoint métier*

👉 **Ce mécanisme n’existe pas du tout dans ton `FileViewerController` actuel.**

Donc aujourd’hui tu fais :

```
https://…/rag-api-dev/.../file?path=XXX
```

Alors que tu devrais faire :

```
[getFinalRedirectedEndpoint()] + '/file?path=XXX'
```

➡️ **L’API que tu appelles n’est pas la bonne instance.**
➡️ **Le 404 est normal**, même avec un encodage parfait.

Ce n’est PAS :

* un bug Symfony
* un bug base64
* un bug front

C’est **un bug d’orchestration backend**.

---

## 2️⃣ Conclusion intermédiaire (importante)

> ❌ Le problème n’est PAS “comment encoder le path”
>
> ✅ Le problème est “où est-ce qu’on envoie la requête”

L’encodage **sera nécessaire**, mais **il n’est pas la cause actuelle du 404**.

---

## 3️⃣ Ce que le contrôleur DOIT faire (aligné avec votre existant)

Ton propre `DownloadController` montre la voie :

```php
$baseUrl = $this->endpointRepository->getFinalRedirectedEndpoint();
$url = $baseUrl . '/vector_store_report';
```

👉 **Le FileViewerController doit faire exactement la même chose.**

---

## 4️⃣ Contrôleur corrigé (version PROPRE et alignée)

Voici **la version corrigée** de ton `FileViewerController`, adaptée à ton infra réelle.

### 📄 `src/Controller/Api/FileViewerController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\FileViewer\OpenFileHandler;
use App\Application\FileViewer\OpenFileQuery;
use App\Repository\EndpointRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final class FileViewerController extends AbstractController
{
    public function __construct(
        private EndpointRepository $endpointRepository,
    ) {}

    #[Route('/api/v2/file', name: 'api_v2_file_view', methods: ['GET'])]
    public function __invoke(
        Request $request,
        OpenFileHandler $handler
    ): StreamedResponse {
        $encodedPath = (string) $request->query->get('path');

        if ($encodedPath === '') {
            throw $this->createNotFoundException('Missing file path');
        }

        // Résolution dynamique de l’endpoint (CRUCIAL)
        $baseUrl = $this->endpointRepository->getFinalRedirectedEndpoint();

        // On passe le path tel quel (déjà encodé côté front)
        $query = new OpenFileQuery(
            $baseUrl . '/file',
            $encodedPath
        );

        $stream = $handler->handle($query);

        return new StreamedResponse(
            function () use ($stream) {
                foreach ($stream as $chunk) {
                    echo $chunk;
                    flush();
                }
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="source.pdf"',
            ]
        );
    }
}
```

> ⚠️ Oui, ça implique que `OpenFileQuery` prenne maintenant **l’URL cible + le path**
> C’est **normal**, tu passes d’un fake local à une vraie API distribuée.

---

## 5️⃣ Provider réel : petite correction conceptuelle

Ton provider **doit appeler une URL déjà résolue**, pas la construire lui-même.

### Interface attendue (concept)

```php
interface FileStreamProvider
{
    public function stream(string $url, string $encodedPath): iterable;
}
```

➡️ Le provider **ne connaît pas EndpointRepository**
➡️ Le contrôleur orchestre
➡️ L’Application décide
➡️ L’Infra exécute

---

## 6️⃣ Et l’encodage, alors ? (règle finale)

### ✅ Règle définitive

* **Frontend** :

  * encode en **base64-url-safe**
* **Symfony** :

  * ne touche PAS au path
* **API distante** :

  * décode base64-url-safe

Si le Swagger dit :

> `Encoded path (base64-url-safe)`

👉 **Tu fais exactement ça**, et tu es bon.

### ❌ Ce que tu ne dois plus tester

* copier-coller un path brut dans Swagger
* tester sans passer par la bonne instance d’API
* changer l’encodage tant que l’URL est fausse

---

## 7️⃣ Résumé brutal (mais juste)

* ❌ Tu appelais la mauvaise API → 404 normal
* ❌ L’encodage n’était PAS le vrai problème
* ✅ Il faut résoudre l’endpoint dynamiquement
* ✅ Ton exemple existant était la clé
* ✅ Une fois corrigé, ton base64-url-safe sera accepté

Tu as eu le bon instinct **depuis le début**, mais il manquait **une pièce d’infra**, pas une transformation de string.

Si tu veux, prochaine étape possible :

* adapter `OpenFileHandler` proprement
* sécuriser le flux
* écrire un test d’intégration avec un endpoint mocké

Mais là, on a identifié **le vrai bug**, et ce n’était pas toi.
