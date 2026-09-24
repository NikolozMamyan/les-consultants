<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Submission;
use App\Repository\SubmissionRepository;
use App\Service\AdminDashboardDataProvider;
use App\Service\SubmissionManager;
use App\Service\ThemePageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly AdminDashboardDataProvider $dashboardData,
        private readonly ThemePageManager $themePages,
        private readonly SubmissionManager $submissionManager,
        private readonly SubmissionRepository $submissions,
    ) {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', $this->dashboardData->dashboard() + ['adminSection' => 'dashboard']);
    }

    #[Route('/contenus', name: 'content', methods: ['GET'])]
    public function content(): Response
    {
        return $this->render('admin/content/index.html.twig', [
            'adminSection' => 'content',
            'pages' => $this->themePages->pages(),
        ]);
    }

    #[Route('/contenus/{slug}', name: 'content_edit', methods: ['GET'], requirements: ['slug' => 'home|services|about|contact'])]
    public function editContent(string $slug): Response
    {
        $page = $this->themePages->page($slug);

        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/content/edit.html.twig', [
            'adminSection' => 'content',
            'pageData' => $page,
        ]);
    }

    #[Route('/contenus/{slug}', name: 'content_update', methods: ['POST'], requirements: ['slug' => 'home|services|about|contact'])]
    public function updateContent(string $slug, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('theme_page_'.$slug, $request->request->getString('_token'))) {
            return $this->json(['message' => 'Le formulaire a expiré. Rechargez la page puis réessayez.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $fields = json_decode($request->request->getString('fields', '{}'), true, flags: JSON_THROW_ON_ERROR);
            $carousels = json_decode($request->request->getString('carousels', '{}'), true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($fields) || !is_array($carousels)) {
                throw new \InvalidArgumentException('Les données envoyées sont invalides.');
            }

            $selectors = array_values($request->request->all('imageSelectors'));
            $files = array_values($request->files->all('images'));
            if (count($selectors) !== count($files)) {
                throw new \InvalidArgumentException('Les images envoyées sont invalides.');
            }

            $images = [];
            foreach ($files as $index => $file) {
                if (!is_string($selectors[$index]) || !$file instanceof UploadedFile) {
                    throw new \InvalidArgumentException('Une image envoyée est invalide.');
                }
                $images[] = ['selector' => $selectors[$index], 'file' => $file];
            }

            $page = $this->themePages->save($slug, $fields, $carousels, $images);
        } catch (\JsonException|\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            'message' => 'Les modifications ont été publiées.',
            'content' => $page->getContent(),
            'updatedAt' => $page->getUpdatedAt()->format(DATE_ATOM),
        ]);
    }

    #[Route('/missions-talents', name: 'submissions', methods: ['GET'])]
    public function submissions(Request $request): Response
    {
        $type = $request->query->getString('type');
        $type = in_array($type, [Submission::TYPE_MISSION, Submission::TYPE_PROFILE], true) ? $type : null;
        $search = mb_substr(trim($request->query->getString('q')), 0, 120);

        return $this->render('admin/submissions.html.twig', $this->submissionManager->adminData($type, $search) + [
            'adminSection' => 'submissions',
        ]);
    }

    #[Route('/missions-talents/export.csv', name: 'submissions_export', methods: ['GET'])]
    public function exportSubmissions(Request $request): StreamedResponse
    {
        $type = $request->query->getString('type');
        $type = in_array($type, [Submission::TYPE_MISSION, Submission::TYPE_PROFILE], true) ? $type : null;
        $search = mb_substr(trim($request->query->getString('q')), 0, 120);
        $items = $this->submissions->findForAdmin($type, $search);

        $response = new StreamedResponse(function () use ($items): void {
            $output = fopen('php://output', 'wb');
            if (false === $output) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Référence', 'Type', 'Sujet', 'Contact', 'Entreprise', 'Expertise', 'E-mail', 'Téléphone', 'Début', 'Durée', 'Statut', 'Reçu le'], ';', '"', '');

            foreach ($items as $item) {
                fputcsv($output, array_map($this->csvCell(...), [
                    '#'.str_pad((string) $item->getId(), 5, '0', STR_PAD_LEFT),
                    $item->getTypeLabel(),
                    $item->getSubject(),
                    $item->getContactName(),
                    $item->getOrganization() ?? '',
                    $item->getExpertise() ?? '',
                    $item->getEmail(),
                    $item->getPhone() ?? '',
                    $item->getStartDate()?->format('d/m/Y') ?? '',
                    $item->getDuration() ?? '',
                    $item->getStatusLabel(),
                    $item->getCreatedAt()->format('d/m/Y H:i'),
                ]), ';', '"', '');
            }

            fclose($output);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'missions-talents-'.(new \DateTimeImmutable())->format('Y-m-d').'.csv',
        ));

        return $response;
    }

    #[Route('/missions-talents/{id}', name: 'submission_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function showSubmission(int $id): Response
    {
        $submission = $this->submissions->find($id);
        if (!$submission instanceof Submission) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/submissions/show.html.twig', [
            'adminSection' => 'submissions',
            'submission' => $submission,
            'statuses' => [
                Submission::STATUS_NEW => $submission->getType() === Submission::TYPE_PROFILE ? 'À qualifier' : 'Nouveau',
                Submission::STATUS_IN_PROGRESS => 'En cours',
                Submission::STATUS_CONTACTED => 'Contacté',
                Submission::STATUS_CLOSED => 'Archivé',
            ],
        ]);
    }

    #[Route('/missions-talents/{id}/statut', name: 'submission_status', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function updateSubmissionStatus(int $id, Request $request): Response
    {
        $submission = $this->submissions->find($id);
        if (!$submission instanceof Submission) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('submission_status_'.$id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Le formulaire a expiré.');
        }

        try {
            $this->submissionManager->updateStatus($submission, $request->request->getString('status'));
        } catch (\InvalidArgumentException) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Le statut du dépôt a été mis à jour.');

        return $this->redirectToRoute('admin_submission_show', ['id' => $id]);
    }

    private function csvCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

}
