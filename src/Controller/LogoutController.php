<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class LogoutController extends AbstractController
{

    #[Route('/api/deconnexion', name: 'app_deconnexion', methods: ['GET'])]
    public function logout(Request $request)
    {
        $session = $request->getSession();
        $session->remove('user_id');
        return $this->redirectToRoute('app_connexion');
    }
}
