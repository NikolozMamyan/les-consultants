<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ContactRequest as ContactData;
use App\Dto\ConsultantApplication;
use App\Dto\MissionRequest;
use App\Form\ConsultantApplicationType;
use App\Form\ContactRequestType;
use App\Form\MissionRequestType;
use App\Service\ContactMessageMailer;
use App\Service\SubmissionMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, ContactMessageMailer $mailer): Response
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
    public function deposit(Request $request, SubmissionMailer $mailer): Response
    {
        $mission = new MissionRequest();
        $consultant = new ConsultantApplication();
        $missionForm = $this->createForm(MissionRequestType::class, $mission);
        $consultantForm = $this->createForm(ConsultantApplicationType::class, $consultant);

        $missionForm->handleRequest($request);
        $consultantForm->handleRequest($request);

        $activeFlow = in_array($request->query->get('form'), ['mission', 'consultant'], true)
            ? $request->query->getString('form')
            : 'mission';
        $autoOpen = 'GET' === $request->getMethod() && $request->query->has('form');

        if ($missionForm->isSubmitted()) {
            $activeFlow = 'mission';
            $autoOpen = true;

            if ($missionForm->isValid()) {
                $mailer->sendMission($mission);
                $this->addFlash('success', 'Votre mission a bien été transmise. Notre équipe revient vers vous sous 24 h.');

                return $this->redirectToRoute('app_deposit');
            }
        }

        if ($consultantForm->isSubmitted()) {
            $activeFlow = 'consultant';
            $autoOpen = true;

            if ($consultantForm->isValid()) {
                $mailer->sendConsultant($consultant);
                $this->addFlash('success', 'Votre profil a bien été transmis. Notre équipe l’étudiera avec attention.');

                return $this->redirectToRoute('app_deposit');
            }
        }

        return $this->render('pages/deposit.html.twig', [
            'page' => 'deposit',
            'missionForm' => $missionForm,
            'consultantForm' => $consultantForm,
            'activeFlow' => $activeFlow,
            'autoOpen' => $autoOpen,
        ], new Response(
            status: $missionForm->isSubmitted() || $consultantForm->isSubmitted()
                ? Response::HTTP_UNPROCESSABLE_ENTITY
                : Response::HTTP_OK,
        ));
    }
}
