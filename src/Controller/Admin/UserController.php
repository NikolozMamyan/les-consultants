<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Service\AdminUserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('/admin/utilisateurs', name: 'admin_users', methods: ['GET'])]
    public function index(UserRepository $users): Response
    {
        return $this->render('admin/users.html.twig', [
            'adminSection' => 'users',
            'users' => $users->findAllOrdered(),
        ]);
    }

    #[Route('/admin/utilisateurs/nouveau', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AdminUserManager $userManager): Response
    {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, ['password_required' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save(
                $user,
                (string) $form->get('role')->getData(),
                (string) $form->get('plainPassword')->getData(),
            );
            $this->addFlash('success', 'Le compte administrateur a été créé.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/form.html.twig', [
            'adminSection' => 'users',
            'form' => $form,
            'editedUser' => $user,
            'isEdit' => false,
        ]);
    }

    #[Route('/admin/utilisateurs/{id}', name: 'admin_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function edit(int $id, Request $request, UserRepository $users, AdminUserManager $userManager): Response
    {
        $user = $users->find($id);
        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $currentUser = $this->getUser();
        $isOwnAccount = $currentUser instanceof User && $currentUser->getId() === $user->getId();
        $role = in_array(AdminUserManager::ROLE_SUPER_ADMIN, $user->getRoles(), true)
            ? AdminUserManager::ROLE_SUPER_ADMIN
            : AdminUserManager::ROLE_ADMIN;
        $form = $this->createForm(AdminUserType::class, $user, [
            'role' => $role,
            'lock_access' => $isOwnAccount,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save(
                $user,
                (string) $form->get('role')->getData(),
                $form->get('plainPassword')->getData(),
            );
            $this->addFlash('success', 'Le compte administrateur a été mis à jour.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/form.html.twig', [
            'adminSection' => 'users',
            'form' => $form,
            'editedUser' => $user,
            'isEdit' => true,
        ]);
    }
}
