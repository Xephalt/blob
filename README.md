Je relis tous les fichiers originaux depuis le transcript avant de les modifier.​​​​​​​​​​​​​​​​

J’ai tout. Je génère les 6 fichiers modifiés.​​​​​​​​​​​​​​​​

8 fichiers générés. N’oublie pas le SQL à exécuter dans phpMyAdmin :

ALTER TABLE announcement_popup 
ADD COLUMN title_fr VARCHAR(255) DEFAULT NULL,
ADD COLUMN content_fr LONGTEXT DEFAULT NULL;


Résumé des changements par fichier :
	∙	AnnouncementPopup.php — titleFr et contentFr dans constructeur, create(), reconstitute(), update() et getters
	∙	DoctrineAnnouncementPopupRepository.php — title_fr et content_fr dans save() et hydrate()
	∙	AnnouncementPopupController.php — expose titleFr et contentFr dans le JSON
	∙	AnnouncementPopupFormType.php — 2 nouveaux champs CKEditor (titleFr, contentFr)
	∙	AnnouncementPopupAdminController.php — passe titleFr/contentFr dans create() et update()
	∙	AnnouncementPopup.ts — interface TypeScript mise à jour
	∙	AnnouncementPopupApi.ts — pas de mapping nécessaire, le backend renvoie déjà en camelCase
	∙	AnnouncementPopupModal.tsx — useTranslation + fallback EN si pas de traduction FR​​​​​​​​​​​​​​​​

<?php

namespace App\Domain\Announcement;

final class AnnouncementPopup
{
    private function __construct(
        private readonly string $id,
        private string $title,
        private ?string $titleFr,
        private string $content,
        private ?string $contentFr,
        private ?string $imageUrl,
        private bool $isActive,
        private int $priority,
    ) {
    }

    public static function create(
        string $id,
        string $title,
        string $content,
        ?string $imageUrl,
        int $priority,
        ?string $titleFr = null,
        ?string $contentFr = null,
    ): self {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (trim($content) === '') {
            throw new \InvalidArgumentException('Content cannot be empty');
        }

        if ($priority < 0) {
            throw new \InvalidArgumentException('Priority must be a positive integer');
        }

        return new self(
            id: $id,
            title: $title,
            titleFr: $titleFr,
            content: $content,
            contentFr: $contentFr,
            imageUrl: $imageUrl,
            isActive: false,
            priority: $priority,
        );
    }

    public static function reconstitute(
        string $id,
        string $title,
        ?string $titleFr,
        string $content,
        ?string $contentFr,
        ?string $imageUrl,
        bool $isActive,
        int $priority,
    ): self {
        return new self(
            id: $id,
            title: $title,
            titleFr: $titleFr,
            content: $content,
            contentFr: $contentFr,
            imageUrl: $imageUrl,
            isActive: $isActive,
            priority: $priority,
        );
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function update(
        string $title,
        string $content,
        ?string $imageUrl,
        int $priority,
        ?string $titleFr = null,
        ?string $contentFr = null,
    ): void {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (trim($content) === '') {
            throw new \InvalidArgumentException('Content cannot be empty');
        }

        if ($priority < 0) {
            throw new \InvalidArgumentException('Priority must be a positive integer');
        }

        $this->title = $title;
        $this->titleFr = $titleFr;
        $this->content = $content;
        $this->contentFr = $contentFr;
        $this->imageUrl = $imageUrl;
        $this->priority = $priority;
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getTitleFr(): ?string { return $this->titleFr; }
    public function getContent(): string { return $this->content; }
    public function getContentFr(): ?string { return $this->contentFr; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function isActive(): bool { return $this->isActive; }
    public function getPriority(): int { return $this->priority; }
}



---


<?php

namespace App\Infrastructure\Announcement;

use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;
use Doctrine\DBAL\Connection;

final class DoctrineAnnouncementPopupRepository implements AnnouncementPopupRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function save(AnnouncementPopup $popup): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT id FROM announcement_popup WHERE id = ?',
            [$popup->getId()]
        );

        $data = [
            'title'      => $popup->getTitle(),
            'title_fr'   => $popup->getTitleFr(),
            'content'    => $popup->getContent(),
            'content_fr' => $popup->getContentFr(),
            'image_url'  => $popup->getImageUrl(),
            'is_active'  => (int) $popup->isActive(),
            'priority'   => $popup->getPriority(),
        ];

        if ($exists) {
            $this->connection->update('announcement_popup', $data, ['id' => $popup->getId()]);
            return;
        }

        $this->connection->insert('announcement_popup', array_merge($data, [
            'id'         => $popup->getId(),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]));
    }

    public function findById(string $id): ?AnnouncementPopup
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM announcement_popup WHERE id = ?',
            [$id]
        );

        return $row ? $this->hydrate($row) : null;
    }

    public function findActiveOrderedByPriority(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM announcement_popup WHERE is_active = 1 ORDER BY priority ASC'
        );

        return array_map($this->hydrate(...), $rows);
    }

    public function findAllOrderedByPriority(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM announcement_popup ORDER BY priority ASC'
        );

        return array_map($this->hydrate(...), $rows);
    }

    public function delete(string $id): void
    {
        $this->connection->delete('announcement_popup', ['id' => $id]);
    }

    private function hydrate(array $row): AnnouncementPopup
    {
        return AnnouncementPopup::reconstitute(
            id: $row['id'],
            title: $row['title'],
            titleFr: $row['title_fr'] ?? null,
            content: $row['content'],
            contentFr: $row['content_fr'] ?? null,
            imageUrl: $row['image_url'],
            isActive: (bool) $row['is_active'],
            priority: (int) $row['priority'],
        );
    }
}


---



<?php

namespace App\Controller;

use App\Application\Announcement\Query\GetActiveAnnouncementsQuery;
use App\Application\Announcement\UseCase\GetActiveAnnouncementsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/announcement-popups')]
final class AnnouncementPopupController extends AbstractController
{
    public function __construct(
        private readonly GetActiveAnnouncementsHandler $handler,
    ) {
    }

    #[Route('/active', name: 'api_announcement_popups_active', methods: ['GET'])]
    public function active(): JsonResponse
    {
        $popups = ($this->handler)(new GetActiveAnnouncementsQuery());

        return $this->json(array_map(fn($popup) => [
            'id'        => $popup->getId(),
            'title'     => $popup->getTitle(),
            'titleFr'   => $popup->getTitleFr(),
            'content'   => $popup->getContent(),
            'contentFr' => $popup->getContentFr(),
            'imageUrl'  => $popup->getImageUrl(),
            'priority'  => $popup->getPriority(),
        ], $popups));
    }
}


---

<?php

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class AnnouncementPopupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'       => 'Titre (EN)',
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire')],
                'attr'        => ['placeholder' => 'Title in English'],
            ])
            ->add('titleFr', TextType::class, [
                'label'    => 'Titre (FR)',
                'required' => false,
                'attr'     => ['placeholder' => 'Titre en français'],
            ])
            ->add('content', CKEditorType::class, [
                'label'       => 'Contenu (EN)',
                'constraints' => [new NotBlank(message: 'Le contenu est obligatoire')],
            ])
            ->add('contentFr', CKEditorType::class, [
                'label'    => 'Contenu (FR)',
                'required' => false,
            ])
            ->add('imageUrl', TextType::class, [
                'label'    => 'URL de l\'image',
                'required' => false,
                'attr'     => ['placeholder' => 'https://...'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Actif',
                'required' => false,
            ])
            ->add('priority', IntegerType::class, [
                'label'       => 'Priorité',
                'data'        => 0,
                'constraints' => [new PositiveOrZero(message: 'La priorité doit être positive ou nulle')],
                'help'        => 'Plus le chiffre est petit, plus le popup apparaît en premier (0 = premier)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}


---

<?php

namespace App\Controller\Admin;

use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;
use App\Form\AnnouncementPopupFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/admin/announcement-popups', name: 'admin_announcement_popup_')]
final class AnnouncementPopupAdminController extends AbstractController
{
    public function __construct(
        private readonly AnnouncementPopupRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/announcement_popup/index.html.twig', [
            'popups' => $this->repository->findAllOrderedByPriority(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(AnnouncementPopupFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $popup = AnnouncementPopup::create(
                id: Uuid::v4()->toRfc4122(),
                title: $data['title'],
                content: $data['content'],
                imageUrl: $data['imageUrl'] ?: null,
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?: null,
                contentFr: $data['contentFr'] ?: null,
            );

            if ($data['isActive']) {
                $popup->activate();
            }

            $this->repository->save($popup);
            $this->addFlash('success', 'Popup créé avec succès.');

            return $this->redirectToRoute('admin_announcement_popup_index');
        }

        return $this->render('admin/announcement_popup/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(string $id, Request $request): Response
    {
        $popup = $this->repository->findById($id);

        if (!$popup) {
            throw $this->createNotFoundException('Popup introuvable.');
        }

        $form = $this->createForm(AnnouncementPopupFormType::class, [
            'title'     => $popup->getTitle(),
            'titleFr'   => $popup->getTitleFr(),
            'content'   => $popup->getContent(),
            'contentFr' => $popup->getContentFr(),
            'imageUrl'  => $popup->getImageUrl(),
            'isActive'  => $popup->isActive(),
            'priority'  => $popup->getPriority(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $popup->update(
                title: $data['title'],
                content: $data['content'],
                imageUrl: $data['imageUrl'] ?: null,
                priority: (int) $data['priority'],
                titleFr: $data['titleFr'] ?: null,
                contentFr: $data['contentFr'] ?: null,
            );

            $data['isActive'] ? $popup->activate() : $popup->deactivate();

            $this->repository->save($popup);
            $this->addFlash('success', 'Popup mis à jour.');

            return $this->redirectToRoute('admin_announcement_popup_index');
        }

        return $this->render('admin/announcement_popup/edit.html.twig', [
            'popup' => $popup,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_announcement_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->repository->delete($id);
        $this->addFlash('success', 'Popup supprimé.');

        return $this->redirectToRoute('admin_announcement_popup_index');
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(string $id): Response
    {
        $popup = $this->repository->findById($id);

        if (!$popup) {
            throw $this->createNotFoundException('Popup introuvable.');
        }

        $popup->isActive() ? $popup->deactivate() : $popup->activate();
        $this->repository->save($popup);

        return $this->json(['isActive' => $popup->isActive()]);
    }
}


---

AnnouncementPopup.ts

export interface AnnouncementPopup {
  id: string
  title: string
  titleFr: string | null
  content: string
  contentFr: string | null
  imageUrl: string | null
  priority: number
}


AnnouncementPopupApi.ts

import { AnnouncementPopup } from '../domain/AnnouncementPopup'

export async function fetchActiveAnnouncementPopups(): Promise<AnnouncementPopup[]> {
  const response = await fetch('/api/announcement-popups/active')

  if (!response.ok) {
    throw new Error('Failed to fetch announcement popups')
  }

  const data = await response.json()

  // Le backend renvoie déjà titleFr / contentFr en camelCase
  return data as AnnouncementPopup[]
}


AnnouncementPopupModal.tsx

import React from 'react'
import BasicModal from './BasicModal'
import { AnnouncementPopup } from '../../core/announcement'
import { useTranslation } from 'react-i18next'

interface Props {
  popup: AnnouncementPopup
  currentIndex: number
  total: number
  onNext: () => void
  onPrev: () => void
  onFinish: () => void
}

export function AnnouncementPopupModal({ popup, currentIndex, total, onNext, onPrev, onFinish }: Props) {
  const { i18n } = useTranslation()
  const isFr = i18n.language?.startsWith('fr')

  const isFirst = currentIndex === 0
  const isLast = currentIndex === total - 1

  const displayTitle = isFr && popup.titleFr ? popup.titleFr : popup.title
  const displayContent = isFr && popup.contentFr ? popup.contentFr : popup.content

  return (
    <BasicModal open={true} onClose={onFinish} removeButtons={true} setOpen={onFinish}>
      <div className="flex flex-col h-full">

        <div className="flex items-center justify-center mb-6">
          <h2 className="text-xl font-bold text-bnpp-color-green text-center">
            Astuces et nouveautés CommsGPT !
          </h2>
        </div>

        <h3 className="text-lg font-semibold mb-4">
          {currentIndex + 1} - {displayTitle}
        </h3>

        <div className="flex flex-row flex-1 gap-4 min-h-0">

          <div className="w-3/5 bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
            {popup.imageUrl ? (
              <img src={popup.imageUrl} alt="" className="w-full h-full object-contain" />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-gray-300">
                <i className="fa fa-image fa-3x" />
              </div>
            )}
          </div>

          <div className="w-2/5 flex flex-col">
            <p className="font-bold text-base mb-2">Description</p>
            <div
              className="text-sm text-gray-700 leading-relaxed"
              dangerouslySetInnerHTML={{ __html: displayContent }}
            />
          </div>

        </div>

        <div className="flex justify-center items-center gap-6 mt-6">
          <button
            onClick={onPrev}
            disabled={isFirst}
            className={`text-gray-500 hover:text-gray-800 transition ${isFirst ? 'invisible' : ''}`}
          >
            <i className="fa fa-chevron-left" />
          </button>

          <span className="text-sm font-medium text-gray-600">
            {currentIndex + 1} / {total}
          </span>

          {isLast ? (
            <button onClick={onFinish} className="text-sm font-medium text-bnpp-color-green hover:underline">
              Terminer
            </button>
          ) : (
            <button onClick={onNext} className="text-gray-500 hover:text-gray-800 transition">
              <i className="fa fa-chevron-right" />
            </button>
          )}
        </div>

      </div>
    </BasicModal>
  )
}

