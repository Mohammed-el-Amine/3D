<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST','GET'])]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $emailOrUsername = $request->request->get('email_or_username');
            $password = $request->request->get('password');

            
            $userExisting = $userRepository->findOneBy(['email' => $emailOrUsername]);
            if (!$userExisting) {
                $userExisting = $userRepository->findOneBy(['username' => $emailOrUsername]);
            }

            if (!$userExisting) {
                throw new \Exception('Utilisateur non trouvé');
            }

            $hashedPassword = hash('sha256', $password);

            if ($hashedPassword === $userExisting->getPassword()) {
                return $this->redirectToRoute('app_home');
            }
        }
        return $this->render('Login/index.html.twig');
    }
}
