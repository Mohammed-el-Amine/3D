<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;

class HomeController extends AbstractController
{
    #[Route('/api/acceuil', name: 'app_acceuil')]
    #[Route('/')]
    public function index(UserRepository $userRepository, Request $request,)
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if ($userId) {
            $user = $userRepository->find($userId);
            if ($user) {
                $name = $user->getUsername();
            } else {
                $name = null;
            }
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'name' => $name,
        ]);
    }
}
