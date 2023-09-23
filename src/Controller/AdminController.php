<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/admin', name: 'app_admin_panel')]
    public function index(UserRepository $userRepository, Request $request): Response
    {

        $session = $request->getSession();

        if (!$session->has('user_id')) {
            return $this->redirectToRoute('app_connexion');
        }

        $userId = $session->get('user_id');
        $user = $userRepository->find($userId);

        $role = $user->getRole();
        $name = $user->getUsername();

        if (!$user && !$role[0] == 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_connexion');
        }

        $users = $this->readAll($userRepository);

        return $this->render('Admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'name' => $name,
            'users' => $users,
        ]);
    }

    function readAll(UserRepository $userRepository)
    {
        $users = $userRepository->findAll();
        return $users;
    }

    private function read(UserRepository $userRepository)
    {
    }

    private function create()
    {
    }

    #[Route('/api/admin/edit-user/{id}', name: 'app_admin_edit_user', methods: ['GET', 'POST'])]
    public function update(Request $request, UserRepository $userRepository, $id)
    {
        $session = $request->getSession();

        if (!$session->has('user_id')) {
            return $this->redirectToRoute('app_connexion');
        }

        $userId = $session->get('user_id');
        $admin = $userRepository->find($userId);
        $role = $admin->getRole();
        $user = $userRepository->find($id);
        $active = $user->isActive();


        if (!$admin && !$role[0] == 'ROLE_ADMIN' && $active != true) {
            return $this->redirectToRoute('app_connexion');
        }

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $email = $request->request->get('email');
            //  annotation de type explicite pour indiquer un tableau
            /** @var array|string|int|float|bool|null $roleEdit */
            $roleEdit = $request->request->get('role');

            $user->setUsername($username);
            $user->setEmail($email);
            $user->setRole([$roleEdit]);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $this->render('Admin/modifier.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
        ]);
    }

    #[Route('/api/admin/delete-user/{id}', name: 'app_admin_delete_user', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, UserRepository $userRepository, $id)
    {
        $session = $request->getSession();

        if (!$session->has('user_id')) {
            return $this->redirectToRoute('app_connexion');
        }

        $userId = $session->get('user_id');
        $admin = $userRepository->find($userId);
        $role = $admin->getRole();
        $user = $userRepository->find($id);
        $active = $user->isActive();

        if (!$admin && !$role[0] == 'ROLE_ADMIN' && $active != true) {
            return $this->redirectToRoute('app_connexion');
        }

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $user->setActive(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }
}
