<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ContactRequest as ContactData;
use App\Dto\MissionRequest;
use App\Form\ContactRequestType;
use App\Form\MissionRequestType;
use App\Service\ContactMessageMailer;
use App\Service\SubmissionManager;
use App\Service\SubmissionMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, ContactMessageMailer $mailer, SubmissionManager $submissions): Response
    {
        $contact = new ContactData();
        $contact->profile = 'consultant' === $request->query->get('profil') ? 'consultant' : 'entreprise';

        $expertise = $request->query->getString('expertise');
        if (in_array($expertise, ['compliance', 'finance', 'risques', 'juridique', 'digital', 'formation', 'autre'], true)) {
            $contact->subject = $expertise;
        }

        $form = $this->createForm(ContactRequestType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submissions->recordProfile($contact);
            $mailer->send($contact);
            $this->addFlash('success', 'Merci pour votre message. Nous vous répondons sous 24h.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('pages/contact.html.twig', [
            'contactForm' => $form,
            'page' => 'contact',
            'selectedProfile' => $contact->profile,
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/deposer', name: 'app_deposit', methods: ['GET', 'POST'])]
    public function deposit(Request $request, SubmissionMailer $mailer, SubmissionManager $submissions): Response
    {
        $mission = new MissionRequest();
        $missionForm = $this->createForm(MissionRequestType::class, $mission);

        $missionForm->handleRequest($request);
        $autoOpen = 'GET' === $request->getMethod() && $request->query->has('form');

        if ($missionForm->isSubmitted()) {
            $autoOpen = true;

            if ($missionForm->isValid()) {
                $submissions->recordMission($mission);
                $mailer->sendMission($mission);
                $this->addFlash('success', 'Votre mission a bien été transmise. Notre équipe revient vers vous sous 24 h.');

                return $this->redirectToRoute('app_deposit');
            }
        }

        return $this->render('pages/deposit.html.twig', [
            'page' => 'deposit',
            'missionForm' => $missionForm,
            'autoOpen' => $autoOpen,
        ], new Response(
            status: $missionForm->isSubmitted()
                ? Response::HTTP_UNPROCESSABLE_ENTITY
                : Response::HTTP_OK,
        ));
    }
}
