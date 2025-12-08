<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdminUserController extends AbstractController
{
    #[Route('/{_locale}/admin/users', name: 'admin.users', requirements: ['_locale' => 'fr|en'])]
    public function users(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $users = $paginator->paginate(
            $userRepository->findAll(),
            $request->query->getInt('page', 1),
            25
        );
        $usersCount = count($userRepository->findAll());

        return $this->render('pages/admin/users/users.html.twig', [
            'users' => $users,
            'usersCount' => $usersCount,
        ]);
    }

    #[Route('/{_locale}/admin/user/new', name: 'admin.user.new', requirements: ['_locale' => 'fr|en'])]
    public function newUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été créé.');

            return $this->redirectToRoute('admin.users');
        }

        return $this->render('pages/admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{_locale}/admin/user/edit/{id}', name: 'admin.user.edit', requirements: ['_locale' => 'fr|en'])]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été modifié.');

            return $this->redirectToRoute('admin.users');
        }

        return $this->render('pages/admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{_locale}/admin/user/delete/{id}', name: 'admin.user.delete', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        if (count($userRepository->findByOrderNotDeliveredAndNotCancelled($user->getId())) > 0) {
            $this->addFlash('error', 'L\'utilisateur ne peut pas être supprimé car il a des commandes non livrées.');
            return $this->redirectToRoute('admin.users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');

        return $this->redirectToRoute('admin.users');
    }

    #[Route('/{_locale}/admin/user/{id}', name: 'admin.user.show', requirements: ['_locale' => 'fr|en'])]
    public function showUser(int $id, UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $user = $userRepository->find($id);

        return $this->render('pages/admin/users/details.html.twig', [
            'user' => $user,
        ]);
    }
}
