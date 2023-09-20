<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

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

        if ($user && $role[0] == 'ADMIN') {
            return $this->render('Admin/index.html.twig', [
                'controller_name' => 'AdminController',
            ]);
        } else {
            return $this->redirectToRoute('app_connexion');
        }
    }
}
