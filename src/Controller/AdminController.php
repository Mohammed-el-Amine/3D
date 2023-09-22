<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AdminController extends AbstractController
{
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

        if (!$admin && !$role[0] == 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_connexion');
        }

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // mettre la method post 

        return $this->render('Admin/modifier.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
        ]);

    }

    private function delete()
    {
    }
}
