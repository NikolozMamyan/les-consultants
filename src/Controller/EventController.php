<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\EventRegistration;
use App\Form\EventRegistrationType;
use App\Service\EventRegistrationManager;
use Doctrine\DBAL\Exception as DatabaseException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/after-champagne-work', name: 'app_event_after_champagne_work', methods: ['GET', 'POST'])]
    public function register(Request $request, EventRegistrationManager $registrations, LoggerInterface $logger): Response
    {
        $form = $this->createForm(EventRegistrationType::class, new EventRegistration());
        $form->handleRequest($request);
        $status = $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $registrations->register($form->getData());
                $this->addFlash('event_registration_success', true);

                return $this->redirectToRoute('app_event_after_champagne_work', status: Response::HTTP_SEE_OTHER);
            } catch (DatabaseException) {
                $logger->error('An After Champagne Work registration could not be saved.');
                $form->addError(new FormError('Votre inscription n’a pas pu être confirmée. Veuillez réessayer dans quelques instants ou nous contacter.'));
                $status = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        }

        $response = $this->render('events/after_champagne_work.html.twig', [
            'registrationForm' => $form,
            'registered' => [] !== $request->getSession()->getFlashBag()->get('event_registration_success'),
        ], new Response(status: $status));
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
