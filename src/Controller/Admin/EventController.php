<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\EventRegistrationRepository;
use App\Service\EventRegistrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/evenements', name: 'admin_events')]
final class EventController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(EventRegistrationRepository $registrations): Response
    {
        $response = $this->render('admin/events.html.twig', [
            'adminSection' => 'events',
            'registrations' => $registrations->findForEvent(EventRegistrationManager::EVENT_SLUG),
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    #[Route('/export.csv', name: '_export', methods: ['GET'])]
    public function export(EventRegistrationManager $registrations): StreamedResponse
    {
        $response = new StreamedResponse(static function () use ($registrations): void {
            $output = fopen('php://output', 'wb');
            $registrations->exportCsv($output);
            fclose($output);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'inscriptions-after-champagne-work-2026-11-19.csv',
        ));
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
