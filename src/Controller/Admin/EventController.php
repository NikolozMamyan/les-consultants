<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\EventRegistrationRepository;
use App\Service\EventRegistrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/evenements', name: 'admin_events')]
final class EventController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(EventRegistrationRepository $registrations, EventRegistrationManager $manager): Response
    {
        $response = $this->render('admin/events.html.twig', [
            'adminSection' => 'events',
            'registrations' => $registrations->findForEvent(EventRegistrationManager::EVENT_SLUG),
            'registrationOpen' => $manager->isRegistrationOpen(),
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    #[Route('/inscriptions', name: '_registration_settings', methods: ['POST'])]
    public function registrationSettings(Request $request, EventRegistrationManager $registrations): Response
    {
        if (!$this->isCsrfTokenValid('event_registration_settings', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Le formulaire a expiré.');
        }

        $open = $request->request->getBoolean('registration_open');
        $registrations->setRegistrationOpen($open);
        $this->addFlash('success', $open ? 'Les inscriptions sont ouvertes.' : 'Les inscriptions sont fermées. La page Sold out est affichée.');

        return $this->redirectToRoute('admin_events', status: Response::HTTP_SEE_OTHER);
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
