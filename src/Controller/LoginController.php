<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class LoginController extends AbstractController
{
    #[Route('/api/connexion', name: 'app_connexion', methods: ['POST', 'GET'])]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $emailOrUsername = $request->request->get('email_or_username');
            $password = $request->request->get('password');

            $user = $userRepository->findOneBy(['email' => $emailOrUsername]);
            if (!$user) {
                $user = $userRepository->findOneBy(['username' => $emailOrUsername]);
            }

            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }

            if ($user->isActive() != 1){
                throw new \Exception('Votre compte à été désactiver. Merci de contacter le support');
            }

            $hashedPassword = hash('sha256', $password);

            $role = $user->getRole();
            
            if ($hashedPassword === $user->getPassword() && $role[0] == 'ROLE_ADMIN') {
                $session = $request->getSession();
                $session->set('user_id', $user->getId());
                return $this->redirectToRoute('app_admin_panel');
            } else {
                $session = $request->getSession();
                $session->set('user_id', $user->getId());
                return $this->redirectToRoute('app_acceuil');
            }            
        }
        return $this->render('Login/index.html.twig');
    }
}
